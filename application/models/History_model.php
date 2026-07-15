<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History_model extends CI_Model
{
    private string $table = 'as_history';
    private string $electricTable = 'as_electric';
    private string $dateColumn = 'date';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // Determine actual history table name used in DB (support legacy 'history')
        if ($this->db->table_exists('as_history')) {
            $this->table = 'as_history';
        } elseif ($this->db->table_exists('history')) {
            $this->table = 'history';
        }

        // Prefer receipt date column for FIFO if present
        foreach (['tanggal_terima', 'date', 'tanggal_masuk', 'tgl_masuk', 'created_at'] as $c) {
            if ($this->db->field_exists($c, $this->table)) {
                $this->dateColumn = $c;
                break;
            }
        }
    }

    /**
     * Get last ID from as_history (manual id logic)
     * @return int
     */
    public function getLastId(): int
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }
        $row = $this->db->select_max('id')->get($this->table)->row_array();
        return isset($row['id']) && $row['id'] !== null ? (int) $row['id'] : 0;
    }

    /**
     * Compatibility wrapper: snake_case name requested by spec
     * @return int
     */
    public function get_last_id(): int
    {
        return $this->getLastId();
    }

    /**
     * Add a transaction (history record) and update stock column in as_electric (if present).
     * Uses a DB transaction to keep things consistent.
     *
     * $data should contain: electric_id, type ('IN'|'OUT'), qty (int), user_nik (string), optional note
     *
     * @param array $data
     * @return array ['success' => bool, 'id' => int|null, 'message' => string]
     */
    public function addTransaction(array $data): array
    {
        // Enforce local timezone for all inserted/displayed timestamps
        date_default_timezone_set('Asia/Jakarta');

        if (!$this->db->table_exists($this->table)) {
            return ['success' => false, 'id' => null, 'message' => 'Table ' . $this->table . ' tidak ditemukan'];
        }
        // Normalize type
        $typeRaw = $data['type'] ?? '';
        $typeNorm = in_array(strtolower($typeRaw), ['masuk', 'in']) ? 'Masuk' : 'Keluar';

        // Load id generator
        $this->load->model('Id_generator_model');

        // Determine which column to use for quantity (compatibility with various schemas)
        $possibleQtyCols = ['qty', 'quantity', 'jumlah', 'amount', 'jml', 'qty_masuk', 'jumlah_masuk', 'quantity_masuk'];
        $qtyCol = null;
        foreach ($possibleQtyCols as $c) {
            if ($this->db->field_exists($c, $this->table)) {
                $qtyCol = $c;
                break;
            }
        }

        // Ensure we have a date field name available
        $dateField = $this->dateColumn;

        // If Masuk: insert a batch with qty_sisa = qty (if column exists)
        if ($typeNorm === 'Masuk') {
            $this->db->trans_begin();
            try {
                $nextId = (int) $this->Id_generator_model->generate_manual_id($this->table, 'id');
                $insert = [
                    'id' => $nextId,
                    'electric_id' => $data['electric_id'],
                    'type' => 'Masuk',
                    'user_nik' => $data['user_nik'] ?? null,
                    'keterangan' => $data['keterangan'] ?? ($data['note'] ?? null),
                ];

                // Put qty into the appropriate column if it exists, otherwise keep in metadata
                $givenQty = (int) ($data['qty'] ?? 0);
                if ($qtyCol !== null) {
                    $insert[$qtyCol] = $givenQty;
                }

                // Store remaining qty in qty_sisa if available (authoritative for FIFO)
                // WAJIB: qty_sisa saat masuk = qty_masuk (tidak pernah ada pengurangan saat insert awal)
                if ($this->db->field_exists('qty_sisa', $this->table)) {
                    $insert['qty_sisa'] = $givenQty;
                }

                // Set date/receipt field
                if (!empty($data['tanggal_terima']) && $this->db->field_exists('tanggal_terima', $this->table)) {
                    $insert['tanggal_terima'] = $data['tanggal_terima'];
                } elseif ($dateField) {
                    $insert[$dateField] = $data['date'] ?? date('Y-m-d H:i:s');
                }

                // Procurement metadata support: prefer dedicated columns, otherwise append to keterangan
                $procFields = ['po_number', 'distributor', 'tanggal_pesan', 'tanggal_terima', 'harga_satuan', 'po_id'];
                $procMeta = [];
                foreach ($procFields as $pf) {
                    if (isset($data[$pf]) && $data[$pf] !== '') {
                        if ($this->db->field_exists($pf, $this->table)) {
                            $insert[$pf] = $data[$pf];
                        } else {
                            $procMeta[$pf] = $data[$pf];
                        }
                    }
                }
                if (!empty($procMeta)) {
                    $metaJson = json_encode($procMeta, JSON_UNESCAPED_UNICODE);
                    $insert['keterangan'] = trim((string)($insert['keterangan'] ?? '') . ($insert['keterangan'] ? ' | ' : '') . $metaJson);
                }

                $this->db->insert($this->table, $insert);

                if ($this->db->table_exists($this->electricTable) && $this->db->field_exists('stock', $this->electricTable)) {
                    if ($this->db->field_exists('qty_sisa', $this->table)) {
                        $row = $this->db->select('SUM(qty_sisa) as total')->where('electric_id', $data['electric_id'])->where('type', 'Masuk')->get($this->table)->row_array();
                        $newStock = isset($row['total']) ? (int) $row['total'] : 0;
                    } else {
                        $electric = $this->db->get_where($this->electricTable, ['electric_id' => $data['electric_id']])->row_array();
                        $currentStock = isset($electric['stock']) ? (int) $electric['stock'] : 0;
                        $newStock = $currentStock + (int) $data['qty'];
                    }
                    $this->db->where('electric_id', $data['electric_id'])->update($this->electricTable, ['stock' => $newStock]);
                }

                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    return ['success' => false, 'id' => null, 'message' => 'Database transaction failed'];
                }

                $this->db->trans_commit();
                return ['success' => true, 'id' => $nextId, 'message' => 'Batch Masuk tersimpan'];
            } catch (Exception $e) {
                $this->db->trans_rollback();
                return ['success' => false, 'id' => null, 'message' => $e->getMessage()];
            }
        }

        // If Keluar: consume from as_history Masuk batches in FIFO order (oldest date first)
        $requested = (int) ($data['qty'] ?? 0);
        $electricId = $data['electric_id'] ?? null;
        if ($requested <= 0 || !$electricId) {
            return ['success' => false, 'id' => null, 'message' => 'Data tidak valid'];
        }

        // If qty_sisa is present, use history FIFO; otherwise fall back to storage or stock decrement
        if ($this->db->field_exists('qty_sisa', $this->table)) {
            $this->db->trans_begin();
            try {
                $remaining = $requested;
                $insertedIds = [];

                // Select candidate batches (Masuk) with remaining qty
                $this->db->from($this->table . ' h');
                $this->db->where('h.electric_id', $electricId);
                // Allow Audit entries to be treated as incoming batches for FIFO consumption
                $this->db->group_start();
                $this->db->where('h.type', 'Masuk');
                $this->db->or_where('h.type', 'Audit');
                $this->db->group_end();
                $this->db->where('h.qty_sisa >', 0);
                // Use created_at for strict FIFO ordering when present, otherwise use detected date column
                if ($this->db->field_exists('created_at', $this->table)) {
                    $this->db->order_by('h.created_at', 'ASC');
                } else {
                    $this->db->order_by('h.' . $this->dateColumn, 'ASC');
                }
                $this->db->order_by('h.id', 'ASC');
                $batches = $this->db->get()->result_array();

                if (empty($batches)) {
                    $this->db->trans_rollback();
                    return ['success' => false, 'id' => null, 'message' => 'Stok tidak tersedia'];
                }

                foreach ($batches as $batch) {
                    if ($remaining <= 0) break;
                    $available = (int) ($batch['qty_sisa'] ?? 0);
                    if ($available <= 0) continue;
                    $take = min($available, $remaining);

                    // Decrease the batch remaining qty: sisa_batch_baru = sisa_batch_lama - qty_keluar
                    // WAJIB: sisa batch dikurangi dengan qty yang diambil (FIFO consumption)
                    $newQtySisa = $available - $take;
                    $this->db->where('id', $batch['id'])->update($this->table, ['qty_sisa' => $newQtySisa]);

                    // Insert a Keluar history row referencing this batch in keterangan
                    $keluarId = (int) $this->Id_generator_model->generate_manual_id($this->table, 'id');
                    $keluarInsert = [
                        'id' => $keluarId,
                        'electric_id' => $electricId,
                        'type' => 'Keluar',
                        'user_nik' => $data['user_nik'] ?? null,
                    ];

                    if (isset($data['wo_id']) && $this->db->field_exists('wo_id', $this->table)) {
                        $keluarInsert['wo_id'] = $data['wo_id'];
                    }

                    // Store explicit reference to source batch (new from_batch_id field)
                    if ($this->db->field_exists('from_batch_id', $this->table)) {
                        $keluarInsert['from_batch_id'] = (int)$batch['id'];
                    }

                    // Standardized automatic description for centralized outgoing flow (include taken amount)
                    $roleName = $this->session->userdata('role') ?? 'Sistem';
                    $keluarInsert['keterangan'] = 'Pengambilan oleh ' . $roleName . ': ' . $take . ' dari Batch #' . $batch['id'];

                    // If PO/Batch display column exists, populate with informative FIFO label including the batch date
                    $batchDate = null;
                    if (!empty($batch[$dateField])) $batchDate = $batch[$dateField];
                    elseif (!empty($batch['created_at'])) $batchDate = $batch['created_at'];
                    $poLabel = 'FIFO-B' . $batch['id'];
                    if (!empty($batchDate)) {
                        $poLabel .= ' (Tgl: ' . date('d M Y', strtotime($batchDate)) . ')';
                    }
                    if ($this->db->field_exists('po_number', $this->table)) {
                        $keluarInsert['po_number'] = $poLabel;
                    } elseif ($this->db->field_exists('po', $this->table)) {
                        $keluarInsert['po'] = $poLabel;
                    }

                    // Put qty into correct column if available
                    if ($qtyCol !== null) {
                        $keluarInsert[$qtyCol] = $take;
                    }
                    // Row Keluar memiliki qty_sisa = 0 karena semua qty sudah diambil
                    if ($this->db->field_exists('qty_sisa', $this->table)) {
                        $keluarInsert['qty_sisa'] = 0;
                    }
                    // set date field
                    if (!empty($data['tanggal_terima']) && $this->db->field_exists('tanggal_terima', $this->table)) {
                        $keluarInsert['tanggal_terima'] = $data['tanggal_terima'];
                    } elseif ($dateField) {
                        $keluarInsert[$dateField] = $data['date'] ?? date('Y-m-d H:i:s');
                    }
                    $this->db->insert($this->table, $keluarInsert);
                    $insertedIds[] = $keluarId;

                    $remaining -= $take;
                }

                if ($remaining > 0) {
                    $this->db->trans_rollback();
                    return ['success' => false, 'id' => null, 'message' => 'Stok tidak mencukupi. Kekurangan: ' . $remaining];
                }

                // Recompute stock from history if possible
                if ($this->db->table_exists($this->electricTable) && $this->db->field_exists('stock', $this->electricTable)) {
                    $row = $this->db->select('SUM(qty_sisa) as total')->where('electric_id', $electricId)->where('type', 'Masuk')->get($this->table)->row_array();
                    $newStock = isset($row['total']) ? (int) $row['total'] : 0;
                    $this->db->where('electric_id', $electricId)->update($this->electricTable, ['stock' => $newStock]);
                }

                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    return ['success' => false, 'id' => null, 'message' => 'Database transaction failed'];
                }

                $this->db->trans_commit();
                return ['success' => true, 'ids' => $insertedIds, 'message' => 'Barang berhasil diambil'];
            } catch (Exception $e) {
                $this->db->trans_rollback();
                return ['success' => false, 'id' => null, 'message' => $e->getMessage()];
            }
        }

        // Fallback: if qty_sisa column not present, optionally use as_storage or simple decrement
        if ($this->db->table_exists('as_storage')) {
            $this->load->model('Storage_model');
            $location_id = $data['location_id'] ?? null;
            $takeRes = $this->Storage_model->take_items($location_id, $electricId, $requested, $data['user_nik'] ?? null);
            if (empty($takeRes['success'])) {
                return ['success' => false, 'id' => null, 'message' => $takeRes['message'] ?? 'Gagal mengambil dari storage'];
            }
            // create a single Keluar history row to record the action
            $keluarId = (int) $this->Id_generator_model->generate_manual_id($this->table, 'id');
            $keluarInsert = [
                'id' => $keluarId,
                'electric_id' => $electricId,
                'type' => 'Keluar',
                'user_nik' => $data['user_nik'] ?? null,
                'keterangan' => $data['keterangan'] ?? ($data['note'] ?? null),
            ];
            if ($qtyCol !== null) {
                $keluarInsert[$qtyCol] = $requested;
            }
            if ($this->db->field_exists('qty_sisa', $this->table)) {
                $keluarInsert['qty_sisa'] = 0;
            }
            if (!empty($data['tanggal_terima']) && $this->db->field_exists('tanggal_terima', $this->table)) {
                $keluarInsert['tanggal_terima'] = $data['tanggal_terima'];
            } elseif ($dateField) {
                $keluarInsert[$dateField] = $data['date'] ?? date('Y-m-d H:i:s');
            }
            $this->db->insert($this->table, $keluarInsert);
            // Recompute stock from storage
            if ($this->db->table_exists($this->electricTable) && $this->db->field_exists('stock', $this->electricTable)) {
                $newStock = (int) $this->Storage_model->get_total_stock($electricId);
                $this->db->where('electric_id', $electricId)->update($this->electricTable, ['stock' => $newStock]);
            }
            return ['success' => true, 'id' => $keluarId, 'message' => 'Barang berhasil diambil (storage)'];
        }

        // Final fallback: single history Keluar row and decrement stock field
        $this->db->trans_begin();
        try {
            $keluarId = (int) $this->Id_generator_model->generate_manual_id($this->table, 'id');
            $keluarInsert = [
                'id' => $keluarId,
                'electric_id' => $electricId,
                'type' => 'Keluar',
                'user_nik' => $data['user_nik'] ?? null,
                'keterangan' => $data['keterangan'] ?? ($data['note'] ?? null),
                'date' => $data['date'] ?? date('Y-m-d H:i:s'),
            ];
            // Put qty into the appropriate column if present
            if ($qtyCol !== null) {
                $keluarInsert[$qtyCol] = $requested;
            }
            if ($this->db->field_exists('qty_sisa', $this->table)) {
                $keluarInsert['qty_sisa'] = 0;
            }
            $this->db->insert($this->table, $keluarInsert);

            if ($this->db->table_exists($this->electricTable) && $this->db->field_exists('stock', $this->electricTable)) {
                $electric = $this->db->get_where($this->electricTable, ['electric_id' => $electricId])->row_array();
                $currentStock = isset($electric['stock']) ? (int) $electric['stock'] : 0;
                $newStock = $currentStock - $requested;
                if ($newStock < 0) $newStock = 0;
                $this->db->where('electric_id', $electricId)->update($this->electricTable, ['stock' => $newStock]);
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return ['success' => false, 'id' => null, 'message' => 'Database transaction failed'];
            }

            $this->db->trans_commit();
            return ['success' => true, 'id' => $keluarId, 'message' => 'Barang berhasil diambil'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'id' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Compatibility wrapper: insert_history per spec
     * @param array $data
     * @return array
     */
    public function insert_history(array $data): array
    {
        return $this->addTransaction($data);
    }

    /**
     * Recalculate sisa_batch values in-place for all records based on FIFO logic
     * Only updates sisa_batch field, preserves all other fields
     * @param array $rows History rows (passed by reference)
     * @return void
     */
    private function recalculateSisaBatchInPlace(array &$rows): void
    {
        // Determine which column holds qty
        $qtyCol = 'qty';
        $amountCols = ['qty', 'amount', 'quantity', 'jumlah', 'jml'];
        foreach ($amountCols as $ac) {
            if ($this->db->field_exists($ac, $this->table)) { $qtyCol = $ac; break; }
        }

        // Check if from_batch_id column exists (new schema)
        $hasFromBatchId = $this->db->field_exists('from_batch_id', $this->table);

        // Group by electric_id to track FIFO batches separately per item
        $byElectric = [];
        $rowIndices = [];
        foreach ($rows as $idx => &$r) {
            $eid = $r['electric_id'] ?? null;
            if (!$eid) continue;
            if (!isset($byElectric[$eid])) $byElectric[$eid] = [];
            $byElectric[$eid][] = &$r;
            $rowIndices[$idx] = true;
        }

        // For each electric, recalculate sisa_batch in FIFO order
        foreach ($byElectric as $eid => &$eRows) {
            $batchState = []; // batchId => remaining_qty
            $batchQueue = []; // ordered list of batch IDs (for FIFO assignment of Keluar)
            $batchSeqMap = []; // batchId => batch_seq for reference
            
            foreach ($eRows as &$r) {
                // Determine row type - use 'type' field directly (more reliable than string patterns)
                $type = isset($r['type']) ? strtolower(trim((string)$r['type'])) : '';
                $isIn = false;
                $isOut = false;
                
                // Type detection: check actual type value
                if (strpos($type, 'masuk') !== false || $type === 'in' || $type === 'masuk') {
                    $isIn = true;
                } elseif (strpos($type, 'keluar') !== false || $type === 'out' || $type === 'keluar') {
                    $isOut = true;
                } elseif ($type === 'audit') {
                    // Audit rows can be treated as incoming (treat as Masuk for FIFO)
                    $isIn = true;
                }
                
                if ($isIn) {
                    // Masuk: set initial qty_sisa = qty
                    $batchId = (int)($r['id'] ?? 0);
                    // Use display_amount if available, else fallback to qty fields
                    $initialQty = (int)($r['display_amount'] ?? $r[$qtyCol] ?? $r['qty_sisa'] ?? 0);
                    $batchState[$batchId] = $initialQty;
                    $batchQueue[] = $batchId; // Add to FIFO queue
                    // Store batch_seq for reference
                    $batchSeqMap[$batchId] = isset($r['batch_seq']) ? $r['batch_seq'] : count($batchQueue);
                    $r['sisa_batch'] = $initialQty;
                } elseif ($isOut) {
                    $take = (int)($r['display_amount'] ?? $r[$qtyCol] ?? $r['qty_sisa'] ?? 0);
                    $refId = null;
                    
                    // PRIORITY 1: Use from_batch_id if available (explicit assignment - NEW SCHEMA)
                    if ($hasFromBatchId && isset($r['from_batch_id']) && (int)$r['from_batch_id'] > 0) {
                        $refId = (int)$r['from_batch_id'];
                    } 
                    // PRIORITY 2: If new schema NOT available, use FIFO fallback (don't guess from keterangan)
                    // This ensures consistent behavior until migration is run
                    else {
                        $refId = null; // Force FIFO fallback
                    }
                    
                    // If explicit refId found and valid
                    if ($refId && isset($batchState[$refId])) {
                        $batchState[$refId] = max(0, $batchState[$refId] - $take);
                        $r['sisa_batch'] = $batchState[$refId];
                        // Ensure batch_seq is set for this explicit batch
                        if (isset($batchSeqMap[$refId])) {
                            $r['batch_seq'] = $batchSeqMap[$refId];
                        }
                    } 
                    // PRIORITY 3: Use FIFO fallback (assign to oldest non-empty batch)
                    else {
                        $assigned = false;
                        foreach ($batchQueue as $batchId) {
                            if ($batchState[$batchId] > 0) {
                                $batchState[$batchId] = max(0, $batchState[$batchId] - $take);
                                $r['sisa_batch'] = $batchState[$batchId];
                                $r['from_batch_id'] = $batchId; // Store for future reference
                                // Set batch_seq from the source batch
                                if (isset($batchSeqMap[$batchId])) {
                                    $r['batch_seq'] = $batchSeqMap[$batchId];
                                }
                                $assigned = true;
                                break;
                            }
                        }
                        if (!$assigned) {
                            $r['sisa_batch'] = '-';
                            // Keep batch_seq as is if no batch found
                        }
                    }
                } else {
                    // Other types: keep existing sisa_batch or dash
                    if (!isset($r['sisa_batch']) || $r['sisa_batch'] === '' || $r['sisa_batch'] === null) {
                        $r['sisa_batch'] = isset($r['qty_sisa']) && (int)$r['qty_sisa'] > 0 ? (int)$r['qty_sisa'] : '-';
                    }
                }
            }
        }
    }

    /**
     * Get available incoming batches (qty_sisa > 0) joined with electric and location.
     * Ordered by oldest batch first (FIFO)
     * @return array
     */
    public function get_available_batches(): array
    {
        if (!$this->db->table_exists($this->table)) return [];
        // Determine qty column to use
        $qtyCol = 'qty_sisa';
        if (!$this->db->field_exists('qty_sisa', $this->table)) {
            $possible = ['qty', 'quantity', 'jumlah', 'amount', 'jml'];
            $found = null;
            foreach ($possible as $p) {
                if ($this->db->field_exists($p, $this->table)) { $found = $p; break; }
            }
            $qtyCol = $found ?? 'qty_sisa';
        }

        $tblE = $this->electricTable;

        // Build select parts conditionally (avoid referencing missing columns)
        $selectParts = [];
        $selectParts[] = 'h.id';
        $selectParts[] = 'h.electric_id';
        $selectParts[] = 'h.' . $qtyCol . ' as qty_sisa';
        $selectParts[] = 'h.' . $this->dateColumn . ' as created_at';

        // Electric specification columns (provide empty string fallback if column missing)
        $specCols = ['nama' => 'nama', 'brand' => 'brand', 'voltage' => 'voltage', 'voltage_unit' => 'voltage_unit', 'ampere' => 'ampere', 'daya' => 'daya'];
        foreach ($specCols as $col => $alias) {
            if ($this->db->field_exists($col, $tblE)) {
                $selectParts[] = 'e.' . $col . ' as ' . $alias;
            } else {
                $selectParts[] = "'' as " . $alias;
            }
        }

        // Location join and selection (detect join column in electric table)
        $joinOn = null;
        if ($this->db->table_exists('as_location')) {
            if ($this->db->field_exists('location_id', $tblE)) {
                $joinOn = 'e.location_id = l.id';
            } elseif ($this->db->field_exists('location', $tblE)) {
                $joinOn = 'e.location = l.id';
            }
            if ($joinOn) {
                // WAJIB: Include both location_id and location_name for filtering
                $selectParts[] = 'l.id as location_id';
                $selectParts[] = 'l.location_name as location_name';
            } else {
                $selectParts[] = "'' as location_id";
                $selectParts[] = "'' as location_name";
            }
        } else {
            $selectParts[] = "'' as location_id";
            $selectParts[] = "'' as location_name";
        }

        $this->db->select(implode(', ', $selectParts), false);
        $this->db->from($this->table . ' h');
        $this->db->join($tblE . ' e', 'e.electric_id = h.electric_id', 'left');
        if ($joinOn) {
            $this->db->join('as_location l', $joinOn, 'left');
        }

        $this->db->where('h.' . $qtyCol . ' >', 0);

        // Order by batch created date asc (oldest first)
        if ($this->db->field_exists('created_at', $this->table)) {
            $this->db->order_by('h.created_at', 'ASC');
        } else {
            $this->db->order_by('h.' . $this->dateColumn, 'ASC');
        }

        return $this->db->get()->result_array();
    }

    /**
     * Consume quantity from a specific incoming batch (history.id) and record a Keluar row.
     * Uses manual ID generator for inserted rows and updates as_electric stock when available.
     * @param int $batchId
     * @param int $qty
     * @param string|null $user_nik
     * @param string|null $keterangan
     * @return array ['success'=>bool,'id'=>int|null,'message'=>string]
     */
    public function take_from_batch(int $batchId, int $qty, ?string $user_nik = null, ?string $keterangan = null, ?int $wo_id = null): array
    {
        if ($qty <= 0) return ['success' => false, 'id' => null, 'message' => 'Jumlah harus lebih dari 0'];
        if (!$this->db->table_exists($this->table)) return ['success' => false, 'id' => null, 'message' => 'Tabel history tidak ditemukan'];

        $this->load->model('Id_generator_model');
        $this->db->trans_begin();
        try {
            $batch = $this->db->get_where($this->table, ['id' => $batchId])->row_array();
            if (!$batch) {
                $this->db->trans_rollback();
                return ['success' => false, 'id' => null, 'message' => 'Batch tidak ditemukan'];
            }

            // Determine qty_sisa column
            $qtyCol = 'qty_sisa';
            if (!$this->db->field_exists('qty_sisa', $this->table)) {
                $possible = ['qty', 'quantity', 'jumlah', 'amount', 'jml'];
                $found = null;
                foreach ($possible as $p) { if ($this->db->field_exists($p, $this->table)) { $found = $p; break; } }
                $qtyCol = $found ?? 'qty_sisa';
            }

            $available = isset($batch[$qtyCol]) ? (int)$batch[$qtyCol] : 0;
            if ($available < $qty) {
                $this->db->trans_rollback();
                return ['success' => false, 'id' => null, 'message' => 'Jumlah melebihi sisa batch (' . $available . ')'];
            }

            // decrement batch remaining
            $newRemain = $available - $qty;
            $this->db->where('id', $batchId)->update($this->table, [$qtyCol => $newRemain]);

            // insert Keluar row
            $nextId = (int)$this->Id_generator_model->generate_manual_id($this->table, 'id');
            $keluar = [
                'id' => $nextId,
                'electric_id' => $batch['electric_id'],
                'type' => 'Keluar',
                'user_nik' => $user_nik,
                // Standardize keterangan to include the centralized admin phrase, include qty, and preserve any provided note
                'keterangan' => (empty($keterangan) ? ('Pengambilan Barang: ' . $qty . ' dari Batch #' . $batchId) : ('Pengambilan Barang: ' . $qty . ' dari Batch #' . $batchId . ' | ' . $keterangan)),
            ];
            
            if ($wo_id !== null && $this->db->field_exists('wo_id', $this->table)) {
                $keluar['wo_id'] = $wo_id;
            }
            // put qty into appropriate column if exists
            if ($this->db->field_exists($qtyCol, $this->table)) {
                $keluar[$qtyCol] = $qty;
            }
            if ($this->db->field_exists('qty_sisa', $this->table)) {
                $keluar['qty_sisa'] = 0;
            }
            // set date field
            if (!empty($this->dateColumn) && $this->db->field_exists($this->dateColumn, $this->table)) {
                $keluar[$this->dateColumn] = date('Y-m-d H:i:s');
            }

            $this->db->insert($this->table, $keluar);

            // recompute stock in as_electric if column exists
            if ($this->db->table_exists('as_electric') && $this->db->field_exists('stock', 'as_electric')) {
                $row = $this->db->select('SUM(qty_sisa) as total')->where('electric_id', $batch['electric_id'])->get($this->table)->row_array();
                $newStock = isset($row['total']) ? (int)$row['total'] : 0;
                $this->db->where('electric_id', $batch['electric_id'])->update('as_electric', ['stock' => $newStock]);
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return ['success' => false, 'id' => null, 'message' => 'Database transaction failed'];
            }

            $this->db->trans_commit();
            return ['success' => true, 'id' => $nextId, 'message' => 'Berhasil mengambil dari batch'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'id' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Return all history rows joined with electric name and user name.
     * @return array
     */
    public function get_all_history(): array
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        // Decide whether we can join location (and how) to expose a human-friendly location_name
        $canJoinLocation = false;
        $locationJoinOn = '';
        if ($this->db->table_exists('as_location') && $this->db->table_exists($this->electricTable)) {
            if ($this->db->field_exists('location', $this->electricTable)) {
                $locationJoinOn = 'e.location = l.id';
                $canJoinLocation = true;
            } elseif ($this->db->field_exists('location_id', $this->electricTable)) {
                $locationJoinOn = 'e.location_id = l.id';
                $canJoinLocation = true;
            }
        }

        // Build select clause depending on available columns (stock / qty_sisa)
        if ($this->db->table_exists($this->electricTable) && $this->db->field_exists('stock', $this->electricTable)) {
            $select = 'h.*, e.nama as nama_barang, e.brand as brand, e.type as spec_type, e.voltage, e.voltage_unit, e.ampere, e.daya, e.daya_unit, u.name as user_name, COALESCE(e.stock, (SELECT SUM(hh.qty_sisa) FROM ' . $this->table . ' hh WHERE hh.electric_id = h.electric_id), 0) AS system_stock';
        } elseif ($this->db->field_exists('qty_sisa', $this->table)) {
            $select = 'h.*, e.nama as nama_barang, e.brand as brand, e.type as spec_type, e.voltage, e.voltage_unit, e.ampere, e.daya, e.daya_unit, u.name as user_name, (SELECT SUM(hh.qty_sisa) FROM ' . $this->table . ' hh WHERE hh.electric_id = h.electric_id) AS system_stock';
        } else {
            $select = 'h.*, e.nama as nama_barang, e.brand as brand, e.type as spec_type, e.voltage, e.voltage_unit, e.ampere, e.daya, e.daya_unit, u.name as user_name, 0 AS system_stock';
        }

        if ($canJoinLocation) {
            $select .= ', COALESCE(l.location_name, "-") as location_name';
        } else {
            $select .= ", '' as location_name";
        }

        // Detect active amount-like column to expose a normalized display_amount
        $amountCols = ['amount', 'qty', 'quantity', 'jumlah', 'jml'];
        $pickedAmountCol = null;
        foreach ($amountCols as $ac) {
            if ($this->db->field_exists($ac, $this->table)) { $pickedAmountCol = $ac; break; }
        }
        if ($pickedAmountCol) {
            $select .= ', COALESCE(h.' . $pickedAmountCol . ', COALESCE(h.qty_sisa, 0), 0) as display_amount';
        } else {
            $select .= ', COALESCE(h.qty_sisa, 0) as display_amount';
        }

        $this->db->select($select, false);
        $this->db->from($this->table . ' h');

        // Electric join (if available)
        if ($this->db->table_exists($this->electricTable)) {
            $this->db->join($this->electricTable . ' e', 'h.electric_id = e.electric_id', 'left');
        }

        // Location join only when we can determine the join column
        if ($canJoinLocation) {
            $this->db->join('as_location l', $locationJoinOn, 'left');
        }

        // User join
        if ($this->db->table_exists('as_user')) {
            $this->db->join('as_user u', 'h.user_nik = u.nik', 'left');
        }

        // PO + Supplier join: mendapatkan supplier_name dari PO terkait (untuk data lama yang tidak punya distributor)
        if ($this->db->table_exists('as_po') && $this->db->field_exists('po_id', $this->table)) {
            $this->db->join('as_po p', 'h.po_id = p.id', 'left');
            if ($this->db->table_exists('as_suppliers')) {
                $this->db->join('as_suppliers sp', 'p.supplier_id = sp.id', 'left');
                $this->db->select('COALESCE(NULLIF(TRIM(h.distributor), \'\'), sp.supplier_name, \'\') as supplier_name', false);
            } else {
                $this->db->select('COALESCE(NULLIF(TRIM(h.distributor), \'\'), \'\') as supplier_name', false);
            }
        } elseif ($this->db->field_exists('distributor', $this->table)) {
            $this->db->select('COALESCE(NULLIF(TRIM(h.distributor), \'\'), \'\') as supplier_name', false);
        } else {
            $this->db->select("'' as supplier_name", false);
        }

        // For per-row batch sisa computation we need chronological order (oldest first)
        if ($this->db->field_exists('created_at', $this->table)) {
            $this->db->order_by('h.created_at', 'ASC');
        } else {
            $this->db->order_by('h.' . $this->dateColumn, 'ASC');
        }

        $rows = $this->db->get()->result_array();

        // Map rows by id for quick lookup
        $rowsById = [];
        foreach ($rows as $r) {
            $rowsById[(int)($r['id'] ?? 0)] = $r;
        }

        $batchState = []; // batch_id => remaining_qty
        $batchMeta = [];  // batch_id => ['supplier_name'=>..., 'batch_number'=>..., 'batch_seq'=>...]
        $batchSeqByElectric = []; // electric_id => next_seq_number
        $enhanced = [];

        foreach ($rows as $r) {
            $t = strtolower($r['type'] ?? '');
            $action = 'other';
            if (strpos($t, 'masuk') !== false || $t === 'in' || strpos($t, 'store') !== false) $action = 'in';
            elseif (strpos($t, 'keluar') !== false || $t === 'out' || strpos($t, 'ambil') !== false) $action = 'out';

            $r['action'] = $action;
            $r['display_amount'] = isset($r['display_amount']) ? (int)$r['display_amount'] : (int)($r[$pickedAmountCol] ?? ($r['qty_sisa'] ?? 0));

            // helper to determine a batch identifier string
            $determineBatchNumber = function($row) {
                if (isset($row['batch_number']) && $row['batch_number'] !== '') return $row['batch_number'];
                if (isset($row['po_number']) && $row['po_number'] !== '') return $row['po_number'];
                return (int)($row['id'] ?? 0);
            };

            if ($action === 'in') {
                $batchId = (int)($r['id'] ?? 0);
                $electricId = $r['electric_id'] ?? null;
                
                // Initialize batch sequence counter per electric_id
                if (!isset($batchSeqByElectric[$electricId])) {
                    $batchSeqByElectric[$electricId] = 1;
                }
                $batchSeq = $batchSeqByElectric[$electricId]++;

                $initial = 0;
                // WAJIB: ambil qty_sisa dari row Masuk (yang merupakan sisa batch terkini setelah Keluar consume)
                if (isset($r['qty_sisa'])) {
                    $initial = (int)$r['qty_sisa'];
                } elseif ($r['display_amount'] > 0) {
                    $initial = (int)$r['display_amount'];
                }

                $batchState[$batchId] = $initial;
                $supplier = $r['distributor'] ?? ($r['supplier_name'] ?? '');
                $batchNum = $determineBatchNumber($r);
                $batchMeta[$batchId] = [
                    'supplier_name' => $supplier, 
                    'batch_number' => $batchNum,
                    'batch_seq' => $batchSeq,
                    'initial_qty' => $initial
                ];

                $r['sisa_batch'] = $batchState[$batchId];
                $r['supplier_name'] = $supplier;
                $r['batch_number'] = $batchNum;
                $r['batch_seq'] = $batchSeq;
                $r['keterangan_dinamis'] = 'Barang masuk: Batch #' . $batchSeq . ' - Qty: ' . $initial . ' unit';
            } elseif ($action === 'out') {
                // PRIORITY 1: Use from_batch_id if available (explicit assignment)
                $refId = null;
                if (isset($r['from_batch_id']) && (int)$r['from_batch_id'] > 0) {
                    $refId = (int)$r['from_batch_id'];
                }
                // NOTE: Don't try to extract refId from keterangan - instead rely on FIFO in recalculateSisaBatchInPlace()
                // This prevents incorrect batch grouping when keterangan format varies

                $r['sisa_batch'] = '-';
                $r['supplier_name'] = $r['distributor'] ?? ($r['supplier_name'] ?? '');
                $r['batch_number'] = $r['po_number'] ?? (($r['batch_number'] ?? '') ?: ($r['id'] ?? ''));
                $r['batch_seq'] = '-'; // Will be set by recalculateSisaBatchInPlace() via FIFO
                $r['keterangan_dinamis'] = $r['keterangan'] ?? '';

                if ($refId) {
                    // ensure batch meta exists (fallback to rowsById)
                    if (!isset($batchMeta[$refId]) && isset($rowsById[$refId])) {
                        $br = $rowsById[$refId];
                        $br_electric = $br['electric_id'] ?? null;
                        if (!isset($batchSeqByElectric[$br_electric])) {
                            $batchSeqByElectric[$br_electric] = 1;
                        }
                        $br_seq = $batchSeqByElectric[$br_electric]++;
                        
                        $batchMeta[$refId] = [
                            'supplier_name' => $br['distributor'] ?? ($br['supplier_name'] ?? ''), 
                            'batch_number' => $determineBatchNumber($br),
                            'batch_seq' => $br_seq,
                            'initial_qty' => isset($br['qty_sisa']) ? (int)$br['qty_sisa'] : (int)($br['display_amount'] ?? 0)
                        ];
                        // WAJIB: gunakan qty_sisa dari batch Masuk, bukan display_amount (qty)
                        $batchState[$refId] = isset($br['qty_sisa']) ? (int)$br['qty_sisa'] : (int)($br['display_amount'] ?? 0);
                    }

                    $take = (int)$r['display_amount'];
                    if (!isset($batchState[$refId])) {
                        // try to load batch row from DB as fallback
                        $batchRow = $this->db->get_where($this->table, ['id' => $refId])->row_array();
                        if ($batchRow) {
                            $br_electric = $batchRow['electric_id'] ?? null;
                            if (!isset($batchSeqByElectric[$br_electric])) {
                                $batchSeqByElectric[$br_electric] = 1;
                            }
                            $br_seq = $batchSeqByElectric[$br_electric]++;
                            
                            // WAJIB: prioritas qty_sisa (remaining qty), bukan qty/amount (initial qty)
                            $batchState[$refId] = isset($batchRow['qty_sisa']) ? (int)$batchRow['qty_sisa'] : (int)($batchRow[$pickedAmountCol] ?? 0);
                            $batchMeta[$refId] = [
                                'supplier_name' => $batchRow['distributor'] ?? ($batchRow['supplier_name'] ?? ''), 
                                'batch_number' => $determineBatchNumber($batchRow),
                                'batch_seq' => $br_seq,
                                'initial_qty' => $batchState[$refId]
                            ];
                        } else {
                            $batchState[$refId] = 0;
                            $batchMeta[$refId] = ['supplier_name' => '', 'batch_number' => $refId, 'batch_seq' => '-', 'initial_qty' => 0];
                        }
                    }

                    // Before qty: sisa_batch_sebelumnya
                    $beforeQty = $batchState[$refId] + $take;
                    
                    // Decrement: sisa_batch_sekarang = sisa_batch_sebelumnya - qty_keluar
                    // WAJIB: kalkulasi selisih batch menggunakan rumus yang benar
                    $batchState[$refId] = max(0, (int)$batchState[$refId] - $take);
                    $r['sisa_batch'] = $batchState[$refId];
                    $r['supplier_name'] = $batchMeta[$refId]['supplier_name'] ?? '';
                    $r['batch_number'] = $batchMeta[$refId]['batch_number'] ?? $refId;
                    $r['batch_seq'] = $batchMeta[$refId]['batch_seq'] ?? '-';
                    $r['keterangan_dinamis'] = 'Keluar dari Batch #' . ($batchMeta[$refId]['batch_seq'] ?? $refId) . 
                        ': Ambil ' . $take . ' dari ' . $beforeQty . ' → Sisa ' . $batchState[$refId];
                }
            } else {
                // other types: keep defaults
                $r['sisa_batch'] = isset($r['qty_sisa']) ? (int)$r['qty_sisa'] : '-';
                $r['supplier_name'] = $r['distributor'] ?? ($r['supplier_name'] ?? '');
                $r['batch_number'] = $r['po_number'] ?? ($r['batch_number'] ?? ($r['id'] ?? ''));
                $r['batch_seq'] = '-';
                $r['keterangan_dinamis'] = $r['keterangan'] ?? '';
            }

            $enhanced[] = $r;
        }

        // Recalculate sisa_batch values on-the-fly to ensure accuracy regardless of database state
        // This ensures even old data shows correct FIFO calculations
        // Only updates sisa_batch field, preserves all other enriched fields
        $this->recalculateSisaBatchInPlace($enhanced);

        // return oldest-first (FIFO order) for accurate FIFO chain display
        // This ensures batch sequence numbering is correct and flow is chronological
        return $enhanced;
    }

    /**
     * Get total available stock (qty_sisa > 0) untuk barang tertentu
     * FIFO-based calculation for accurate inventory tracking
     *
     * @param string $electricId
     * @return int Total stok tersedia
     */
    public function getTotalAvailableStock(string $electricId): int
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        // Prefer qty_sisa if available (authoritative for FIFO)
        if ($this->db->field_exists('qty_sisa', $this->table)) {
            $row = $this->db
                ->select('SUM(qty_sisa) as total')
                ->where('electric_id', $electricId)
                ->where('qty_sisa >', 0)
                ->where('type', 'Masuk')
                ->get($this->table)
                ->row_array();
            return isset($row['total']) && $row['total'] !== null ? (int)$row['total'] : 0;
        }

        // Fallback: sum qty column from Masuk transactions
        $row = $this->db
            ->select('SUM(qty) as total')
            ->where('electric_id', $electricId)
            ->where_in('type', ['Masuk', 'In'])
            ->get($this->table)
            ->row_array();
        return isset($row['total']) && $row['total'] !== null ? (int)$row['total'] : 0;
    }
}

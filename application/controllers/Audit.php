<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Audit extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) redirect(base_url());
        // Allow Admin and Manager OE to use Audit (Manager OE is read-only via view logic)
        if (!(is_admin() || is_manajer_oe())) {
            $this->session->set_flashdata('action', ['danger', 'Akses ditolak. Hanya Admin dan Manager OE yang dapat mengakses fitur ini.']);
            redirect(base_url());
        }
        $this->load->model(['Electric_model', 'History_model']);
        $this->load->helper('url');
    }

    private function get_system_stock(string $electricId): int
    {
        // Prefer authoritative as_history.qty_sisa if available
        if ($this->db->table_exists('as_history') && $this->db->field_exists('qty_sisa', 'as_history')) {
            $row = $this->db->select('SUM(qty_sisa) as total')->where('electric_id', $electricId)->get('as_history')->row_array();
            return isset($row['total']) ? (int) $row['total'] : 0;
        }

        // Fall back to as_storage totals
        if ($this->db->table_exists('as_storage')) {
            $this->load->model('Storage_model');
            return (int) $this->Storage_model->get_total_stock($electricId);
        }

        // Lastly, fall back to as_electric.stock column
        if ($this->db->table_exists('as_electric') && $this->db->field_exists('stock', 'as_electric')) {
            $row = $this->db->get_where('as_electric', ['electric_id' => $electricId])->row_array();
            return isset($row['stock']) ? (int) $row['stock'] : 0;
        }

        return 0;
    }

    public function index()
    {
        $lokasiId = $this->input->get('lokasi_id') ?? null;
        
        // Get all electrics or filter by location
        if (!empty($lokasiId)) {
            $electrics = $this->Electric_model->getByLocation($lokasiId);
        } else {
            $electrics = $this->Electric_model->getAllElectrics();
        }

        $data = [
            'title' => 'Audit / Stock Opname',
            'electrics' => $electrics,
        ];
        render_view('audit/index', $data);
    }

    public function adjust()
    {
        // Only Admin can adjust audit
        if (!is_admin()) {
            set_message(['danger', 'Akses ditolak. Hanya Admin yang dapat melakukan penyesuaian audit.']);
            redirect('audit');
            return;
        }
        
        if ($this->input->method() !== 'post') redirect('audit');

        $electric_id = $this->input->post('electric_id', true);
        $counted = (int) $this->input->post('counted', true);
        $note = $this->input->post('note', true);
        $location_id = $this->input->post('location_id', true);

        if (!$electric_id || $counted < 0) {
            set_message(['danger', 'Data tidak valid']);
            redirect('audit');
            return;
        }

        $systemStock = $this->get_system_stock($electric_id);
        $diff = $counted - $systemStock;

        if ($diff === 0) {
            set_message(['success', 'Tidak terdapat selisih antara sistem dan hasil hitung.']);
            redirect('audit');
            return;
        }

        $user_nik = $this->session->userdata('user_data')['nik'] ?? null;
        $historyTable = $this->db->table_exists('as_history') ? 'as_history' : ($this->db->table_exists('history') ? 'history' : null);

        if (!$historyTable) {
            set_message(['danger', 'Tabel history tidak ditemukan.']);
            redirect('audit');
            return;
        }

        $this->load->model('Id_generator_model');
        date_default_timezone_set('Asia/Jakarta');
        $now = date('Y-m-d H:i:s');

        // Detect qty column
        $possibleQtyCols = ['qty', 'quantity', 'jumlah', 'amount', 'jml', 'qty_masuk', 'jumlah_masuk', 'quantity_masuk'];
        $qtyCol = null;
        foreach ($possibleQtyCols as $c) {
            if ($this->db->field_exists($c, $historyTable)) { $qtyCol = $c; break; }
        }

        // Create AUDIT record FIRST with counted amount
        // For Audit: qty = abs(diff) = amount of discrepancy, qty_sisa = counted = final verified quantity
        $auditId = (int)$this->Id_generator_model->generate_manual_id($historyTable, 'id');
        $auditInsert = [
            'id' => $auditId,
            'electric_id' => $electric_id,
            'type' => 'Audit',
            'user_nik' => $user_nik,
            'keterangan' => 'Stock Opname: system=' . $systemStock . ', counted=' . $counted . ', diff=' . $diff . '. ' . ($note ?? ''),
        ];
        if ($qtyCol !== null) $auditInsert[$qtyCol] = abs($diff);  // amount of discrepancy
        if ($this->db->field_exists('qty_sisa', $historyTable)) $auditInsert['qty_sisa'] = 0;  // Audit log does not hold batch stock
        if ($this->db->field_exists('tanggal_terima', $historyTable)) $auditInsert['tanggal_terima'] = $now;
        elseif ($this->db->field_exists('date', $historyTable)) $auditInsert['date'] = $now;
        else $auditInsert['created_at'] = $now;

        $auditOk = $this->db->insert($historyTable, $auditInsert);

        if (!$auditOk) {
            set_message(['danger', 'Gagal menyimpan audit adjustment.']);
            redirect('audit');
            return;
        }

        // If diff > 0 (surplus/found more), create IN transaction
        if ($diff > 0) {
            $inId = (int)$this->Id_generator_model->generate_manual_id($historyTable, 'id');
            $inInsert = [
                'id' => $inId,
                'electric_id' => $electric_id,
                'type' => 'Masuk',
                'user_nik' => $user_nik,
                'keterangan' => 'Audit surplus adjustment (' . $diff . ' units)',
            ];
            if ($qtyCol !== null) $inInsert[$qtyCol] = $diff;  // surplus amount
            if ($this->db->field_exists('qty_sisa', $historyTable)) $inInsert['qty_sisa'] = $diff;  // ONLY the surplus amount is available in this batch
            if ($this->db->field_exists('tanggal_terima', $historyTable)) $inInsert['tanggal_terima'] = $now;
            elseif ($this->db->field_exists('date', $historyTable)) $inInsert['date'] = $now;
            else $inInsert['created_at'] = $now;

            $this->db->insert($historyTable, $inInsert);
        } 
        // If diff < 0 (shortage/found less), create OUT transaction
        else if ($diff < 0) {
            $absQty = abs($diff);
            
            // Get latest batches with remaining qty_sisa for this item
            // to properly assign which batch(es) to reduce
            $batchRows = $this->db->select('id, keterangan, qty_sisa')
                ->where('electric_id', $electric_id)
                ->where('type', 'Masuk')
                ->where('qty_sisa >', 0)
                ->order_by('tanggal_terima', 'ASC')  // FIFO: oldest first
                ->get($historyTable)->result_array();

            $remainingToReduce = $absQty;
            $batchRef = null;

            foreach ($batchRows as $batch) {
                if ($remainingToReduce <= 0) break;

                $batchSisa = (int)($batch['qty_sisa'] ?? 0);
                if ($batchSisa <= 0) continue;

                // Extract batch number from keterangan if available
                if (preg_match('/#(\d+)/', $batch['keterangan'], $m)) {
                    $batchNum = $m[1];
                } else {
                    $batchNum = $batch['id'];
                }

                // Use first batch as reference for Keluar record
                if ($batchRef === null) {
                    $batchRef = '#' . $batchNum;
                }

                // Reduce this batch's qty_sisa
                $toReduce = min($remainingToReduce, $batchSisa);
                $newSisa = $batchSisa - $toReduce;

                $this->db->where('id', $batch['id'])->update($historyTable, ['qty_sisa' => $newSisa]);

                $remainingToReduce -= $toReduce;
            }

            // Create OUT record with proper batch reference
            $outId = (int)$this->Id_generator_model->generate_manual_id($historyTable, 'id');
            $outInsert = [
                'id' => $outId,
                'electric_id' => $electric_id,
                'type' => 'Keluar',
                'user_nik' => $user_nik,
                'keterangan' => 'Audit shortage adjustment (' . $absQty . ' units). ' . ($batchRef ? 'From ' . $batchRef : '') . ($note ? ' Reason: ' . $note : ''),
            ];
            if ($qtyCol !== null) $outInsert[$qtyCol] = $absQty;  // shortage amount
            if ($this->db->field_exists('qty_sisa', $historyTable)) $outInsert['qty_sisa'] = $counted;  // final verified quantity
            if ($this->db->field_exists('tanggal_terima', $historyTable)) $outInsert['tanggal_terima'] = $now;
            elseif ($this->db->field_exists('date', $historyTable)) $outInsert['date'] = $now;
            else $outInsert['created_at'] = $now;

            $this->db->insert($historyTable, $outInsert);
        }

        // Recompute as_electric.stock if exists
        if ($this->db->table_exists('as_electric') && $this->db->field_exists('stock', 'as_electric') && $this->db->field_exists('qty_sisa', $historyTable)) {
            $row = $this->db->select('SUM(qty_sisa) as total')->where('electric_id', $electric_id)->get($historyTable)->row_array();
            $newStock = isset($row['total']) ? (int)$row['total'] : 0;
            $this->db->where('electric_id', $electric_id)->update('as_electric', ['stock' => $newStock]);
        }

        set_message(['success', 'Audit adjustment berhasil disimpan.']);
        redirect('audit?lokasi_id=' . $location_id);
    }

    public function export_audit()
    {
        if (!(is_admin() || is_manajer_oe())) {
            set_message(['danger', 'Anda tidak memiliki akses untuk mengunduh laporan.']);
            redirect('audit');
            return;
        }

        $lokasiId = $this->input->get('lokasi_id') ?? null;
        $historyTable = $this->db->table_exists('as_history') ? 'as_history' : ($this->db->table_exists('history') ? 'history' : null);

        if (!$historyTable) {
            set_message(['danger', 'Tabel history tidak ditemukan']);
            redirect('audit');
            return;
        }

        $lokasiId = $this->input->get('lokasi_id');
        $lokasi_nama = 'Semua_Lokasi';
        
        if (!empty($lokasiId) && $this->db->table_exists('as_location')) {
            if (is_array($lokasiId)) {
                // If multiple locations selected
                $locs = $this->db->where_in('id', $lokasiId)->get('as_location')->result_array();
                if (count($locs) === 1) {
                    $lokasi_nama = preg_replace('/[^a-zA-Z0-9]/', '_', $locs[0]['location_name']);
                } elseif (count($locs) > 1) {
                    $lokasi_nama = 'Multiple_Lokasi_' . count($locs);
                }
                
                // Get components for all selected locations
                $electrics = [];
                foreach ($lokasiId as $lid) {
                    $electrics = array_merge($electrics, $this->Electric_model->getByLocation($lid));
                }
            } else {
                // Single location (from inside the detail page)
                $loc = $this->db->get_where('as_location', ['id' => $lokasiId])->row_array();
                if ($loc) $lokasi_nama = preg_replace('/[^a-zA-Z0-9]/', '_', $loc['location_name']);
                $electrics = $this->Electric_model->getByLocation($lokasiId);
            }
        } else {
            $electrics = $this->Electric_model->getAllElectrics();
        }

        // Build a map of location IDs to Names
        $locMap = [];
        if ($this->db->table_exists('as_location')) {
            $allLocs = $this->db->get('as_location')->result_array();
            foreach ($allLocs as $l) {
                $locMap[$l['id']] = $l['location_name'] ?? $l['nama_lokasi'] ?? $l['id'];
            }
        }

        // Send CSV (Excel-friendly)
        $filename = 'form_stock_opname_' . $lokasi_nama . '_' . date('Y_m_d_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');

        // BOM so Excel recognizes UTF-8
        echo "\xEF\xBB\xBF";
        echo "sep=;\r\n";

        $delimiter = ';';
        $eol = "\r\n";

        $escape = function ($field) {
            $field = (string) ($field ?? '');
            $field = str_replace('"', '""', $field);
            return '"' . $field . '"';
        };

        // Header row
        $headers = ['No', 'ID Barang', 'Kategori', 'Tipe/Spesifikasi', 'Brand', 'Lokasi', 'Stok Sistem', 'Stok Fisik (Hasil Cek)', 'Keterangan'];
        fwrite($out, implode($delimiter, array_map($escape, $headers)) . $eol);

        $no = 1;
        foreach ($electrics as $item) {
            $stok = (int)($item['total_amount'] ?? $item['system_stock'] ?? $item['total_stock'] ?? $item['stock'] ?? 0);
            $rawLocId = $item['location'] ?? $item['location_id'] ?? $item['id_lokasi'] ?? '';
            $locName = $locMap[$rawLocId] ?? $rawLocId ?: '-';
            
            $line = [
                $no++,
                $item['electric_id'] ?? '-',
                $item['nama'] ?? '-',
                $item['type'] ?? '-',
                $item['brand'] ?? '-',
                $locName,
                $stok,
                '', // Empty column for physical check
                ''  // Empty column for notes
            ];
            fwrite($out, implode($delimiter, array_map($escape, $line)) . $eol);
        }

        fclose($out);
        exit;
    }

    public function export_hasil_audit()
    {
        $lokasiId = $this->input->get('lokasi_id');
        $lokasi_nama = 'Semua_Lokasi';
        
        $historyTable = $this->db->table_exists('as_history') ? 'as_history' : ($this->db->table_exists('history') ? 'history' : null);
        if (!$historyTable) {
            echo "Table history tidak ditemukan.";
            return;
        }

        if (!empty($lokasiId) && $this->db->table_exists('as_location')) {
            if (is_array($lokasiId)) {
                $locs = $this->db->where_in('id', $lokasiId)->get('as_location')->result_array();
                if (count($locs) === 1) $lokasi_nama = preg_replace('/[^a-zA-Z0-9]/', '_', $locs[0]['location_name']);
                elseif (count($locs) > 1) $lokasi_nama = 'Multiple_Lokasi_' . count($locs);
            } else {
                $loc = $this->db->get_where('as_location', ['id' => $lokasiId])->row_array();
                if ($loc) $lokasi_nama = preg_replace('/[^a-zA-Z0-9]/', '_', $loc['location_name']);
            }
        }

        $qtyField = null;
        foreach (['qty', 'quantity', 'jumlah', 'amount'] as $c) {
            if ($this->db->field_exists($c, $historyTable)) { $qtyField = $c; break; }
        }

        $dateField = null;
        foreach (['tanggal_terima', 'date', 'created_at'] as $c) {
            if ($this->db->field_exists($c, $historyTable)) { $dateField = $c; break; }
        }

        $today = date('Y-m-d 00:00:00');
        $tonight = date('Y-m-d 23:59:59');

        $this->db->select('h.electric_id, h.type, ' . ($qtyField ? 'h.' . $qtyField : '0') . ' as qty, h.keterangan');
        $this->db->from($historyTable . ' h');
        if ($this->db->table_exists('as_electric')) {
            $this->db->join('as_electric e', 'h.electric_id = e.electric_id', 'left');
            $this->db->select('e.nama, e.type as tipe, e.brand');
        }

        $this->db->where('h.type', 'Audit');
        $this->db->where('h.' . $dateField . ' >=', $today);
        $this->db->where('h.' . $dateField . ' <=', $tonight);

        if (!empty($lokasiId) && $this->db->table_exists('as_electric')) {
            if (is_array($lokasiId)) $this->db->where_in('e.location', $lokasiId);
            else $this->db->where('e.location', $lokasiId);
        }

        $this->db->order_by('h.' . $dateField, 'DESC');
        $auditRows = $this->db->get()->result_array();

        $filename = 'hasil_audit_' . $lokasi_nama . '_' . date('Y_m_d_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        echo "\xEF\xBB\xBF";
        echo "sep=;\r\n";

        $delimiter = ';';
        $eol = "\r\n";

        $escape = function ($field) {
            $field = (string) ($field ?? '');
            $field = str_replace('"', '""', $field);
            return '"' . $field . '"';
        };

        $headers = ['ID Barang', 'Kategori', 'Tipe/Spesifikasi', 'Brand', 'Selisih (Discrepancy)', 'Keterangan Hasil Audit'];
        fwrite($out, implode($delimiter, array_map($escape, $headers)) . $eol);

        foreach ($auditRows as $row) {
            preg_match('/diff=(-?\d+)/', $row['keterangan'], $matches);
            $selisih = isset($matches[1]) ? $matches[1] : $row['qty'];

            $line = [
                $row['electric_id'],
                $row['nama'] ?? '-',
                $row['tipe'] ?? '-',
                $row['brand'] ?? '-',
                $selisih,
                $row['keterangan']
            ];
            fwrite($out, implode($delimiter, array_map($escape, $line)) . $eol);
        }
        fclose($out);
        exit;
    }
}

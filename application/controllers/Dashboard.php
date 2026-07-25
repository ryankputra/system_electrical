<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        require_login();
        $this->load->model('Electric_model');
        $this->load->model('History_model');
    }

    public function index()
    {
        $isManajer = is_manajer_oe();
        $isTeknisi = is_teknisi();
        // Robust role detection: treat Manajer OE as read-only admin dashboard.
        if (!$isManajer && !$isTeknisi) {
            $userData = $this->session->userdata('user_data') ?? [];
            $role = $this->session->userdata('role') ?? ($userData['role'] ?? null);
            if (!empty($role)) {
                $r = strtolower(trim((string)$role));
                if (strpos($r, 'manajer oe') !== false || strpos($r, 'manajer_oe') !== false || strpos($r, 'manager oe') !== false || strpos($r, 'manager_oe') !== false) {
                    $isManajer = true;
                }
            }
        }

        // Render the single dashboard view for all roles (we removed the old teknisi view).
        // prepare_admin_dashboard internally uses prepare_teknisi_dashboard to build legacy payloads.
        $data = $this->prepare_admin_dashboard();
        render_view('dashboard/index', $data);
        return;
    }

    private function prepare_admin_dashboard(): array
    {
        $this->db->cache_off();
        date_default_timezone_set('Asia/Jakarta');

        // Load optional models used by dashboard reports
        $this->load->model(['Location_model', 'Electric_type_model', 'User_model']);

        // Keep the legacy dashboard card payload available so the current view
        // continues to render its existing totals and list sections.
        $legacyPayload = $this->prepare_teknisi_dashboard();

        // Admin Gudang and Manajer OE both receive the full dashboard payload.
        $locations = $this->Location_model->get_all();
        $total_locations = is_array($locations) ? count($locations) : 0;
        $total_items = (int) $this->Electric_model->countElectric(null, null);
        $total_users = (int) $this->User_model->countUser(null, null);
        $total_types = count($this->Electric_type_model->getAllTypes());
        $recentAll = $this->History_model->get_all_history();
        $recent_transactions = is_array($recentAll) ? array_slice($recentAll, 0, 10) : [];

        $thresholdParam = $this->input->get('threshold', true);
        $threshold = ($thresholdParam !== null && is_numeric($thresholdParam)) ? (int)$thresholdParam : 5;

        // Ambil stok rendah dari FIFO history (SUM qty_sisa), bukan dari as_electric.stock
        // agar sinkron dengan stok aktual setelah transaksi masuk/keluar
        $low_stock = [];
        if ($this->db->table_exists('as_history') && $this->db->field_exists('qty_sisa', 'as_history')) {
            $locationJoin = '';
            $locationSelect = "'' as location_name";
            if ($this->db->table_exists('as_location')) {
                if ($this->db->field_exists('location', 'as_electric')) {
                    $locationJoin = 'as_location l ON l.id = e.location';
                } elseif ($this->db->field_exists('location_id', 'as_electric')) {
                    $locationJoin = 'as_location l ON l.id = e.location_id';
                }
                if ($locationJoin) $locationSelect = "COALESCE(l.location_name, '-') as location_name";
            }

            $voltUnit  = $this->db->field_exists('voltage_unit', 'as_electric') ? "COALESCE(e.voltage_unit,'')" : "''";
            $dayaUnit  = $this->db->field_exists('daya_unit',   'as_electric') ? "COALESCE(e.daya_unit,'')"   : "''";

            $sql = "SELECT
                e.electric_id as type_id,
                e.nama as category,
                e.brand,
                e.type as spec_type,
                COALESCE(e.voltage,'') as voltage,
                {$voltUnit} as voltage_unit,
                COALESCE(e.ampere,'') as ampere,
                COALESCE(e.daya,'') as daya,
                {$dayaUnit} as daya_unit,
                {$locationSelect},
                COALESCE(SUM(h.qty_sisa), 0) as total_amount
            FROM as_electric e
            LEFT JOIN as_history h ON h.electric_id = e.electric_id
            " . ($locationJoin ? "LEFT JOIN {$locationJoin}" : '') . "
            GROUP BY e.electric_id, e.nama, e.brand, e.type, e.voltage, e.ampere, e.daya
            HAVING total_amount <= {$threshold}
            ORDER BY total_amount ASC";
            $low_stock = $this->db->query($sql)->result_array();
        } elseif ($this->db->field_exists('stock', 'as_electric')) {
            // Fallback: gunakan kolom stock dari as_electric
            $low_stock = $this->db->select('electric_id as type_id, nama as category, brand, stock as total_amount')
                ->where('stock <=', $threshold)
                ->order_by('stock', 'ASC')
                ->get('as_electric')
                ->result_array();
        }

        // Daily transactions for last 7 days
        $dailyMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $dailyMap[$d] = 0;
        }
        foreach ($recentAll as $row) {
            $date = substr($row['date'] ?? ($row['created_at'] ?? ''), 0, 10);
            if (isset($dailyMap[$date])) {
                $dailyMap[$date]++;
            }
        }
        $chart_labels = [];
        $chart_data = [];
        foreach ($dailyMap as $day => $count) {
            $chart_labels[] = date('d M', strtotime($day));
            $chart_data[] = $count;
        }

        $popular_items = [];
        if ($this->db->table_exists('as_history')) {
            $qtyField = null;
            foreach (['amount', 'qty', 'quantity', 'jumlah'] as $candidateField) {
                if ($this->db->field_exists($candidateField, 'as_history')) {
                    $qtyField = $candidateField;
                    break;
                }
            }

            if ($qtyField !== null) {
                $qtyExpr = 'SUM(CASE WHEN h.type = "Keluar" THEN COALESCE(h.' . $qtyField . ', 0) ELSE 0 END) AS stock';
            } elseif ($this->db->field_exists('qty_sisa', 'as_history')) {
                $qtyExpr = 'SUM(CASE WHEN h.type = "Keluar" THEN COALESCE(h.qty_sisa, 0) ELSE 0 END) AS stock';
            } else {
                $qtyExpr = 'COUNT(h.id) AS stock';
            }

            $popular_items = $this->db->select(
                    'e.electric_id, e.nama as name, COALESCE(l.location_name, "-") as location, ' . $qtyExpr,
                    false
                )
                ->from('as_history h')
                ->join('as_electric e', 'e.electric_id = h.electric_id', 'left')
                ->join('as_location l', 'l.id = e.location', 'left')
                ->where_in('h.type', ['Keluar', 'Keluar Barang', 'Out'])
                ->group_by(['e.electric_id', 'e.nama', 'l.location_name'])
                ->order_by('stock', 'DESC')
                ->limit(5)
                ->get()
                ->result_array();
        }

        return [
            'title'                => 'Dashboard',
            'total_stock'          => $legacyPayload['total_stock'] ?? 0,
            'total_lokasi'         => $legacyPayload['total_lokasi'] ?? 0,
            'barang_terlama'           => $legacyPayload['barang_terlama'] ?? '-',
            'barang_terlama_name'      => $legacyPayload['barang_terlama_name'] ?? ($legacyPayload['barang_terlama_barang'] ?? '-'),
            'barang_terlama_location'  => $legacyPayload['barang_terlama_location'] ?? '-',
            'barang_terlama_quantity'  => $legacyPayload['barang_terlama_quantity'] ?? 0,
            'barang_terlama_status'    => $legacyPayload['barang_terlama_status'] ?? '',
            'barang_terlama_list'      => $legacyPayload['barang_terlama_list'] ?? [],
            'items'                => $legacyPayload['items'] ?? [],
            'popular_items'        => $popular_items,
            'last_updated'         => $legacyPayload['last_updated'] ?? date('d M Y H:i'),
            'total_locations'      => $total_locations,
            'total_items'          => $total_items,
            'total_users'          => $total_users,
            'total_types'          => $total_types,
            'recent_transactions'  => $recent_transactions,
            'low_stock'            => $low_stock,
            'critical_stock_count' => is_array($low_stock) ? count($low_stock) : 0,
            'threshold'            => $threshold,
            'chart_labels'         => $chart_labels,
            'chart_data'           => $chart_data,
        ];
    }

    /**
     * Halaman detail Stok Rendah — tampil daftar lengkap barang di bawah threshold
     */
    public function low_stock_detail()
    {
        require_login();
        $thresholdParam = $this->input->get('threshold', true);
        $threshold = ($thresholdParam !== null && is_numeric($thresholdParam)) ? (int)$thresholdParam : 5;

        $low_stock = [];
        if ($this->db->table_exists('as_history') && $this->db->field_exists('qty_sisa', 'as_history')) {
            $locationJoin = '';
            $locationSelect = "'' as location_name";
            if ($this->db->table_exists('as_location')) {
                if ($this->db->field_exists('location', 'as_electric')) {
                    $locationJoin = 'as_location l ON l.id = e.location';
                } elseif ($this->db->field_exists('location_id', 'as_electric')) {
                    $locationJoin = 'as_location l ON l.id = e.location_id';
                }
                if ($locationJoin) $locationSelect = "COALESCE(l.location_name, '-') as location_name";
            }

            $voltUnit = $this->db->field_exists('voltage_unit', 'as_electric') ? "COALESCE(e.voltage_unit,'')" : "''";
            $dayaUnit = $this->db->field_exists('daya_unit',   'as_electric') ? "COALESCE(e.daya_unit,'')"   : "''";

            $sql = "SELECT
                e.electric_id as type_id,
                e.nama as category,
                e.brand,
                e.type as spec_type,
                COALESCE(e.voltage,'') as voltage,
                {$voltUnit} as voltage_unit,
                COALESCE(e.ampere,'') as ampere,
                COALESCE(e.daya,'') as daya,
                {$dayaUnit} as daya_unit,
                {$locationSelect},
                COALESCE(SUM(h.qty_sisa), 0) as total_amount
            FROM as_electric e
            LEFT JOIN as_history h ON h.electric_id = e.electric_id
            " . ($locationJoin ? "LEFT JOIN {$locationJoin}" : '') . "
            GROUP BY e.electric_id, e.nama, e.brand, e.type, e.voltage, e.ampere, e.daya
            HAVING total_amount <= {$threshold}
            ORDER BY total_amount ASC";
            $low_stock = $this->db->query($sql)->result_array();
        } elseif ($this->db->field_exists('stock', 'as_electric')) {
            $low_stock = $this->db->select('electric_id as type_id, nama as category, brand, stock as total_amount')
                ->where('stock <=', $threshold)
                ->order_by('stock', 'ASC')
                ->get('as_electric')
                ->result_array();
        }

        $data = [
            'title'     => 'Detail Stok Rendah',
            'low_stock' => $low_stock,
            'threshold' => $threshold,
        ];
        render_view('dashboard/low_stock_detail', $data);
    }

    private function prepare_teknisi_dashboard(): array
    {
        $this->db->cache_off();
        date_default_timezone_set('Asia/Jakarta');

        $historyTable = 'as_history';
        
        // 1. Hitung Total Stok (Sum dari qty_sisa)
        $total_stock = 0;
        if ($this->db->table_exists($historyTable)) {
            $stok_row = $this->db->select_sum('qty_sisa', 'total')->get($historyTable)->row_array();
            $total_stock = (int)($stok_row['total'] ?? 0);
        }

        // 2. Hitung Total Lokasi
        $total_lokasi = $this->db->table_exists('as_location') ? $this->db->count_all('as_location') : 0;

        // 3. Batch FIFO Terlama per Tipe — ambil batch tertua untuk setiap type_id yang masih punya sisa (>0)
        $barang_terlama = '-';
        $barang_terlama_name = '-';
        $barang_terlama_location = '-';
        $barang_terlama_quantity = 0;
        $barang_terlama_status = '';
        $barang_terlama_list = [];
        if ($this->db->table_exists($historyTable)) {
            // Detect which quantity-like column exists in the history table
            $qtyCols = ['qty_sisa', 'quantity_remaining', 'quantity', 'qty', 'amount', 'jumlah'];
            $pickedQtyCol = null;
            foreach ($qtyCols as $col) {
                if ($this->db->field_exists($col, $historyTable)) {
                    $pickedQtyCol = $col;
                    break;
                }
            }

            // Detect best date column to use for FIFO ordering
            $dateCandidates = ['created_at', 'tanggal_terima', 'date', 'tanggal_masuk', 'tgl_masuk'];
            $pickedDateCol = 'created_at';
            foreach ($dateCandidates as $dc) {
                if ($this->db->field_exists($dc, $historyTable)) { $pickedDateCol = $dc; break; }
            }

            // Determine batch display column (batch_number / po_number / fallback to id)
            if ($this->db->field_exists('batch_number', $historyTable)) {
                $batchField = 'batch_number';
            } elseif ($this->db->field_exists('po_number', $historyTable)) {
                $batchField = 'po_number';
            } else {
                $batchField = 'id';
            }

            // Build subquery: earliest stored date per type_id where remaining qty > 0
            $minSelect = $this->db
                ->select('e.type_id, MIN(h.' . $pickedDateCol . ') as min_created_at', false)
                ->from($historyTable . ' h')
                ->join('as_electric e', 'e.electric_id = h.electric_id', 'left')
                ->where(($pickedQtyCol ? 'h.' . $pickedQtyCol : '1') . ' >', 0)
                ->where_in('h.type', ['Masuk', 'In', 'masuk'])
                ->group_by('e.type_id')
                ->get_compiled_select();

            // Main query: pick the batch rows that match the earliest per type
            $pickedQtyExpr = ($pickedQtyCol ? 'COALESCE(h.' . $pickedQtyCol . ',0)' : 'COALESCE(h.qty_sisa,0)');
            $sql = 'SELECT COALESCE(t.type, "-") as type_name, COALESCE(h.' . $batchField . ', "-") as batch_number, h.' . $pickedDateCol . ' as date_stored, ' . $pickedQtyExpr . ' as quantity_remaining'
                . ' FROM ' . $historyTable . ' h'
                . ' JOIN as_electric e ON e.electric_id = h.electric_id'
                . ' LEFT JOIN as_electric_types t ON t.id = e.type_id'
                . ' JOIN (' . $minSelect . ') s ON s.type_id = e.type_id AND s.min_created_at = h.' . $pickedDateCol
                . ' WHERE ' . $pickedQtyExpr . ' > 0'
                . ' ORDER BY h.' . $pickedDateCol . ' ASC';

            $rows = $this->db->query($sql)->result_array();

            // Format list with status badges
            $nearEmptyThreshold = 5;
            foreach ($rows as $r) {
                $qtyRem = isset($r['quantity_remaining']) ? (int)$r['quantity_remaining'] : 0;
                if ($qtyRem <= 0) $status = "<span class='badge bg-danger'>Habis</span>";
                elseif ($qtyRem <= $nearEmptyThreshold) $status = "<span class='badge bg-warning'>Hampir Habis</span>";
                else $status = "<span class='badge bg-success'>Aktif</span>";

                $barang_terlama_list[] = [
                    'type_name' => $r['type_name'] ?? '-',
                    'batch_number' => $r['batch_number'] ?? '-',
                    'date_stored' => $r['date_stored'] ?? null,
                    'quantity_remaining' => $qtyRem,
                    'status_batch' => $status,
                ];
            }

            // Keep backward-compatible single-item fields populated from first list element
            if (!empty($barang_terlama_list)) {
                $first = $barang_terlama_list[0];
                $barang_terlama = $first['date_stored'] ? date('d M Y', strtotime($first['date_stored'])) : '-';
                $barang_terlama_name = $first['type_name'] ?? '-';
                $barang_terlama_location = '-';
                $barang_terlama_quantity = $first['quantity_remaining'] ?? 0;
                $barang_terlama_status = $first['status_batch'] ?? '';
            }
        }

        // 4. Query Utama — JOIN ke master type dan lokasi, stok berasal dari SUM(qty_sisa)
        $this->db->select(
            'e.electric_id, '
            . 'e.nama, '
            . 'e.brand, '
            . 'e.type_id as type_id, '
            . "COALESCE(l.location_name, '-') as display_location, "
            . "COALESCE(t.type, '-') as type_name, "
            . 'COALESCE(hs.history_stock, 0) as stock'
        );
        $this->db->from('as_electric e');

        // Subquery Stok FIFO
        $historySub = "(SELECT electric_id, SUM(qty_sisa) as history_stock FROM {$historyTable} GROUP BY electric_id)";
        $this->db->join($historySub . ' hs', 'hs.electric_id = e.electric_id', 'left', false);

        // Join ke Lokasi (pasti menghubungkan e.location -> as_location.id)
        if ($this->db->table_exists('as_location')) {
            $this->db->join('as_location l', 'l.id = e.location', 'left');
        }

        // Join ke Type master (e.type_id -> as_electric_types.id)
        if ($this->db->table_exists('as_electric_types')) {
            $this->db->join('as_electric_types t', 't.id = e.type_id', 'left');
        }

        $this->db->order_by('stock', 'DESC');
        $this->db->limit(10);

        $items_data = $this->db->get()->result_array();

        return [
            'title' => 'Dashboard Teknisi',
            'total_stock' => $total_stock,
            'total_lokasi' => $total_lokasi,
            'barang_terlama' => $barang_terlama,
            'barang_terlama_name' => $barang_terlama_name,
            'barang_terlama_location' => $barang_terlama_location,
            'barang_terlama_quantity' => $barang_terlama_quantity,
            'barang_terlama_status' => $barang_terlama_status,
            'items' => $items_data,
            'last_updated' => date('d M Y H:i')
        ];
    }

    /**
     * Download monthly report (CSV) - accessible to Admin Gudang and Manajer OE
     */
    public function download_monthly()
    {
        if (!(is_admin() || is_manajer_oe())) {
            set_message(['danger', 'Anda tidak memiliki akses untuk mengunduh laporan.']);
            redirect('dashboard');
            return;
        }

        $histTable = $this->db->table_exists('as_history') ? 'as_history' : ($this->db->table_exists('history') ? 'history' : null);
        if ($histTable === null) {
            set_message(['danger', 'Tabel riwayat tidak ditemukan']);
            redirect('dashboard');
            return;
        }

        // determine date field
        $dateCandidates = ['tanggal_terima', 'date', 'tanggal_masuk', 'tgl_masuk', 'created_at'];
        $dateField = null;
        foreach ($dateCandidates as $c) {
            if ($this->db->field_exists($c, $histTable)) {
                $dateField = $c;
                break;
            }
        }
        if ($dateField === null) $dateField = 'date';

        $start = date('Y-m-01 00:00:00');
        $end = date('Y-m-t 23:59:59');

        // Build base query selecting needed fields and optional lokasi
        $select = 'h.*';
        if ($this->db->table_exists('as_electric')) {
            $select .= ', e.nama as nama_barang, e.electric_id as electric_code, e.type, e.brand';
        }
        if ($this->db->table_exists('as_user')) {
            $select .= ', u.name as user_name, u.nik as user_nik';
        }
        if ($this->db->table_exists('as_electric') && $this->db->table_exists('as_location')) {
            $select .= ', COALESCE(l.location_name, "") as lokasi';
        }

        $this->db->select($select, false);
        $this->db->from($histTable . ' h');
        if ($this->db->table_exists('as_electric')) $this->db->join('as_electric e', 'h.electric_id = e.electric_id', 'left');
        if ($this->db->table_exists('as_location') && $this->db->table_exists('as_electric')) $this->db->join('as_location l', 'e.location = l.id', 'left');
        if ($this->db->table_exists('as_user')) $this->db->join('as_user u', 'h.user_nik = u.nik', 'left');

        $this->db->where('h.' . $dateField . ' >=', $start);
        $this->db->where('h.' . $dateField . ' <=', $end);
        $this->db->order_by('h.' . $dateField, 'DESC');
        $rows = $this->db->get()->result_array();

        // detect qty column
        $qtyField = null;
        foreach (['amount', 'qty', 'quantity', 'jumlah'] as $c) {
            if ($this->db->field_exists($c, $histTable)) {
                $qtyField = $c;
                break;
            }
        }
        $hasQtySisa = $this->db->field_exists('qty_sisa', $histTable);

        $data['title'] = 'Laporan Bulanan ' . date('F Y');
        $data['rows'] = $rows;
        $data['dateField'] = $dateField;
        $data['qtyField'] = $qtyField;

        // Render PDF Print View
        $this->load->view('dashboard/print_pdf', $data);
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) redirect(base_url());
        $this->load->model('History_model');
    }
    /**
     * Show list of all history records.
     */
    public function index()
    {
        $this->load->model('Electric_model');
        $history = $this->History_model->get_all_history();
        $data = [
            'title' => 'History Transaksi Stok',
            'history' => $history,
        ];
        render_view('history/index', $data);
    }

    public function masuk()
    {
        $start_date  = $this->input->get('start_date');
        $end_date    = $this->input->get('end_date');
        $electric_id = $this->input->get('electric_id', true);

        $history = $this->History_model->get_all_history($start_date, $end_date, 'Masuk');

        // Filter per barang jika dipilih
        if (!empty($electric_id)) {
            $history = array_values(array_filter($history, fn($r) => ($r['electric_id'] ?? '') === $electric_id));
        }

        $this->load->model('Electric_model');
        $data = [
            'title'       => 'Laporan Barang Masuk',
            'history'     => $history,
            'start_date'  => $start_date,
            'end_date'    => $end_date,
            'electric_id' => $electric_id,
            'electrics'   => $this->Electric_model->getAllElectrics(),
        ];
        render_view('history/laporan_masuk', $data);
    }

    public function keluar()
    {
        $start_date  = $this->input->get('start_date');
        $end_date    = $this->input->get('end_date');
        $electric_id = $this->input->get('electric_id', true);

        $history = $this->History_model->get_all_history($start_date, $end_date, 'Keluar');

        // Filter per barang jika dipilih
        if (!empty($electric_id)) {
            $history = array_values(array_filter($history, fn($r) => ($r['electric_id'] ?? '') === $electric_id));
        }

        $this->load->model('Electric_model');
        $data = [
            'title'       => 'Laporan Barang Keluar',
            'history'     => $history,
            'start_date'  => $start_date,
            'end_date'    => $end_date,
            'electric_id' => $electric_id,
            'electrics'   => $this->Electric_model->getAllElectrics(),
        ];
        render_view('history/laporan_keluar', $data);
    }

    public function print_masuk()
    {
        $start_date  = $this->input->get('start_date');
        $end_date    = $this->input->get('end_date');
        $electric_id = $this->input->get('electric_id', true);

        $history = $this->History_model->get_all_history($start_date, $end_date, 'Masuk');

        if (!empty($electric_id)) {
            $history = array_values(array_filter($history, fn($r) => ($r['electric_id'] ?? '') === $electric_id));
        }

        // Tentukan label barang untuk judul PDF
        $electric_label = '';
        if (!empty($electric_id) && !empty($history)) {
            $first = $history[0];
            $electric_label = trim(($first['nama_barang'] ?? '') . ' ' . ($first['spec_type'] ?? '') . ' ' . ($first['brand'] ?? ''));
        }

        $data = [
            'title'          => 'Laporan Barang Masuk',
            'history'        => $history,
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'electric_id'    => $electric_id,
            'electric_label' => $electric_label,
        ];
        $this->load->view('history/print_pdf_masuk', $data);
    }

    public function print_keluar()
    {
        $start_date  = $this->input->get('start_date');
        $end_date    = $this->input->get('end_date');
        $electric_id = $this->input->get('electric_id', true);

        $history = $this->History_model->get_all_history($start_date, $end_date, 'Keluar');

        if (!empty($electric_id)) {
            $history = array_values(array_filter($history, fn($r) => ($r['electric_id'] ?? '') === $electric_id));
        }

        $electric_label = '';
        if (!empty($electric_id) && !empty($history)) {
            $first = $history[0];
            $electric_label = trim(($first['nama_barang'] ?? '') . ' ' . ($first['spec_type'] ?? '') . ' ' . ($first['brand'] ?? ''));
        }

        $data = [
            'title'          => 'Laporan Barang Keluar',
            'history'        => $history,
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'electric_id'    => $electric_id,
            'electric_label' => $electric_label,
        ];
        $this->load->view('history/print_pdf_keluar', $data);
    }

    /**
     * Form to record incoming stock (Masuk) and handle POST.
     */
    public function in()
    {
        $this->load->model('Electric_model');
        if ($this->input->method() === 'post') {
            $electric_id = $this->input->post('electric_id', true);
            $qty = (int) $this->input->post('qty', true);
            $keterangan = $this->input->post('keterangan', true);
            $user_nik = $this->session->userdata('user_data')['nik'] ?? null;

            if (!$electric_id || $qty <= 0) {
                set_message(['danger', 'Data tidak valid']);
                redirect('history/in');
                return;
            }

            $res = $this->History_model->insert_history([
                'electric_id' => $electric_id,
                'type' => 'Masuk',
                'qty' => $qty,
                'user_nik' => $user_nik,
                'keterangan' => $keterangan,
            ]);

            if ($res['success']) set_message(['success', 'Transaksi Masuk disimpan (ID: ' . $res['id'] . ')']);
            else set_message(['danger', 'Gagal menyimpan transaksi: ' . $res['message']]);

            redirect('history');
            return;
        }

        $electrics = $this->Electric_model->getAllElectrics();
        $data = ['title' => 'Catat Barang Masuk', 'electrics' => $electrics];
        render_view('history/in', $data);
    }

    /**
     * Form to record outgoing stock (Keluar) and handle POST.
     */
    public function out()
    {
        // Allow both Admin and Technician to perform 'Keluar' (take) operations.
        $this->load->model('Electric_model');
        if ($this->input->method() === 'post') {
            $electric_id = $this->input->post('electric_id', true);
            $qty = (int) $this->input->post('qty', true);
            $keterangan = $this->input->post('keterangan', true);
            $user_nik = $this->session->userdata('user_data')['nik'] ?? null;
            $wo_id = $this->input->post('wo_id', true) ?: null;

            if ($qty <= 0 || !$wo_id || !$electric_id) {
                set_message(['danger', 'Data pengajuan tidak valid. Pastikan memilih WO, Barang, dan Qty.']);
                redirect('history/out');
                return;
            }

            // Insert into as_wo_details instead of cutting stock directly
            $data = [
                'wo_id' => $wo_id,
                'electric_id' => $electric_id,
                'qty' => $qty,
                'user_nik' => $user_nik,
                'status' => 'Pending',
                'keterangan' => $keterangan,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->db->insert('as_wo_details', $data)) {
                set_message(['success', 'Pengajuan (Request) barang berhasil dikirim. Menunggu Approval Staf Gudang.']);
                redirect('history/mine');
            } else {
                set_message(['danger', 'Gagal mengirim pengajuan.']);
                redirect('history/out');
            }
            return;
        }

        $this->load->model('Location_model');
        $this->load->model('Wo_model');
        
        $lokasi_id = $this->input->get('lokasi_id', true);
        
        $locations = $this->Location_model->get_all();
        $work_orders = $this->Wo_model->get_all();
        
        $barang_per_lokasi = [];
        if (!empty($lokasi_id)) {
            $all_electrics = $this->Electric_model->getAllElectrics();
            foreach ($all_electrics as $el) {
                if ((string)($el['location'] ?? '') === (string)$lokasi_id) {
                    if ((int)($el['total_stock'] ?? 0) > 0) {
                        // Get location name
                        $locRow = $this->db->get_where('as_location', ['id' => $lokasi_id])->row_array();
                        $el['location_name'] = $locRow ? $locRow['location_name'] : $lokasi_id;
                        $barang_per_lokasi[] = $el;
                    }
                }
            }
        }
        
        $data = [
            'title' => 'Catat Barang Keluar',
            'lokasi_id' => $lokasi_id,
            'locations' => $locations,
            'work_orders' => $work_orders,
            'barang_per_lokasi' => $barang_per_lokasi
        ];
        
        render_view('history/out', $data);
    }

    /**
     * Show digital stock card for a specific electric component.
     */
    public function stock_card()
    {
        $electric_id = $this->input->get('electric_id', true);
        $start_date = $this->input->get('start_date', true);
        $end_date = $this->input->get('end_date', true);

        $this->load->model('Electric_model');
        
        $all_history = $this->History_model->get_all_history($start_date, $end_date);
        $card_history = [];
        $running_balance = 0;
        
        if (!empty($electric_id)) {
            // Filter chronologically and compute running balance
            foreach ($all_history as $row) {
                if ((string)$row['electric_id'] === (string)$electric_id) {
                    $type = strtolower($row['type'] ?? '');
                    $qty = (int)($row['display_amount'] ?? 0);
                    
                    if (strpos($type, 'masuk') !== false || $type === 'in') {
                        $running_balance += $qty;
                    } elseif (strpos($type, 'keluar') !== false || $type === 'out') {
                        $running_balance -= $qty;
                    }
                    
                    $row['running_balance'] = $running_balance;
                    $card_history[] = $row;
                }
            }
        }
        
        $selected_electric = null;
        $active_batches = [];
        if (!empty($electric_id)) {
            $selected_electric = $this->Electric_model->getById($electric_id);
            if ($selected_electric) {
                $selected_electric['stock'] = !empty($card_history) ? $card_history[count($card_history) - 1]['running_balance'] : 0;
            }
            
            // Build map of database ID to sequential batch number
            $batch_seq_map = [];
            foreach ($card_history as $row) {
                $type = strtolower($row['type'] ?? '');
                $isMasuk = strpos($type, 'masuk') !== false || $type === 'in';
                if ($isMasuk && isset($row['batch_seq']) && $row['batch_seq'] !== '-') {
                    $batch_seq_map[(int)$row['id']] = $row['batch_seq'];
                }
            }
            
            // Fetch active batches for this electric and attach sequential batch display number
            $all_avail = $this->History_model->get_available_batches();
            foreach ($all_avail as $b) {
                if ((string)$b['electric_id'] === (string)$electric_id) {
                    $b_id = (int)$b['id'];
                    $b['batch_seq_display'] = isset($batch_seq_map[$b_id]) ? $batch_seq_map[$b_id] : $b_id;
                    $active_batches[] = $b;
                }
            }
        }
        
        $data = [
            'title' => 'Kartu Stok Suku Cadang',
            'electrics' => $this->Electric_model->getAllElectrics(),
            'categories' => $this->db->get('as_electric_types')->result_array(),
            'selected_id' => $electric_id,
            'selected_electric' => $selected_electric,
            'active_batches' => $active_batches,
            'card_history' => $card_history,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        
        render_view('history/stock_card', $data);
    }

    /**
     * Show outbound history for the currently logged in technician.
     */
    public function mine()
    {
        $user_nik = $this->session->userdata('user_data')['nik'] ?? null;
        $all_history = $this->History_model->get_all_history();
        $history = [];
        
        if ($user_nik) {
            foreach ($all_history as $row) {
                if ((string)($row['user_nik'] ?? '') === (string)$user_nik) {
                    $history[] = $row;
                }
            }
        }
        
        $pending_requests = [];
        if ($user_nik && $this->db->table_exists('as_wo_details')) {
            $this->db->select('wd.*, e.nama, e.brand, e.type as electric_type, wo.wo_number');
            $this->db->from('as_wo_details wd');
            $this->db->join('as_electric e', 'e.electric_id = wd.electric_id', 'left');
            $this->db->join('as_work_orders wo', 'wo.id = wd.wo_id', 'left');
            $this->db->where('wd.user_nik', $user_nik);
            
            $pending_requests = $this->db->order_by('wd.created_at', 'DESC')->get()->result_array();
        }
        
        $data = [
            'title' => 'Riwayat Pengambilan Saya',
            'history' => $history,
            'pending_requests' => $pending_requests
        ];
        render_view('history/mine', $data);
    }

    /**
     * View for thermal printer sticker print.
     */
    public function print_sticker($id) 
    {
        $data['history'] = $this->db->get_where('as_history', ['id' => $id])->row_array();
        if (!$data['history']) {
            show_404();
        }
        $this->load->model('Electric_model');
        $data['electric'] = $this->Electric_model->getById($data['history']['electric_id']);
        
        // Month names and color coding
        $months = [
            '01' => ['name' => 'JANUARI', 'color' => '#E74C3C'],   // Merah
            '02' => ['name' => 'FEBRUARI', 'color' => '#3498DB'],  // Biru
            '03' => ['name' => 'MARET', 'color' => '#F1C40F'],     // Kuning
            '04' => ['name' => 'APRIL', 'color' => '#2ECC71'],     // Hijau
            '05' => ['name' => 'MEI', 'color' => '#E67E22'],       // Oranye
            '06' => ['name' => 'JUNI', 'color' => '#9B59B6'],      // Ungu
            '07' => ['name' => 'JULI', 'color' => '#8E44AD'],      // Ungu Tua
            '08' => ['name' => 'AGUSTUS', 'color' => '#34495E'],   // Biru Dongker
            '09' => ['name' => 'SEPTEMBER', 'color' => '#1ABC9C'], // Tosca
            '10' => ['name' => 'OKTOBER', 'color' => '#D35400'],   // Coklat
            '11' => ['name' => 'NOVEMBER', 'color' => '#7F8C8D'],  // Abu-abu
            '12' => ['name' => 'DESEMBER', 'color' => '#2C3E50']   // Hitam
        ];
        
        $date = strtotime($data['history']['created_at']);
        $m = date('m', $date);
        
        $data['month_name'] = $months[$m]['name'];
        $data['month_color'] = $months[$m]['color'];
        
        $this->load->view('history/print_sticker', $data);
    }
}

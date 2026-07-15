<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Controller User
 * Mengelola data pengguna dan Dashboard Multi-role.
 * * @package OE-Inventory
 * @author Apparel One Indonesia
 */
class User extends CI_Controller
{
    private const CONFIG = [
        'pagination' => [
            'items_per_page' => 7
        ],
        'validation' => [
            'nik' => [
                'field' => 'nik',
                'label' => 'NIK',
                'rules' => 'required|is_unique[as_user.nik]',
                'errors' => [
                    'required'  => '%s harus diisi',
                    'is_unique' => '%s sudah terdaftar',
                ],
            ],
            'name' => [
                'field' => 'name',
                'label' => 'Nama',
                'rules' => 'trim|required',
                'errors' => [
                    'required' => '%s harus diisi',
                ],
            ],
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Proteksi: Cek apakah user sudah login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        $this->load->model('User_model');
        $this->load->library(['form_validation', 'pagination']);

        // Tracking controller untuk reset pencarian jika berpindah menu
        if ($this->session->userdata('controller') !== 'user') {
            $this->session->set_userdata('controller', 'user');
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * Halaman Dashboard Utama (Multi-role)
     */
    public function dashboard(): void
    {
        // Load model yang dibutuhkan untuk statistik dashboard
        $this->load->model(['Storage_model', 'Electric_model', 'Report_model']);

        $role = $this->session->userdata('role');

        // Data Dasar Dashboard
        $data = [
            'title'     => 'Dashboard ' . ucfirst($role),
            'user_name' => $this->session->userdata('name'),
            'user_role' => $role,
        ];

        // Jika Admin atau Warehouse, tampilkan statistik lengkap
        if ($role == 'admin' || $role == 'warehouse') {
            $data['total_locations'] = $this->db->count_all('as_location');
            $data['total_types']     = $this->db->count_all('as_electric');
            
            // Ambil transaksi terakhir (10 data)
            $data['recent_transactions'] = $this->Report_model->get_all_transactions(10, 0);
            
            // Logika Chart Transaksi 7 hari terakhir
            $start = date('Y-m-d', strtotime('-6 days'));
            $end = date('Y-m-d');
            $dailyRows = $this->Report_model->get_daily_summary($start, $end);
            
            $dailyMap = [];
            foreach ($dailyRows as $dr) {
                $dailyMap[$dr['transaction_date']] = (int)$dr['total_transactions'];
            }
            
            $data['chart_labels'] = [];
            $data['chart_data']   = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $data['chart_labels'][] = date('d M', strtotime($d));
                $data['chart_data'][]   = $dailyMap[$d] ?? 0;
            }
        }

        render_view('user/dashboard', $data);
    }

    /**
     * Manajemen User (Hanya Admin)
     */
    public function index(): void
    {
        // Proteksi: Hanya admin yang boleh kelola user
        if ($this->session->userdata('role') !== 'admin') {
            redirect('user/dashboard');
        }

        $this->handleFileUpload();
        $this->handleSessionState();

        $search = $this->session->userdata('keyword');
        $filter = $this->session->userdata('filter');
        $sort   = $this->session->userdata('sort');

        $config = [
            'base_url'   => site_url('user/index'),
            'total_rows' => $this->User_model->countUser($search, $filter),
            'per_page'   => self::CONFIG['pagination']['items_per_page'],
        ];
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ?? 0);
        $users = $this->User_model->getUser($config['per_page'], $startData, $search, $filter, $sort);

        $data = [
            'title'         => 'Manajemen Pengguna',
            'users'         => $users,
            'display'       => ($startData + 1) . ' - ' . ($startData + count($users)) . ' dari ' . $config['total_rows'],
            'searchKeyword' => $search,
            'pagination'    => $this->pagination->create_links(),
        ];

        render_view('user/index', $data);
    }

    public function add(): void
    {
        $this->form_validation->set_rules(self::CONFIG['validation']['nik']['field'], self::CONFIG['validation']['nik']['label'], self::CONFIG['validation']['nik']['rules'], self::CONFIG['validation']['nik']['errors']);
        $this->form_validation->set_rules(self::CONFIG['validation']['name']['field'], self::CONFIG['validation']['name']['label'], self::CONFIG['validation']['name']['rules'], self::CONFIG['validation']['name']['errors']);

        if ($this->form_validation->run() === false) {
            render_view('user/add', ['title' => 'Tambah Pengguna Baru']);
            return;
        }

        $this->User_model->addUser();
        set_message(['success', 'Pengguna berhasil ditambahkan']);
        redirect('user');
    }

    public function edit($nik = null): void
    {
        if (!$nik) show_404();

        $user = $this->User_model->getByNik($nik);
        if (!$user) {
            set_message(['danger', 'Pengguna tidak ditemukan']);
            redirect('user');
        }

        $this->form_validation->set_rules('name', 'Nama', 'required');

        if ($this->form_validation->run() === false) {
            render_view('user/edit', ['title' => 'Edit Pengguna', 'user' => $user]);
            return;
        }

        $this->User_model->editUser($nik);
        set_message(['success', 'Data berhasil diperbarui']);
        redirect('user');
    }

    public function delete($nik = null): void
    {
        if (!$nik) show_404();
        $this->User_model->deleteUser($nik);
        set_message(['success', 'Pengguna berhasil dihapus']);
        redirect('user');
    }

    private function handleFileUpload(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_FILES['file'])) return;

        $file = $_FILES['file']['tmp_name'];
        try {
            $spreadsheet = @IOFactory::load($file);
            $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            array_shift($data); // Hapus header

            $insertData = [];
            foreach ($data as $row) {
                if (empty($row['A']) || empty($row['B'])) continue;

                $insertData[] = [
                    'nik'        => $row['A'],
                    'name'       => ucwords(strtolower($row['B'])),
                    'password'   => password_hash('123456', PASSWORD_DEFAULT), // Default password
                    'role'       => 'staff', // Default role
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'editor'     => $this->session->userdata('nik'),
                ];
            }

            if (!empty($insertData)) {
                $this->User_model->insertBatch($insertData);
                set_message(['success', count($insertData) . ' data berhasil diimport dengan password default: 123456']);
            }
            redirect('user');
        } catch (Exception $e) {
            set_message(['danger', 'Gagal membaca file Excel']);
        }
    }

    private function handleSessionState(): void
    {
        if ($this->input->post('find')) $this->session->set_userdata('keyword', $this->input->post('keyword', true));
        if ($this->input->post('reset')) $this->session->unset_userdata(['keyword', 'sort', 'filter']);
    }
}
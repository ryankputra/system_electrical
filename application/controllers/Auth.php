<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authentication Controller
 *
 * Mengelola proses login dan logout. 
 * Mendukung Manual ID (NIK) dan Multi-role (Admin, Warehouse, Staff).
 *
 * @package OE-Inventory
 * @category Controllers
 * @version 1.1.0
 */
class Auth extends CI_Controller
{
    /**
     * Constructor
     * Memuat model User_model untuk verifikasi data.
     */
    public function __construct()
    {
        parent::__construct();
        // Memuat model (Pastikan file di models bernama User_model.php)
        $this->load->model('User_model');
    }

    /**
     * Halaman Utama Login
     */
    public function index(): void
    {
        // Cek jika user sudah login, langsung lempar ke dashboard yang sesuai
        if ($this->session->userdata('logged_in')) {
            $this->_redirect_by_role($this->session->userdata('role'));
            return;
        }

        $this->load->library('form_validation');

        // Aturan validasi input
        $this->form_validation->set_rules('nik', 'NIK', 'required', [
            'required' => 'NIK wajib diisi.'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required', [
            'required' => 'Password wajib diisi.'
        ]);

        if ($this->form_validation->run() == FALSE) {
            // Tampilkan view login jika validasi gagal atau baru akses halaman
            $this->load->view('auth/index');
        } else {
            // Proses pengecekan ke database
            $this->_login();
        }
    }

    /**
     * Logika verifikasi login
     */
    private function _login(): void
    {
        $nik = $this->input->post('nik', true);
        $password = $this->input->post('password', true);

        // Memanggil fungsi checkLogin di User_model
        $user = $this->User_model->checkLogin($nik, $password);

        if ($user) {
            // Menyiapkan data untuk disimpan di session
            $sessionData = [
                'nik'       => $user['nik'],
                'name'      => $user['name'],
                'role'      => $user['role'],
                'logged_in' => TRUE
            ];

            $this->session->set_userdata($sessionData);

            // Arahkan ke dashboard sesuai role masing-masing
            $this->_redirect_by_role($user['role']);
        } else {
            // Jika NIK tidak ditemukan atau Password salah
            $this->session->set_flashdata('error', 'NIK atau Password salah!');
            redirect('auth');
        }
    }

    /**
     * Helper untuk mengalihkan halaman berdasarkan role
     */
    private function _redirect_by_role(string $role): void
    {
        redirect('user/dashboard');
    }

    /**
     * Proses Logout
     */
    public function logout(): void
    {
        // Hapus semua data session
        $this->session->sess_destroy();
        
        // Opsional: tambahkan pesan sukses logout
        $this->session->set_flashdata('success', 'Anda telah berhasil keluar.');
        
        redirect('auth');
    }
}
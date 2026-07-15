<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        require_login(); // Pastikan user sudah login
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $userData = $this->session->userdata('user_data') ?? [];
        $data = [
            'title' => 'Profil & Password',
            'user'  => $userData
        ];
        render_view('profile/index', $data);
    }

    public function update_password()
    {
        $this->form_validation->set_rules('old_password', 'Password Lama', 'required');
        $this->form_validation->set_rules('new_password', 'Password Baru', 'required|min_length[5]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password Baru', 'required|matches[new_password]');

        if ($this->form_validation->run() == FALSE) {
            set_message(['danger', validation_errors()]);
            redirect('profile');
            return;
        }

        $userData = $this->session->userdata('user_data') ?? [];
        $nik = $userData['nik'] ?? '';

        // Ambil data user dari database untuk cek password lama
        $userDb = $this->db->get_where('as_user', ['nik' => $nik])->row_array();

        if (!$userDb) {
            set_message(['danger', 'Data pengguna tidak ditemukan.']);
            redirect('profile');
            return;
        }

        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');

        // Verifikasi password lama
        if (!password_verify($old_password, $userDb['password'])) {
            set_message(['danger', 'Password lama tidak sesuai.']);
            redirect('profile');
            return;
        }

        // Update ke password baru
        $hash_baru = password_hash($new_password, PASSWORD_DEFAULT);
        $this->db->where('nik', $nik);
        $this->db->update('as_user', ['password' => $hash_baru]);

        set_message(['success', 'Password berhasil diperbarui!']);
        redirect('profile');
    }
}


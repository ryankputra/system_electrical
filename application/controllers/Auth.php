<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }

    public function index() {
        // Validasi simpel
        $this->form_validation->set_rules('nik', 'NIK', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('auth/index');
        } else {
            $nik = $this->input->post('nik');
            $password = $this->input->post('password');

            // Cek ke database
            $user = $this->User_model->checkLogin($nik, $password);

            if ($user) {
                // Set session mirip kode lama kamu tapi ditambah status login
                $this->session->set_userdata([
                    'user_data' => $user,
                    'role'      => $user['role'],
                    'logged_in' => TRUE
                ]);
                redirect('user/dashboard');
            } else {
                // Jika salah, balik ke login
                $this->session->set_flashdata('error', 'NIK atau Password Salah!');
                redirect('auth');
            }
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }

    public function reset_password_saya()
{
    $password_baru = "admin123";
    $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
    
    $this->db->set('password', $hash_baru);
    $this->db->where('nik', '223016012');
    $this->db->update('as_user');

    echo "Password berhasil direset! Hash baru kamu adalah: " . $hash_baru;
}
}
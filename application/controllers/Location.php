<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Location extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }
        $this->load->model('Location_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'common']);
    }

    public function index(): void
    {
        $locations = $this->Location_model->get_all();
        $data = [
            'title' => 'Master Lokasi',
            'locations' => $locations,
        ];
        render_view('location/index', $data);
    }

    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('location_name', 'Nama Lokasi', 'required|trim|max_length[100]');
            if ($this->form_validation->run()) {
                $insert = $this->Location_model->insert(['location_name' => $this->input->post('location_name', true)]);
                if ($insert) {
                    set_message(['success', 'Lokasi berhasil ditambahkan.']);
                } else {
                    set_message(['danger', 'Gagal menambahkan lokasi.']);
                }
                redirect('location');
            }
        }
        redirect('location');
    }

    public function delete($id): void
    {
        $id = urldecode($id);

        // 1. Cek apakah lokasi ada
        $lokasi = $this->db->get_where('as_location', ['id' => $id])->row_array();
        if (!$lokasi) {
            set_message(['danger', 'Lokasi tidak ditemukan.']);
            redirect('location');
            return;
        }

        // 2. Cek apakah ada barang yang masih memakai lokasi ini
        $cek_barang = $this->db->get_where('as_electric', ['location' => $lokasi['location_name']])->num_rows();

        if ($cek_barang > 0) {
            // Jika ada barang, jangan hapus
            set_message(['danger', 'Gagal hapus! Masih ada ' . $cek_barang . ' barang di lokasi ' . $lokasi['location_name']]);
        } else {
            // Jika kosong, gunakan model untuk hapus (mengikuti aturan manual ID)
            $this->Location_model->delete($id);
            set_message(['success', 'Lokasi berhasil dihapus!']);
        }
        redirect('location');
    }
}

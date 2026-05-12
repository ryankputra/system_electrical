<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi_stok extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }
        $this->load->model('History_model');
        $this->load->model('Report_model');
        $this->load->model('Storage_model');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'common']);
    }

    public function index()
    {
        $data['title'] = 'Transaksi Stok';
        $data['transactions'] = $this->History_model->get_all_history();
        render_view('transaksi_stok/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Buat Transaksi Stok';
        $data['electric_items'] = $this->db->select('electric_id, nama')->order_by('nama','ASC')->get('as_electric')->result_array();
        render_view('transaksi_stok/form', $data);
    }

    public function store()
    {
        $this->form_validation->set_rules('electric_id', 'Barang', 'required');
        $this->form_validation->set_rules('type', 'Tipe', 'required');
        $this->form_validation->set_rules('qty', 'Jumlah', 'required|integer|greater_than[0]');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $payload = [
            'electric_id' => $this->input->post('electric_id', true),
            'type' => $this->input->post('type', true),
            'qty' => (int)$this->input->post('qty', true),
            'user_nik' => $this->session->userdata('user_data')['nik'] ?? null,
            'keterangan' => $this->input->post('keterangan', true) ?: null,
            'date' => date('Y-m-d H:i:s'),
        ];

        $result = $this->History_model->addTransaction($payload);
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Transaksi berhasil dicatat');
        } else {
            $this->session->set_flashdata('error', 'Gagal mencatat transaksi: ' . ($result['message'] ?? ''));
        }
        redirect('transaksi_stok');
    }

    public function reports()
    {
        $data['title'] = 'Laporan Transaksi Stok';
        $data['transactions'] = $this->Report_model->get_all_transactions(200);
        render_view('transaksi_stok/reports', $data);
    }

    public function low_stock_detail()
    {
        $data['title'] = 'Detail Stok Rendah';
        $data['low_stock'] = $this->Storage_model->get_low_stock();
        render_view('transaksi_stok/low_stock_detail', $data);
    }
}

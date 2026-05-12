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
        $this->load->model('Electric_model');
        if ($this->input->method() === 'post') {
            $electric_id = $this->input->post('electric_id', true);
            $qty = (int) $this->input->post('qty', true);
            $keterangan = $this->input->post('keterangan', true);
            $user_nik = $this->session->userdata('user_data')['nik'] ?? null;

            if (!$electric_id || $qty <= 0) {
                set_message(['danger', 'Data tidak valid']);
                redirect('history/out');
                return;
            }

            $res = $this->History_model->insert_history([
                'electric_id' => $electric_id,
                'type' => 'Keluar',
                'qty' => $qty,
                'user_nik' => $user_nik,
                'keterangan' => $keterangan,
            ]);

            if ($res['success']) set_message(['success', 'Transaksi Keluar disimpan (ID: ' . $res['id'] . ')']);
            else set_message(['danger', 'Gagal menyimpan transaksi: ' . $res['message']]);

            redirect('history');
            return;
        }

        $electrics = $this->Electric_model->getAllElectrics();
        $data = ['title' => 'Catat Barang Keluar', 'electrics' => $electrics];
        render_view('history/out', $data);
    }
}

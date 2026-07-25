<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wo extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin() && !is_teknisi()) {
            redirect('auth');
        }
        $this->load->model('Wo_model');
    }

    public function index()
    {
        $data['title'] = 'Work Order (WO) / Permintaan Keluar';
        $data['work_orders'] = $this->Wo_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('wo/index', $data);
        $this->load->view('templates/footer');
    }

    public function add()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('project_name', 'Nama Proyek/Divisi', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
        } else {
            $data = [
                'wo_number' => $this->Wo_model->generate_wo_number(),
                'project_name' => $this->input->post('project_name'),
                'request_date' => $this->input->post('request_date') ?: date('Y-m-d')
            ];
            if($this->Wo_model->insert($data)){
                $this->session->set_flashdata('message', 'Work Order berhasil ditambahkan!');
            } else {
                $this->session->set_flashdata('error', 'Gagal, nomor WO mungkin sudah ada.');
            }
        }
        redirect('wo');
    }

    public function delete($id)
    {
        if ($this->Wo_model->delete($id)) {
            $this->session->set_flashdata('message', 'Work Order berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus Work Order.');
        }
        redirect('wo');
    }

    public function detail($id)
    {
        $data['title'] = 'Detail Work Order';
        $data['wo'] = $this->Wo_model->get_by_id($id);
        if (!$data['wo']) {
            show_404();
        }
        
        $data['details'] = $this->Wo_model->get_details($id);

        $this->load->view('templates/header', $data);
        $this->load->view('wo/detail', $data);
        $this->load->view('templates/footer');
    }

    public function approve_item($detail_id)
    {
        // Hanya Staf Gudang yang boleh approve
        if (!is_admin() && $this->session->userdata('role') !== 'Staf Gudang') {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('wo');
        }

        $detail = $this->db->get_where('as_wo_details', ['id' => $detail_id])->row_array();
        if (!$detail || $detail['status'] !== 'Pending') {
            $this->session->set_flashdata('error', 'Item tidak ditemukan atau sudah diproses.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'wo');
        }

        // Dapatkan nama Proyek dari WO
        $wo = $this->db->get_where('as_work_orders', ['id' => $detail['wo_id']])->row_array();
        $projectName = $wo ? $wo['project_name'] : 'Unknown Project';
        
        $originalKeterangan = !empty($detail['keterangan']) ? ' - Catatan Teknisi: ' . $detail['keterangan'] : '';
        $stafName = $this->session->userdata('user_data')['name'] ?? 'Staf Gudang';

        // Potong stok dengan FIFO
        $this->load->model('History_model');
        $res = $this->History_model->addTransaction([
            'electric_id' => $detail['electric_id'],
            'type' => 'Keluar',
            'qty' => $detail['qty'],
            'user_nik' => $detail['user_nik'], // Gunakan NIK teknisi sebagai pelaku Keluar
            'keterangan' => 'Proyek: ' . $projectName . $originalKeterangan . ' (Di-ACC oleh ' . $stafName . ')',
            'wo_id' => $detail['wo_id']
        ]);

        if ($res['success']) {
            $this->db->where('id', $detail_id)->update('as_wo_details', [
                'status' => 'Approved',
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $this->session->userdata('user_data')['nik'] ?? 'Sistem'
            ]);
            $this->session->set_flashdata('message', 'Item berhasil di-approve dan stok telah terpotong (FIFO).');
        } else {
            $this->session->set_flashdata('error', 'Gagal memotong stok: ' . $res['message']);
        }
        redirect('wo/detail/' . $detail['wo_id']);
    }

    public function reject_item($detail_id)
    {
        if (!is_admin() && $this->session->userdata('role') !== 'Staf Gudang') {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('wo');
        }

        $detail = $this->db->get_where('as_wo_details', ['id' => $detail_id])->row_array();
        if ($detail && $detail['status'] === 'Pending') {
            $this->db->where('id', $detail_id)->update('as_wo_details', [
                'status' => 'Rejected',
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $this->session->userdata('user_data')['nik'] ?? 'Sistem'
            ]);
            $this->session->set_flashdata('message', 'Permintaan item ditolak.');
            redirect('wo/detail/' . $detail['wo_id']);
        }
        redirect($_SERVER['HTTP_REFERER'] ?? 'wo');
    }
}


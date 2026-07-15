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
}

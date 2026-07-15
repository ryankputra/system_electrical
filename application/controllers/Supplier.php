<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Cek login
        if (!$this->session->userdata('role')) {
            redirect('auth');
        }
        $this->load->model('Supplier_model');
    }

    public function index()
    {
        $data['title'] = 'Data Master Supplier';
        $data['suppliers'] = $this->Supplier_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('supplier/index', $data);
        $this->load->view('templates/footer');
    }

    public function add()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('supplier_name', 'Nama Supplier', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
        } else {
            $data = [
                'supplier_name' => $this->input->post('supplier_name'),
                'contact_person' => $this->input->post('contact_person'),
                'phone' => $this->input->post('phone'),
                'address' => $this->input->post('address')
            ];
            $this->Supplier_model->insert($data);
            $this->session->set_flashdata('message', 'Data supplier berhasil ditambahkan!');
        }
        redirect('supplier');
    }

    public function edit($id)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('supplier_name', 'Nama Supplier', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
        } else {
            $data = [
                'supplier_name' => $this->input->post('supplier_name'),
                'contact_person' => $this->input->post('contact_person'),
                'phone' => $this->input->post('phone'),
                'address' => $this->input->post('address')
            ];
            $this->Supplier_model->update($id, $data);
            $this->session->set_flashdata('message', 'Data supplier berhasil diupdate!');
        }
        redirect('supplier');
    }

    public function delete($id)
    {
        $this->Supplier_model->delete($id);
        $this->session->set_flashdata('message', 'Data supplier berhasil dihapus!');
        redirect('supplier');
    }
}

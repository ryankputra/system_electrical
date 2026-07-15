<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Po extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('role') || $this->session->userdata('role') !== 'Staf Gudang') {
            redirect('auth');
        }
        $this->load->model('Po_model');
        $this->load->model('Supplier_model');
        $this->load->model('Electric_model');

        // Reset session if controller changed
        if ($this->session->userdata('controller') !== 'po') {
            $this->session->set_userdata('controller', 'po');
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    public function index()
    {
        handle_session_state('po');
        $search = $this->session->userdata('keyword');

        $data['title'] = 'Purchase Order (PO)';
        $data['purchase_orders'] = $this->Po_model->get_all($search);
        $data['searchKeyword'] = $search;

        $this->load->view('templates/header', $data);
        $this->load->view('po/index', $data);
        $this->load->view('templates/footer');
    }

    public function create()
    {
        $data['title'] = 'Buat Purchase Order';
        $data['suppliers'] = $this->Supplier_model->get_all();
        $data['electrics'] = $this->Electric_model->getAllElectrics();

        $this->load->view('templates/header', $data);
        $this->load->view('po/create', $data);
        $this->load->view('templates/footer');
    }

    public function store()
    {
        $supplier_id = $this->input->post('supplier_id');
        $order_date = $this->input->post('order_date');
        
        $electric_ids = $this->input->post('electric_id[]');
        $qty_ordereds = $this->input->post('qty_ordered[]');
        $prices = $this->input->post('price[]');

        if (empty($electric_ids)) {
            $this->session->set_flashdata('error', 'Minimal pilih 1 barang untuk dipesan!');
            redirect('po/create');
            return;
        }

        $po_data = [
            'po_number' => $this->Po_model->generate_po_number(),
            'supplier_id' => $supplier_id,
            'order_date' => $order_date,
            'status' => 'Pending'
        ];

        $details = [];
        for ($i = 0; $i < count($electric_ids); $i++) {
            $details[] = [
                'electric_id' => $electric_ids[$i],
                'qty_ordered' => $qty_ordereds[$i],
                'price' => $prices[$i]
            ];
        }

        if ($this->Po_model->insert($po_data, $details)) {
            $this->session->set_flashdata('message', 'Purchase Order berhasil dibuat!');
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat PO. Pastikan nomor PO belum digunakan.');
        }
        redirect('po');
    }

    public function detail($id)
    {
        $data['title'] = 'Detail Purchase Order';
        $data['po'] = $this->Po_model->get_by_id($id);
        if (!$data['po']) show_404();
        
        $data['details'] = $this->Po_model->get_details($id);

        $this->load->view('templates/header', $data);
        $this->load->view('po/detail', $data);
        $this->load->view('templates/footer');
    }

    public function receive($id)
    {
        $po = $this->Po_model->get_by_id($id);
        if (!$po || $po['status'] != 'Pending') {
            $this->session->set_flashdata('error', 'PO tidak valid atau sudah diproses.');
            redirect('po');
            return;
        }

        $details = $this->Po_model->get_details($id);
        $this->load->model('History_model');
        
        $success = true;
        foreach ($details as $d) {
            $data_trans = [
                'electric_id' => $d['electric_id'],
                'type'        => 'Masuk',
                'qty'         => $d['qty_ordered'],
                'user_nik'    => $this->session->userdata('user_data')['nik'] ?? null,
                'keterangan'  => 'Penerimaan otomatis dari PO ' . $po['po_number'],
                'po_id'       => $po['id'],
                'po_number'   => $po['po_number'],
                'distributor' => $po['supplier_name'] ?? '',
                'harga_satuan'=> $d['price']
            ];
            $res = $this->History_model->addTransaction($data_trans);
            if (!$res['success']) {
                $success = false;
            }
        }

        if ($success) {
            $this->Po_model->update_status($id, 'Completed');
            $this->session->set_flashdata('message', 'Semua barang dalam PO berhasil diterima dan masuk ke gudang (Antrean)!');
        } else {
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menerima sebagian barang.');
        }
        redirect('po/detail/'.$id);
    }

    public function delete($id)
    {
        $po = $this->Po_model->get_by_id($id);
        if (!$po) show_404();
        
        if ($po['status'] === 'Completed') {
            $this->session->set_flashdata('error', 'PO yang sudah berstatus Selesai tidak dapat dihapus.');
            redirect('po');
            return;
        }

        if ($this->Po_model->delete($id)) {
            $this->session->set_flashdata('message', 'Purchase Order berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus Purchase Order.');
        }
        redirect('po');
    }
}


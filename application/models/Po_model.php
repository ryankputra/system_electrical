<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Po_model extends CI_Model
{
    private string $table = 'as_purchase_orders';
    private string $details_table = 'as_po_details';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Id_generator_model');
    }

    public function get_all(?string $search = null): array
    {
        $this->db->select('po.*, s.supplier_name');
        $this->db->from($this->table . ' po');
        $this->db->join('as_suppliers s', 'po.supplier_id = s.id', 'left');
        
        if ($search !== null && trim($search) !== '') {
            $search = trim($search);
            $this->db->group_start();
            $this->db->like('po.po_number', $search);
            $this->db->or_like('s.supplier_name', $search);
            $this->db->or_like('po.status', $search);
            $this->db->group_end();
        }

        $this->db->order_by('po.order_date', 'DESC');
        $this->db->order_by('po.id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_by_id(int $id): ?array
    {
        $this->db->select('po.*, s.supplier_name');
        $this->db->from($this->table . ' po');
        $this->db->join('as_suppliers s', 'po.supplier_id = s.id', 'left');
        $this->db->where('po.id', $id);
        return $this->db->get()->row_array();
    }

    public function get_details(int $po_id): array
    {
        $this->db->select('pod.*, e.nama, e.type as spesifikasi, e.brand, e.voltage, e.voltage_unit, e.ampere, e.daya, e.daya_unit');
        $this->db->from($this->details_table . ' pod');
        $this->db->join('as_electric e', 'pod.electric_id = e.electric_id', 'left');
        $this->db->where('pod.po_id', $po_id);
        return $this->db->get()->result_array();
    }

    public function generate_po_number()
    {
        $date_prefix = date('Ymd');
        $prefix = "PO-{$date_prefix}-";
        
        $this->db->select('po_number');
        $this->db->like('po_number', $prefix, 'after');
        $this->db->order_by('po_number', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        
        if ($query->num_rows() > 0) {
            $last_po = $query->row()->po_number;
            $last_seq = (int)substr($last_po, -3);
            $new_seq = str_pad($last_seq + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $new_seq = '001';
        }
        
        return $prefix . $new_seq;
    }

    public function insert(array $po_data, array $details): bool
    {
        $this->db->trans_start();
        
        $po_id = $this->Id_generator_model->generate_manual_id($this->table, 'id');
        $po_data['id'] = $po_id;
        $this->db->insert($this->table, $po_data);

        $detail_id = $this->Id_generator_model->generate_manual_id($this->details_table, 'id');
        foreach ($details as $detail) {
            $detail['id'] = $detail_id++;
            $detail['po_id'] = $po_id;
            $this->db->insert($this->details_table, $detail);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function update_status(int $id, string $status): bool
    {
        return $this->db->where('id', $id)->update($this->table, ['status' => $status]);
    }

    public function delete(int $id): bool
    {
        $this->db->trans_start();
        $this->db->where('po_id', $id)->delete($this->details_table);
        $this->db->where('id', $id)->delete($this->table);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}

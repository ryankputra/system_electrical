<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wo_model extends CI_Model
{
    private string $table = 'as_work_orders';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Id_generator_model');
    }

    public function get_all(): array
    {
        return $this->db->order_by('request_date', 'DESC')->get($this->table)->result_array();
    }

    public function get_by_id(int $id): ?array
    {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    public function generate_wo_number()
    {
        $date_prefix = date('Ymd');
        $prefix = "WO-{$date_prefix}-";
        
        $this->db->select('wo_number');
        $this->db->like('wo_number', $prefix, 'after');
        $this->db->order_by('wo_number', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        
        if ($query->num_rows() > 0) {
            $last_wo = $query->row()->wo_number;
            $last_seq = (int)substr($last_wo, -3);
            $new_seq = str_pad($last_seq + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $new_seq = '001';
        }
        
        return $prefix . $new_seq;
    }

    public function insert(array $data): bool
    {
        $newId = $this->Id_generator_model->generate_manual_id($this->table, 'id');
        $data['id'] = $newId;
        return $this->db->insert($this->table, $data);
    }

    public function delete($id): bool
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function get_details($wo_id)
    {
        $this->db->select('h.*, e.nama as electric_name, e.brand, e.type as electric_type');
        $this->db->from('as_history h');
        $this->db->join('as_electric e', 'e.electric_id = h.electric_id', 'left');
        $this->db->where('h.wo_id', $wo_id);
        return $this->db->get()->result_array();
    }
}

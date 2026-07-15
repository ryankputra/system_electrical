<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_model extends CI_Model
{
    private string $table = 'as_suppliers';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Id_generator_model');
    }

    public function get_all(): array
    {
        return $this->db->order_by('supplier_name', 'ASC')->get($this->table)->result_array();
    }

    public function get_by_id(int $id): ?array
    {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    public function insert(array $data): bool
    {
        $newId = $this->Id_generator_model->generate_manual_id($this->table, 'id');
        $data['id'] = $newId;
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->where('id', $id)->delete($this->table);
    }
}

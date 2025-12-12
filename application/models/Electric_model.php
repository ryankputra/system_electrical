<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Electric_model extends CI_Model
{
    private string $table = 'as_electric';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Electric_type_model');
    }
    
    public function getElectric(int $limit, int $start, ?string $search, ?array $filter, ?string $sort): array
    {
        $subquery = "(
            SELECT 
                type_id, 
                SUM(amount) as total_stock 
            FROM as_storage 
            GROUP BY type_id
        )";

        $this->db->select('e.*, s.total_stock');
        $this->db->from($this->table . ' e');

        $this->db->join(
            $subquery . ' s',
            "(e.electric_id LIKE CONCAT(s.type_id, '%') OR s.type_id LIKE CONCAT(e.electric_id, '%'))",
            'left'
        );

        if ($search) {
            $this->db->group_start()
                ->like('e.nama', $search)
                ->or_like('e.type', $search)
                ->or_like('e.brand', $search)
                ->group_end();
        }

        if ($filter) {
            foreach($filter as $key => $val) {
                if(!empty($val)) $this->db->where_in('e.' . $key, $val);
            }
        }

        if ($sort) {
            list($field, $direction) = explode('-', $sort);
            $this->db->order_by('e.' . $field, $direction);
        } else {
            $this->db->order_by('e.updated_at', 'DESC');
        }

        $this->db->limit($limit, $start);

        return $this->db->get()->result_array();
    }

    public function getCategories(): array 
    { 
        return $this->Electric_type_model->getAllTypes(); 
    }

    public function addElectric(array $data): bool 
    { 
        $data['electric_id'] = $this->generateElectricId( $data['nama'], $data['type'], $data['voltage'] ?? null, $data['ampere'] ?? null ); 
        if ($this->isElectricIdExists($data['electric_id'])) { 
            return false; 
        } 
        $data['created_at'] = mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')); 
        $data['updated_at'] = mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')); 
        return $this->db->insert($this->table, $data); 
    }

    public function editElectric(string $electricId, array $data): bool 
    { 
        $data['updated_at'] = mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')); 
        return $this->db->where('electric_id', $electricId)->update($this->table, $data); 
    }

    public function deleteElectric(string $electricId): bool 
    { 
        return $this->db->where('electric_id', $electricId)->delete($this->table); 
    }

    public function getById(string $electricId): ?array 
    { 
        return $this->db->get_where($this->table, ['electric_id' => $electricId])->row_array(); 
    }

    public function isElectricIdExists(string $electricId): bool 
    { 
        $this->db->where('electric_id', $electricId); 
        return $this->db->count_all_results($this->table) > 0; 
    }

    public function generateElectricId(string $nama, string $type, $voltage, $ampere): string 
    { 
        $safeNama = preg_replace('/[^a-z0-9\-]/i', '-', strtolower(trim($nama))); 
        $safeType = preg_replace('/[^a-z0-9\-]/i', '-', strtolower(trim($type))); 
        $cleanPart = function($value) { 
            if ($value === null || trim($value) === '') { 
                return '0'; 
            } 
            $cleanedValue = str_replace('.', '-', trim($value)); 
            return preg_replace('/[^a-z0-9\-]/i', '', strtolower($cleanedValue)); 
        }; 
        $vPart = $cleanPart($voltage); 
        $aPart = $cleanPart($ampere); 
        return 'elc-' . $safeNama . '-' . $safeType . '-' . $vPart . '-' . $aPart; 
    }

    public function countElectric(?string $search, ?array $filter): int 
    { 
        if ($search) { 
            $this->db->group_start()->like('nama', $search)->or_like('type', $search)->or_like('brand', $search)->group_end(); 
        } 
        if ($filter) { 
            foreach($filter as $key => $val) { 
                if(!empty($val)) $this->db->where_in($key, $val); 
            } 
        } 
        return $this->db->count_all_results($this->table); 
    }

    public function getElectricFilter(string $column, ?string $search = null, ?array $filter = null): array 
    { 
        $this->db->select($column)->distinct()->where("$column IS NOT NULL")->where("$column !=", '')->order_by($column, 'ASC'); 
        if ($search) { 
            $this->db->group_start()->like('nama', $search)->or_like('type', $search)->or_like('brand', $search)->group_end(); 
        } 
        if ($filter) { 
            foreach($filter as $key => $val) { 
                if(!empty($val)) $this->db->where_in($key, $val); 
            } 
        } 
        $results = $this->db->get($this->table)->result_array(); 
        return array_column($results, $column); 
    }

    public function getAllElectrics(): array 
    { 
        return $this->db->order_by('nama', 'ASC')->get($this->table)->result_array(); 
    }

    public function insertBatch(array $data): bool 
    { 
        if (empty($data)) return true; 
        return $this->db->insert_batch($this->table, $data); 
    }

    public function getMultipleByIds(array $electricIds): array 
    { 
        if (empty($electricIds)) { 
            return []; 
        } 
        $this->db->where_in('electric_id', $electricIds); 
        return $this->db->get($this->table)->result_array(); 
    }
}
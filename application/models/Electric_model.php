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

        // Determine how location is stored and ensure we always provide a `location` column
        $has_location_col = $this->db->field_exists('location', $this->table);
        $has_location_id_col = $this->db->field_exists('location_id', $this->table);
        $has_location_name_col = $this->db->field_exists('location_name', $this->table);

        $select = ['e.*', 's.total_stock'];

        if ($has_location_id_col) {
            // If electric table stores a location_id, join to master table to get the name
            $this->db->join('as_location l', 'e.location_id = l.id', 'left');
            $select[] = 'l.location_name as location';
        } elseif ($has_location_col) {
            // If electric table already stores location name
            $select[] = 'e.location as location';
        } elseif ($has_location_name_col) {
            $select[] = 'e.location_name as location';
        } else {
            // Ensure the result always contains a `location` key
            $select[] = "NULL as location";
        }

        $this->db->select($select);
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
            // Use updated_at if available, otherwise fall back to created_at to avoid SQL errors
            if ($this->db->field_exists('updated_at', $this->table)) {
                $this->db->order_by('e.updated_at', 'DESC');
            } else {
                $this->db->order_by('e.created_at', 'DESC');
            }
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
    // Mengambil 3 huruf pertama nama dan tipe untuk ID yang lebih pendek
    $prefix = 'ELC';
    $safeNama = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $nama), 0, 3));
    
    // Cari nomor urut terakhir di database untuk nama tersebut
    $this->db->like('electric_id', $prefix . '-' . $safeNama);
    $this->db->order_by('electric_id', 'DESC');
    $lastId = $this->db->get($this->table)->row_array();

    if ($lastId) {
        $lastNumber = intval(substr($lastId['electric_id'], -3)) + 1;
    } else {
        $lastNumber = 1;
    }

    $formattedNumber = str_pad($lastNumber, 3, "0", STR_PAD_LEFT);
    
    // Hasilnya akan seperti: ELC-LAM-001
    return $prefix . '-' . $safeNama . '-' . $formattedNumber; 
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
        // If the column does not exist in the electric table, provide safe fallbacks
        if (!$this->db->field_exists($column, $this->table)) {
            if ($column === 'location') {
                // Get master location names
                $rows = $this->db->select('location_name')->order_by('location_name', 'ASC')->get('as_location')->result_array();
                return array_column($rows, 'location_name');
            }
            return [];
        }

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
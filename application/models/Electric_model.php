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
        // Prefer authoritative totals from as_history.qty_sisa when available.
        // Fallback to as_storage aggregation, then to e.stock column, then 0.
        $has_history_table = $this->db->table_exists('as_history') && $this->db->field_exists('qty_sisa', 'as_history');
        $has_storage_table = $this->db->table_exists('as_storage');

        // Build select parts as an array to avoid [] operator on strings
        $selectParts = ['e.*'];
        if ($has_history_table) {
            $historySub = "(
                SELECT
                    electric_id,
                    SUM(qty_sisa) as history_stock
                FROM as_history
                GROUP BY electric_id
            )";
            $selectParts[] = "COALESCE(hs.history_stock, 0) as total_stock";
        } elseif ($has_storage_table) {
            $subquery = "(
                SELECT
                    type_id,
                    SUM(amount) as total_stock
                FROM as_storage
                GROUP BY type_id
            )";
            $selectParts[] = "COALESCE(s.total_stock, 0) as total_stock";
        } elseif ($this->db->field_exists('stock', $this->table)) {
            $selectParts[] = "COALESCE(e.stock, 0) as total_stock";
        } else {
            $selectParts[] = "0 as total_stock";
        }

        // Determine how location is stored and ensure we always provide a `location` column
        $has_location_col = $this->db->field_exists('location', $this->table);
        $has_location_id_col = $this->db->field_exists('location_id', $this->table);
        $has_location_name_col = $this->db->field_exists('location_name', $this->table);

        if ($has_location_id_col) {
            // If electric table stores a location_id, join to master table to get the name
            $this->db->join('as_location l', 'e.location_id = l.id', 'left');
            $selectParts[] = 'l.location_name as location';
            $selectParts[] = 'l.id as location_id';
        } elseif ($has_location_col) {
            // If electric table already stores location name
            $selectParts[] = 'e.location as location';
        } elseif ($has_location_name_col) {
            $selectParts[] = 'e.location_name as location';
        } else {
            // Ensure the result always contains a `location` key
            $selectParts[] = "NULL as location";
        }

        $this->db->select(implode(', ', $selectParts), false);
        $this->db->from($this->table . ' e');

        // Join to history summary when available
        if (!empty($has_history_table) && isset($historySub)) {
            $this->db->join($historySub . ' hs', 'hs.electric_id = e.electric_id', 'left', false);
        } elseif (!empty($has_storage_table) && isset($subquery)) {
            $this->db->join(
                $subquery . ' s',
                "(e.electric_id LIKE CONCAT(s.type_id, '%') OR s.type_id LIKE CONCAT(e.electric_id, '%'))",
                'left'
            );
        }

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
        // Normalize brand field; ensure it's not empty so UI shows a proper brand name
        if (isset($data['brand'])) {
            $data['brand'] = trim($data['brand']);
            if ($data['brand'] === '') $data['brand'] = 'Unknown';
        } else {
            $data['brand'] = 'Unknown';
        }
        $data['created_at'] = mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')); 
        $data['updated_at'] = mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta'));

        // Manual numeric PK generation (MAX + 1) — do not rely on AUTO_INCREMENT
        if ($this->db->field_exists('id', $this->table)) {
            $row = $this->db->select_max('id')->get($this->table)->row_array();
            $nextId = isset($row['id']) && $row['id'] !== null ? ((int)$row['id'] + 1) : 1;
            $data['id'] = $nextId;
        }

        $ok = $this->db->insert($this->table, $data);

        // Immediately synchronize `stock` from authoritative history source when available
        if ($ok && $this->db->table_exists('as_history') && $this->db->field_exists('qty_sisa', 'as_history') && $this->db->field_exists('stock', $this->table)) {
            $row = $this->db->select('SUM(qty_sisa) as total')->where('electric_id', $data['electric_id'])->get('as_history')->row_array();
            $newStock = isset($row['total']) ? (int)$row['total'] : 0;
            $this->db->where('electric_id', $data['electric_id'])->update($this->table, ['stock' => $newStock]);
        }

        return (bool)$ok; 
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
        // Return all electrics and include authoritative total_stock when possible
        $has_history_table = $this->db->table_exists('as_history') && $this->db->field_exists('qty_sisa', 'as_history');
        $has_storage_table = $this->db->table_exists('as_storage');

        if ($has_history_table) {
            // Only sum qty_sisa from 'Masuk' transactions, as 'Keluar' and 'Audit' do not hold active batch stock
            $sql = "SELECT e.*, COALESCE(hs.history_stock, 0) AS total_stock FROM {$this->table} e LEFT JOIN (SELECT electric_id, SUM(qty_sisa) AS history_stock FROM as_history WHERE type = 'Masuk' GROUP BY electric_id) hs ON hs.electric_id = e.electric_id ORDER BY e.nama ASC";
            return $this->db->query($sql)->result_array();
        }

        if ($has_storage_table) {
            $subquery = "(SELECT type_id, SUM(amount) as total_stock FROM as_storage GROUP BY type_id) s";
            $this->db->select('e.*, COALESCE(s.total_stock,0) as total_stock', false);
            $this->db->from($this->table . ' e');
            $this->db->join($subquery, "(e.electric_id LIKE CONCAT(s.type_id, '%') OR s.type_id LIKE CONCAT(e.electric_id, '%'))", 'left');
            $this->db->order_by('e.nama', 'ASC');
            return $this->db->get()->result_array();
        }

        // Fallback: return table with total_stock from e.stock column when available
        if ($this->db->field_exists('stock', $this->table)) {
            $this->db->select('e.*, COALESCE(e.stock,0) as total_stock', false)->from($this->table . ' e')->order_by('e.nama', 'ASC');
            return $this->db->get()->result_array();
        }

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

    /**
     * Get all electrics for a specific location with total_stock calculated
     * @param mixed $lokasi_id The location ID to filter by
     * @return array Array of electrics with total_amount field
     */
    public function getByLocation($lokasi_id): array
    {
        // Detect which location column exists
        $locationCol = null;
        if ($this->db->field_exists('location_id', $this->table)) $locationCol = 'location_id';
        elseif ($this->db->field_exists('location', $this->table)) $locationCol = 'location';
        elseif ($this->db->field_exists('id_lokasi', $this->table)) $locationCol = 'id_lokasi';

        if ($locationCol === null) {
            return [];
        }

        // Check for history table with qty_sisa
        $has_history_table = $this->db->table_exists('as_history') && $this->db->field_exists('qty_sisa', 'as_history');

        if ($has_history_table) {
            $sql = "SELECT e.*, COALESCE(hs.history_stock, 0) AS total_amount 
                    FROM {$this->table} e 
                    LEFT JOIN (SELECT electric_id, SUM(qty_sisa) AS history_stock FROM as_history WHERE type = 'Masuk' GROUP BY electric_id) hs 
                    ON hs.electric_id = e.electric_id 
                    WHERE e.{$locationCol} = ?
                    ORDER BY e.nama ASC";
            return $this->db->query($sql, [$lokasi_id])->result_array();
        }

        // Fallback to as_storage if exists
        if ($this->db->table_exists('as_storage')) {
            $subquery = "(SELECT type_id, SUM(amount) as total_amount FROM as_storage GROUP BY type_id) s";
            $this->db->select('e.*, COALESCE(s.total_amount,0) as total_amount', false);
            $this->db->from($this->table . ' e');
            $this->db->join($subquery, "(e.electric_id LIKE CONCAT(s.type_id, '%') OR s.type_id LIKE CONCAT(e.electric_id, '%'))", 'left');
            $this->db->where("e.{$locationCol}", $lokasi_id);
            $this->db->order_by('e.nama', 'ASC');
            return $this->db->get()->result_array();
        }

        // Fallback to e.stock column
        if ($this->db->field_exists('stock', $this->table)) {
            $this->db->select('e.*, COALESCE(e.stock,0) as total_amount', false)
                     ->from($this->table . ' e')
                     ->where("e.{$locationCol}", $lokasi_id)
                     ->order_by('e.nama', 'ASC');
            return $this->db->get()->result_array();
        }

        // Final fallback: just return items with location_id filter
        return $this->db->where($locationCol, $lokasi_id)->order_by('nama', 'ASC')->get($this->table)->result_array();
    }
}
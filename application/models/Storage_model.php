<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Storage_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // Load history model for automatic transaction logging (if available)
        if (file_exists(APPPATH . 'models/History_model.php')) {
            $this->load->model('History_model');
        }
    }

    public function get_storage_by_location($location_id, $keyword = null)
    {
        $location_id = trim($location_id);
    
        $this->db->select('s.*, u.name as editor_name');
        $this->db->from('as_storage s');
        $this->db->join('as_user u', 's.editor = u.nik', 'left');
        $this->db->where('s.location_id', $location_id);
        
        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('s.type_id', $keyword);
            $this->db->or_like('s.category', $keyword);
            if (is_numeric($keyword)) {
                $this->db->or_where('s.amount', $keyword);
            }
            $this->db->group_end();
        }
        
        $this->db->order_by('s.category, s.type_id');
        $query = $this->db->get();
        return $this->decode_storage_rows($query->result_array());
    }

    public function get_storage_item($location_id, $type_id)
    {
        $this->db->select('s.*, u.name as editor_name');
        $this->db->from('as_storage s');
        $this->db->join('as_user u', 's.editor = u.nik', 'left');
        $this->db->where('s.location_id', trim($location_id));
        $this->db->where('TRIM(s.type_id)', trim($type_id));
        $query = $this->db->get();
        return $query->row_array();
    }

    public function storage_exists($location_id, $type_id)
    {
        $this->db->where('location_id', trim($location_id));
        $this->db->where('TRIM(type_id)', trim($type_id));
        return $this->db->get('as_storage')->num_rows() > 0;
    }
    
    public function add_storage($data)
    {
        $storage_data_db = [
            'location_id' => isset($data['location_id']) ? trim($data['location_id']) : null,
            'category' => isset($data['category']) ? trim($data['category']) : null,
            'type_id' => isset($data['type_id']) ? trim($data['type_id']) : null,
            'amount' => $data['amount'],
            'storage_data' => isset($data['storage_data']) ? (is_array($data['storage_data']) ? json_encode($data['storage_data']) : $data['storage_data']) : null,
            'editor' => $data['editor']
        ];
        return $this->db->insert('as_storage', $storage_data_db);
    }

    public function update_storage($location_id, $type_id, $new_amount, $editor_nik)
    {
        $this->db->where('location_id', trim($location_id));
        $this->db->where('TRIM(type_id)', trim($type_id));
        return $this->db->update('as_storage', [
            'amount' => $new_amount,
            'editor' => $editor_nik,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function store_items($location_id, $category, $type_id, $quantity, $editor_nik)
    {
        if ($this->storage_exists($location_id, $type_id)) {
            $current_item = $this->get_storage_item($location_id, $type_id);
            $new_amount = (int)($current_item['amount'] ?? 0) + (int)$quantity;
            $result = $this->update_storage($location_id, $type_id, $new_amount, $editor_nik);
            // Log history: IN
            if (isset($this->History_model) && method_exists($this->History_model, 'insert_history')) {
                $this->History_model->insert_history([
                    'electric_id' => $type_id,
                    'type' => 'Masuk',
                    'qty' => $quantity,
                    'user_nik' => $editor_nik,
                ]);
            }
            return $result;
        } else {
            $insertResult = $this->db->insert('as_storage', [
                'location_id' => $location_id,
                'category' => $category,
                'type_id' => $type_id,
                'amount' => $quantity,
                'editor' => $editor_nik
            ]);
            if ($insertResult && isset($this->History_model) && method_exists($this->History_model, 'insert_history')) {
                $this->History_model->insert_history([
                    'electric_id' => $type_id,
                    'type' => 'Masuk',
                    'qty' => $quantity,
                    'user_nik' => $editor_nik,
                ]);
            }
            return $insertResult;
        }
    }

    public function take_items($location_id, $type_id, $quantity, $editor_nik)
    {
        $current_item = $this->get_storage_item($location_id, $type_id);
        if (!$current_item) {
            return ['success' => false, 'message' => 'Barang tidak ditemukan di lokasi ini.'];
        }
        if ($current_item['amount'] < $quantity) {
            return ['success' => false, 'message' => 'Stok tidak mencukupi. Tersedia: ' . $current_item['amount']];
        }
        $new_amount = $current_item['amount'] - $quantity;
        if ($this->update_storage($location_id, $type_id, $new_amount, $editor_nik)) {
            // Log history: OUT
            if (isset($this->History_model) && method_exists($this->History_model, 'insert_history')) {
                $this->History_model->insert_history([
                    'electric_id' => $type_id,
                    'type' => 'Keluar',
                    'qty' => $quantity,
                    'user_nik' => $editor_nik,
                ]);
            }
            return ['success' => true, 'message' => 'Barang berhasil diambil'];
        } else {
            return ['success' => false, 'message' => 'Gagal memperbarui database'];
        }
    }
    
    public function get_available_stock($type_id)
    {
        $this->db->select('s.location_id, s.amount');
        $this->db->like('s.type_id', trim($type_id), 'after');
        $this->db->where('s.amount >', 0);
        $query = $this->db->get('as_storage s');
        return $query->result_array();
    }

    public function get_total_stock($type_id)
    {
        $this->db->select_sum('amount');
        $this->db->like('type_id', trim($type_id), 'after');
        $query = $this->db->get('as_storage');
        $result = $query->row_array();
        return $result['amount'] ?? 0;
    }

    public function get_all_locations()
    {
        // Mengambil data dari tabel master lokasi
        return $this->db->order_by('location_name', 'ASC')->get('as_location')->result_array();
    }
    
    public function get_storage_overview()
    {
        $this->db->select('category, type_id, SUM(amount) as total_amount, COUNT(DISTINCT location_id) as location_count');
        $this->db->group_by(['category', 'type_id']);
        $this->db->having('SUM(amount) >', 0);
        $this->db->order_by('category, type_id');
        $query = $this->db->get('as_storage');
        return $this->decode_storage_rows($query->result_array());
    }

    public function search_storage($search_term, $location_id = null, $category = null)
    {
        $this->db->select('s.*, u.name as editor_name');
        $this->db->from('as_storage s');
        $this->db->join('as_user u', 's.editor = u.nik', 'left');

        if ($search_term) {
            $this->db->group_start();
            $this->db->like('s.type_id', $search_term);
            $this->db->or_like('s.category', $search_term);
            $this->db->or_like('s.location_id', $search_term);
            $this->db->group_end();
        }
        if ($location_id) $this->db->where('s.location_id', $location_id);
        if ($category) $this->db->where('s.category', $category);

        $this->db->order_by('s.location_id, s.category, s.type_id');
        return $this->decode_storage_rows($this->db->get()->result_array());
    }

    public function get_low_stock() 
    {
        $threshold = 10;
        $this->db->select('s.category, s.type_id, SUM(s.amount) AS total_amount');
        $this->db->from('as_storage s');
        $this->db->where('s.amount <=', $threshold);
        $this->db->group_by(['s.category', 's.type_id']);
        $this->db->order_by('s.category, s.type_id');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_all_storage() {
        $this->db->select('*');
        $this->db->from('as_storage');
        $query = $this->db->get();
        return $query->result_array();
    }

    private function decode_storage_rows(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $type_ids = array_unique(array_column($rows, 'type_id'));
        if(empty($type_ids)) return $rows;

        $this->load->model('Electric_model');
        $electric_details = $this->Electric_model->getMultipleByIds($type_ids);
        
        foreach ($rows as &$row) {
            if (!empty($row['storage_data']) && is_string($row['storage_data'])) {
                $row['storage_data'] = json_decode($row['storage_data'], true) ?? null;
            }

            if (isset($electric_details[$row['type_id']])) {
                $row['item_details'] = $electric_details[$row['type_id']];
            } else {
                $row['item_details'] = null;
            }
        }
        return $rows;
    }
}
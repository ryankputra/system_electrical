<?php
/**
 * Report Model
 *
 * Handles database operations related to reports, including generation and retrieval.
 *
 * @package ElectricalSystem
 * @subpackage Models
 * @category Report
 * @version 1.0.0
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Report_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Log a storage transaction
     */
    public function log_transaction($data)
    {
        $storing_id = $this->generate_storing_id($data['action'], $data['location_id']);

        $transaction_data = array(
            'storing_id'    => $storing_id,
            'location_id'   => $data['location_id'],
            'datetime'      => date('Y-m-d H:i:s'),
            'category'      => $data['category'],
            'type_id'       => $data['type_id'],
            'action'        => $data['action'],
            'note'          => $data['note'] ?? null,
            'nik'           => $data['nik'],
            'project_name'  => $data['project_name'] ?? null
        );

        return $this->db->insert('as_report', $transaction_data);
    }

    /**
     * Generate unique storing ID
     */
    private function generate_storing_id($action, $location_id)
    {
        $prefix = ($action == 'store') ? 'ST' : 'TK';
        $date = date('Ymd');
        $time = date('His');
        $clean_location = preg_replace('/[^A-Za-z0-9]/', '', $location_id);
        return $prefix . '-' . strtoupper($clean_location) . '-' . $date . '-' . $time;
    }

    /**
     * Log store transaction
     */
    public function log_store_transaction($location_id, $category, $type_id, $nik, $note = null, $project_name = null)
    {
        $data = array(
            'location_id'   => $location_id,
            'category'      => $category,
            'type_id'       => $type_id,
            'action'        => 'store',
            'nik'           => $nik,
            'note'          => $note,
            
            'project_name'  => $project_name
        );

        return $this->log_transaction($data);
    }

    /**
     * Log take transaction
     */
    public function log_take_transaction($location_id, $category, $type_id, $nik, $note = null)
    {
        $data = array(
            'location_id' => $location_id,
            'category' => $category,
            'type_id' => $type_id,
            'action' => 'take',
            'nik' => $nik,
            'note' => $note
        );

        return $this->log_transaction($data);
    }

    /**
     * Get all transactions
     */
    public function get_all_transactions($limit = null, $offset = 0)
    {
        $this->db->select('r.*, u.name as user_name');
        $this->db->from('as_report r');
        $this->db->join('as_user u', 'r.nik = u.nik', 'left');
        $this->db->order_by('r.datetime', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get transactions by date range
     */
    public function get_transactions_by_date($start_date, $end_date, $limit = null, $offset = 0)
    {
        $this->db->select('r.*, u.name as user_name');
        $this->db->from('as_report r');
        $this->db->join('as_user u', 'r.nik = u.nik', 'left');
        $this->db->where('DATE(r.datetime) >=', $start_date);
        $this->db->where('DATE(r.datetime) <=', $end_date);
        $this->db->order_by('r.datetime', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get transactions by user
     */
    public function get_transactions_by_user($nik, $limit = null, $offset = 0)
    {
        $this->db->select('r.*, u.name as user_name');
        $this->db->from('as_report r');
        $this->db->join('as_user u', 'r.nik = u.nik', 'left');
        $this->db->where('r.nik', $nik);
        $this->db->order_by('r.datetime', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get transactions by location
     */
    public function get_transactions_by_location($location_id, $limit = null, $offset = 0)
    {
        $this->db->select('r.*, u.name as user_name');
        $this->db->from('as_report r');
        $this->db->join('as_user u', 'r.nik = u.nik', 'left');
        $this->db->where('r.location_id', $location_id);
        $this->db->order_by('r.datetime', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get transactions by action (store/take)
     */
    public function get_transactions_by_action($action, $limit = null, $offset = 0)
    {
        $this->db->select('r.*, u.name as user_name');
        $this->db->from('as_report r');
        $this->db->join('as_user u', 'r.nik = u.nik', 'left');
        $this->db->where('r.action', $action);
        $this->db->order_by('r.datetime', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get transactions for specific item
     */
    public function get_item_transactions($category, $type_id, $limit = null, $offset = 0)
    {
        $this->db->select('r.*, u.name as user_name');
        $this->db->from('as_report r');
        $this->db->join('as_user u', 'r.nik = u.nik', 'left');
        $this->db->where('r.category', $category);
        $this->db->where('r.type_id', $type_id);
        $this->db->order_by('r.datetime', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get transaction statistics
     */
    public function get_transaction_stats($start_date = null, $end_date = null)
    {
        $this->db->select('
            COUNT(*) as total_transactions,
            SUM(CASE WHEN action = "store" THEN 1 ELSE 0 END) as store_transactions,
            SUM(CASE WHEN action = "take" THEN 1 ELSE 0 END) as take_transactions,
            COUNT(DISTINCT location_id) as locations_involved,
            COUNT(DISTINCT nik) as users_involved
        ');
        $this->db->from('as_report');

        if ($start_date && $end_date) {
            $this->db->where('DATE(datetime) >=', $start_date);
            $this->db->where('DATE(datetime) <=', $end_date);
        }

        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Get daily transaction summary
     */
    public function get_daily_summary($start_date = null, $end_date = null)
    {
        $this->db->select('
            DATE(datetime) as transaction_date,
            COUNT(*) as total_transactions,
            SUM(CASE WHEN action = "store" THEN 1 ELSE 0 END) as store_count,
            SUM(CASE WHEN action = "take" THEN 1 ELSE 0 END) as take_count
        ');
        $this->db->from('as_report');

        if ($start_date && $end_date) {
            $this->db->where('DATE(datetime) >=', $start_date);
            $this->db->where('DATE(datetime) <=', $end_date);
        }

        $this->db->group_by('DATE(datetime)');
        $this->db->order_by('transaction_date', 'DESC');

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Search transactions
     */
    public function search_transactions($search_term, $filters = array())
    {
        $this->db->select('r.*, u.name as user_name');
        $this->db->from('as_report r');
        $this->db->join('as_user u', 'r.nik = u.nik', 'left');

        if ($search_term) {
            $this->db->group_start();
            $this->db->like('r.storing_id', $search_term);
            $this->db->or_like('r.location_id', $search_term);
            $this->db->or_like('r.category', $search_term);
            $this->db->or_like('r.type_id', $search_term);
            $this->db->or_like('r.note', $search_term);
            $this->db->or_like('u.name', $search_term);
            $this->db->group_end();
        }

        // Apply filters
        if (isset($filters['action']) && $filters['action']) {
            $this->db->where('r.action', $filters['action']);
        }

        if (isset($filters['location_id']) && $filters['location_id']) {
            $this->db->where('r.location_id', $filters['location_id']);
        }

        if (isset($filters['category']) && $filters['category']) {
            $this->db->where('r.category', $filters['category']);
        }

        if (isset($filters['start_date']) && $filters['start_date']) {
            $this->db->where('DATE(r.datetime) >=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date']) {
            $this->db->where('DATE(r.datetime) <=', $filters['end_date']);
        }

        $this->db->order_by('r.datetime', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Count total transactions
     */
    public function count_transactions($filters = array())
    {
        $this->db->from('as_report r');

        if (isset($filters['action']) && $filters['action']) {
            $this->db->where('r.action', $filters['action']);
        }

        if (isset($filters['location_id']) && $filters['location_id']) {
            $this->db->where('r.location_id', $filters['location_id']);
        }

        if (isset($filters['category']) && $filters['category']) {
            $this->db->where('r.category', $filters['category']);
        }

        if (isset($filters['start_date']) && $filters['start_date']) {
            $this->db->where('DATE(r.datetime) >=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date']) {
            $this->db->where('DATE(r.datetime) <=', $filters['end_date']);
        }

        return $this->db->count_all_results();
    }
}
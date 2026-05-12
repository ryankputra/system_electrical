<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Location_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Id_generator_model');
    }

    /**
     * Get all locations ordered by name
     * @return array
     */
    public function get_all()
    {
        return $this->db->order_by('location_name', 'ASC')->get('as_location')->result_array();
    }

    /**
     * Backwards-compatible alias
     */
    public function get_all_locations()
    {
        return $this->get_all();
    }

    /**
     * Insert a new location
     * @param array $data
     * @return bool
     */
    public function insert($data)
    {
        // Generate manual ID using centralized generator
        $newId = $this->Id_generator_model->generate_manual_id('as_location', 'id');
        $data['id'] = (string) $newId;
        return $this->db->insert('as_location', $data);
    }

    /**
     * Delete a location by id
     * @param mixed $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->db->where('id', $id)->delete('as_location');
    }
}
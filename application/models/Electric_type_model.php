<?php
/**
 * Electric Type Model
 *
 * Handles database operations related to electric types, including CRUD operations.
 *
 * @package ElectricalSystem
 * @subpackage Models
 * @category ElectricType
 * @version 1.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Electric_type_model extends CI_Model {

    private string $table = 'as_electric_types';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve all electric types with their usage count.
     *
     * @return array
     */
    public function getAllTypes(): array
    {
        $this->db->select('t.*, COUNT(e.type_id) AS usage_count');
        $this->db->from($this->table . ' AS t');
        $this->db->join('as_electric AS e', 't.id = e.type_id', 'left');
        $this->db->group_by('t.id');
        $this->db->order_by('t.type', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Get electric type by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $this->db->select('t.*, COUNT(e.type_id) AS usage_count');
        $this->db->from($this->table . ' AS t');
        $this->db->join('as_electric AS e', 't.id = e.type_id', 'left');
        $this->db->where('t.id', $id);
        $this->db->group_by('t.id');
        return $this->db->get()->row_array();
    }
    
    /**
     * Get electric type by its string identifier.
     *
     * @param string $type
     * @return array|null
     */
    public function getByType(string $type): ?array
    {
        return $this->db->get_where($this->table, ['type' => strtoupper($type)])->row_array();
    }

    /**
     * Add a new electric type.
     *
     * @param string $type
     * @param string|null $imageFilename
     * @return int Inserted ID
     */
    public function addType(string $type, ?string $imageFilename = null): int
    {
        $data = [
            'type' => strtoupper($type),
            'image' => $imageFilename,
            'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
        ];
        $this->db->insert($this->table, $data);
        return (int)$this->db->insert_id();
    }

    /**
     * Edit an existing electric type.
     *
     * @param int $id
     * @param string $type
     * @param string|null $imageFilename
     * @return bool Success or failure
     */
    public function editType(int $id, string $type, ?string $imageFilename = null): bool
    {
        $data = [
            'type' => strtoupper($type),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
        ];
        if ($imageFilename !== null) {
            $data['image'] = $imageFilename;
        }
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    /**
     * Delete an electric type.
     *
     * @param int $id
     * @return bool Success or failure
     */
    public function deleteType(int $id): bool
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Check if an electric type exists, optionally excluding a specific ID.
     *
     * @param string $type
     * @param int|null $excludeId
     * @return bool Existence of the type
     */
    public function isTypeExists(string $type, ?int $excludeId = null): bool
    {
        $this->db->where('type', strtoupper($type));
        if ($excludeId !== null) {
            $this->db->where('id !=', $excludeId);
        }
        return $this->db->count_all_results($this->table) > 0;
    }
}
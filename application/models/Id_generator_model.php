<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ID Generator Model
 *
 * Provides a centralized method to generate manual sequential IDs
 * for tables that no longer use AUTO_INCREMENT.
 */
class Id_generator_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Generate a manual numeric ID for a table by selecting the current
     * maximum numeric value of the given id column and returning +1.
     *
     * @param string $table Table name
     * @param string $id_column Column name that stores the id (default: id)
     * @return string New ID (string to allow storing in VARCHAR columns)
     */
    public function generate_manual_id(string $table, string $id_column = 'id'): string
    {
        // Use CAST(... AS UNSIGNED) to support numeric string ids stored in VARCHAR
        $sql = "SELECT MAX(CAST(`{$id_column}` AS UNSIGNED)) AS maxid FROM `{$table}`";
        $row = $this->db->query($sql)->row_array();
        $max = isset($row['maxid']) && is_numeric($row['maxid']) ? intval($row['maxid']) : 0;
        return (string) ($max + 1);
    }
}

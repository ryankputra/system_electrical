<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History_model extends CI_Model
{
    private string $table = 'as_history';
    private string $electricTable = 'as_electric';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get last ID from as_history (manual id logic)
     * @return int
     */
    public function getLastId(): int
    {
        $row = $this->db->select_max('id')->get($this->table)->row_array();
        return isset($row['id']) && $row['id'] !== null ? (int) $row['id'] : 0;
    }

    /**
     * Compatibility wrapper: snake_case name requested by spec
     * @return int
     */
    public function get_last_id(): int
    {
        return $this->getLastId();
    }

    /**
     * Add a transaction (history record) and update stock column in as_electric (if present).
     * Uses a DB transaction to keep things consistent.
     *
     * $data should contain: electric_id, type ('IN'|'OUT'), qty (int), user_nik (string), optional note
     *
     * @param array $data
     * @return array ['success' => bool, 'id' => int|null, 'message' => string]
     */
    public function addTransaction(array $data): array
    {
        $this->db->trans_begin();

        try {
            // Use centralized ID generator for manual numeric IDs
            $this->load->model('Id_generator_model');
            $nextId = (int) $this->Id_generator_model->generate_manual_id($this->table, 'id');
            // Normalize type: accept 'Masuk'/'Keluar' or 'IN'/'OUT'
            $typeRaw = $data['type'] ?? '';
            $typeNorm = in_array(strtolower($typeRaw), ['masuk', 'in']) ? 'Masuk' : 'Keluar';
            $insert = [
                'id' => $nextId,
                'electric_id' => $data['electric_id'],
                'type' => $typeNorm,
                'qty' => (int) $data['qty'],
                'user_nik' => $data['user_nik'] ?? null,
                'keterangan' => $data['keterangan'] ?? ($data['note'] ?? null),
                'date' => $data['date'] ?? date('Y-m-d H:i:s'),
            ];

            $this->db->insert($this->table, $insert);

            // Update stock in as_electric if column exists
            if ($this->db->field_exists('stock', $this->electricTable)) {
                // Read current stock (if any)
                $electric = $this->db->get_where($this->electricTable, ['electric_id' => $data['electric_id']])->row_array();
                $currentStock = isset($electric['stock']) ? (int) $electric['stock'] : 0;

                if (strtolower($insert['type']) === 'masuk') {
                    $newStock = $currentStock + $insert['qty'];
                } else {
                    $newStock = $currentStock - $insert['qty'];
                    if ($newStock < 0) $newStock = 0; // prevent negative stock
                }

                // Update the electric table
                $this->db->where('electric_id', $data['electric_id'])->update($this->electricTable, ['stock' => $newStock]);
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return ['success' => false, 'id' => null, 'message' => 'Database transaction failed'];
            }

            $this->db->trans_commit();
            return ['success' => true, 'id' => $nextId, 'message' => 'Transaction recorded'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'id' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Compatibility wrapper: insert_history per spec
     * @param array $data
     * @return array
     */
    public function insert_history(array $data): array
    {
        return $this->addTransaction($data);
    }

    /**
     * Return all history rows joined with electric name and user name.
     * @return array
     */
    public function get_all_history(): array
    {
        $this->db->select('h.*, e.nama as nama_barang, u.name as user_name');
        $this->db->from($this->table . ' h');
        $this->db->join('as_electric e', 'h.electric_id = e.electric_id', 'left');
        $this->db->join('as_user u', 'h.user_nik = u.nik', 'left');
        $this->db->order_by('h.date', 'DESC');
        return $this->db->get()->result_array();
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * User Model
 *
 * Handles database operations related to users, including authentication and profile management.
 *
 * @package ElectricalSystem
 * @subpackage Models
 * @category User
 * @version 1.0.0
 */
class User_model extends CI_Model
{
    /**
     * The name of the user database table used by this model.
     *
     * @var string
     */
    private string $userTable = 'as_user';

    /**
     * Retrieves a list of users based on search, filter, and sort criteria.
     *
     * @param int         $limit         Number of records to retrieve.
     * @param int         $start         Offset for pagination.
     * @param string|null $searchKeyword Optional keyword to search in `nik` or `name` fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     * @param string|null $sortKeyword   Optional sort criteria in the format "field-order".
     *
     * @return array An array of user records matching the criteria.
     */
    public function getUser(int $limit, int $start, ?string $searchKeyword = null, ?array $filterKeyword = null, ?string $sortKeyword = null): array
    {
        $this->userSearchAndFilters($searchKeyword, $filterKeyword);

        if ($sortKeyword && strpos($sortKeyword, '-') !== false) {
            [$field, $order] = explode('-', $sortKeyword, 2);
            $this->db->order_by($field, $order);
        } else {
            $this->db->order_by('updated_at', 'DESC');
        }

        return $this->db->get($this->userTable, $limit, $start)->result_array();
    }

    /**
     * Counts the number of user records matching search and filter criteria.
     *
     * @param string|null $searchKeyword Optional keyword to search in `nik` or `name` fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return int The total number of records matching the criteria.
     */
    public function countUser(?string $searchKeyword = null, ?array $filterKeyword = null): int
    {
        $this->userSearchAndFilters($searchKeyword, $filterKeyword);
        return $this->db->count_all_results($this->userTable);
    }

    /**
     * Retrieves distinct values of a specific field for filter dropdowns.
     *
     * @param string      $field         The field to retrieve distinct values from.
     * @param string|null $searchKeyword Optional keyword to search in `nik` or `name` fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return array An array of distinct values.
     */
    public function getUserFilter(string $field, ?string $searchKeyword = null, ?array $filterKeyword = null): array
    {
        $this->db->select($field);
        $this->userSearchAndFilters($searchKeyword, $filterKeyword);
        $this->db->distinct()->order_by($field, 'ASC');
        $query = $this->db->get($this->userTable);
        return array_column($query->result_array(), $field);
    }

    /**
     * Inserts a new user record into the database.
     *
     * @return void
     */
    public function addUser(): void
    {
        $userData = [
            'nik'        => $this->input->post('nik', true),
            'name'       => ucwords(strtolower($this->input->post('name', true))),
            'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'     => $this->session->userdata('user_data')['nik'],
        ];
        $this->db->insert($this->userTable, $userData);
    }

    /**
     * Retrieves a single user record by their unique NIK.
     *
     * @param int $nik The NIK of the user to retrieve.
     *
     * @return array|null The user record, or null if not found.
     */
    public function getByNik(int $nik): ?array
    {
        return $this->db->get_where($this->userTable, ['nik' => $nik])->row_array();
    }

    /**
     * Updates an existing user's details.
     *
     * @param int $nik The NIK of the user to update.
     *
     * @return void
     */
    public function editUser(int $nik): void
    {
        $userData = [
            'name'       => ucwords(strtolower($this->input->post('name', true))),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
            'editor'     => $this->session->userdata('user_data')['nik'],
        ];
        $this->db->update($this->userTable, $userData, ['nik' => $nik]);
    }

    /**
     * Deletes a user record based on their NIK.
     *
     * @param int $nik The NIK of the user to delete.
     *
     * @return void
     */
    public function deleteUser(int $nik): void
    {
        $this->db->where('nik', $nik)->delete($this->userTable);
    }

    /**
     * Checks if a given NIK already exists in the database.
     *
     * @param int $nik The NIK to check.
     *
     * @return bool Returns true if the NIK exists, false otherwise.
     */
    public function isNikExists(int $nik): bool
    {
        return $this->db->where('nik', $nik)->count_all_results($this->userTable) > 0;
    }

    /**
     * Inserts multiple user records in a single batch operation.
     *
     * @param array $data An array of associative arrays containing user data.
     *
     * @return void
     */
    public function insertBatch(array $data): void
    {
        $this->db->insert_batch($this->userTable, $data);
    }

    /**
     * A private helper method to apply search and filter conditions for user retrieval.
     *
     * @param string|null $searchKeyword Optional keyword to search in user fields.
     * @param array|null  $filterKeyword Optional associative array of filters.
     *
     * @return void
     */
    private function userSearchAndFilters(?string $searchKeyword, ?array $filterKeyword): void
    {
        if ($searchKeyword && trim($searchKeyword) !== '') {
            $this->db->group_start()
                ->like('nik', trim($searchKeyword))
                ->or_like('name', trim($searchKeyword))
                ->group_end();
        }
        if ($filterKeyword && is_array($filterKeyword)) {
            foreach ($filterKeyword as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    $this->db->where_in($key, $value);
                }
            }
        }
    }
}

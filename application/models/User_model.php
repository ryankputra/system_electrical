<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * User Model
 * * Mengelola operasi database user termasuk autentikasi password hash.
 */
class User_model extends CI_Model
{
    private string $userTable = 'as_user';

    /**
     * FUNGSI KRUSIAL: Verifikasi Login dengan Password Hash
     */
    public function checkLogin($nik, $password)
{
    // Gunakan trim untuk menghapus spasi di depan/belakang NIK dan password
    $nik = trim($nik);
    $password = trim($password);

    // Cari user
    $user = $this->db->get_where($this->userTable, ['nik' => $nik])->row_array();

    if ($user) {
        // Kita bandingkan password hash-nya
        // Gunakan trim juga pada data dari database untuk jaga-jaga
        if (password_verify($password, trim($user['password']))) {
            return $user;
        }
    }

    return false;
}
    /**
     * Ambil data user tunggal berdasarkan NIK
     */
    public function getByNik($nik)
    {
        return $this->db->get_where($this->userTable, ['nik' => $nik])->row_array();
    }

    /**
     * Ambil daftar user dengan filter dan search (untuk Admin)
     */
    public function getUser(int $limit, int $start, ?string $searchKeyword = null, ?array $filterKeyword = null, ?string $sortKeyword = null): array
    {
        $this->userSearchAndFilters($searchKeyword, $filterKeyword);

        if ($sortKeyword && strpos($sortKeyword, '-') !== false) {
            [$field, $order] = explode('-', $sortKeyword, 2);
            $this->db->order_by($field, $order);
        } else {
            // Prefer ordering by updated_at if available, otherwise created_at, else fallback to primary key
            if ($this->db->field_exists('updated_at', $this->userTable)) {
                $this->db->order_by('updated_at', 'DESC');
            } elseif ($this->db->field_exists('created_at', $this->userTable)) {
                $this->db->order_by('created_at', 'DESC');
            } elseif ($this->db->field_exists('id', $this->userTable)) {
                $this->db->order_by('id', 'DESC');
            } else {
                // Fallback to NIK (primary identifier) to avoid SQL errors
                $this->db->order_by('nik', 'DESC');
            }
        }

        return $this->db->get($this->userTable, $limit, $start)->result_array();
    }

    /**
     * Hitung total user untuk pagination
     */
    public function countUser(?string $searchKeyword = null, ?array $filterKeyword = null): int
    {
        $this->userSearchAndFilters($searchKeyword, $filterKeyword);
        return $this->db->count_all_results($this->userTable);
    }

    /**
     * Tambah user baru (Sekarang menyertakan Password & Role)
     */
    public function addUser(): void
    {
        $userData = [
            'nik'        => $this->input->post('nik', true),
            'name'       => ucwords(strtolower($this->input->post('name', true))),
            'password'   => password_hash($this->input->post('password', true), PASSWORD_DEFAULT), // Hash password!
            'role'       => $this->input->post('role', true), // Ambil role dari form
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'editor'     => $this->session->userdata('user_data')['nik'] ?? null,
        ];
        $this->db->insert($this->userTable, $userData);
    }

    /**
     * Update data user
     */
    public function editUser($nik): void
    {
        $userData = [
            'name'       => ucwords(strtolower($this->input->post('name', true))),
            'role'       => $this->input->post('role', true),
            'updated_at' => date('Y-m-d H:i:s'),
            'editor'     => $this->session->userdata('user_data')['nik'] ?? null,
        ];
        
        // Jika password diisi, maka update password juga
        if ($this->input->post('password')) {
            $userData['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }

        $this->db->update($this->userTable, $userData, ['nik' => $nik]);
    }

    public function deleteUser($nik): void
    {
        $this->db->where('nik', $nik)->delete($this->userTable);
    }

    public function isNikExists($nik): bool
    {
        return $this->db->where('nik', $nik)->count_all_results($this->userTable) > 0;
    }

    public function insertBatch(array $data): void
    {
        $this->db->insert_batch($this->userTable, $data);
    }

    /**
     * Ambil daftar role yang diizinkan dari definisi kolom enum `role` di tabel `as_user`.
     * Mengembalikan array string, misal ['admin','warehouse','staff'].
     *
     * @return array
     */
    public function getRoles(): array
    {
        $query = $this->db->query("SHOW COLUMNS FROM `{$this->userTable}` LIKE 'role'");
        $row = $query->row_array();
        if (!$row || empty($row['Type'])) {
            return [];
        }
        $type = $row['Type']; // contoh: enum('admin','warehouse','staff')
        preg_match_all("/'([^']+)'/", $type, $matches);
        return isset($matches[1]) ? $matches[1] : [];
    }

    /**
     * Helper Search & Filter
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
                if (!empty($value)) {
                    $this->db->where($key, $value);
                }
            }
        }
    }
}
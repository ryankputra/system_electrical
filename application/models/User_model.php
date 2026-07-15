<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * User Model
 *
 * Mengelola operasi database terkait pengguna, termasuk autentikasi 
 * dan manajemen profil dengan menggunakan NIK sebagai Primary Key manual.
 *
 * @package OE-Inventory
 * @category Models
 * @version 1.1.0
 */
class User_model extends CI_Model
{
    /**
     * Nama tabel database user.
     *
     * @var string
     */
    private string $userTable = 'as_user';

    /**
     * Memverifikasi login berdasarkan NIK dan Password.
     * * @param string $nik
     * @param string $password
     * @return array|bool Data user jika berhasil, false jika gagal.
     */
    public function checkLogin(string $nik, string $password)
    {
        // Cari user berdasarkan NIK (Manual ID)
        $user = $this->db->get_where($this->userTable, ['nik' => $nik])->row_array();

        if ($user) {
            // Verifikasi password menggunakan password_verify (standar PHP)
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    /**
     * Mengambil daftar user berdasarkan kriteria pencarian, filter, dan sort.
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
     * Menghitung jumlah total user sesuai filter (untuk pagination).
     */
    public function countUser(?string $searchKeyword = null, ?array $filterKeyword = null): int
    {
        $this->userSearchAndFilters($searchKeyword, $filterKeyword);
        return $this->db->count_all_results($this->userTable);
    }

    /**
     * Menambahkan user baru ke database tanpa auto-increment.
     */
    public function addUser(): void
    {
        $userData = [
            'nik'        => $this->input->post('nik', true), // Diisi manual dari input
            'name'       => ucwords(strtolower($this->input->post('name', true))),
            'password'   => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role'       => $this->input->post('role', true), // admin, warehouse, atau staff
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'editor'     => $this->session->userdata('nik'), 
        ];
        $this->db->insert($this->userTable, $userData);
    }

    /**
     * Mengambil satu record user berdasarkan NIK.
     */
    public function getByNik(string $nik): ?array
    {
        return $this->db->get_where($this->userTable, ['nik' => $nik])->row_array();
    }

    /**
     * Mengupdate data user.
     */
    public function editUser(string $nik): void
    {
        $userData = [
            'name'       => ucwords(strtolower($this->input->post('name', true))),
            'role'       => $this->input->post('role', true),
            'updated_at' => date('Y-m-d H:i:s'),
            'editor'     => $this->session->userdata('nik'),
        ];
        
        // Jika password diisi, update password juga
        if ($this->input->post('password')) {
            $userData['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }

        $this->db->update($this->userTable, $userData, ['nik' => $nik]);
    }

    /**
     * Menghapus user berdasarkan NIK.
     */
    public function deleteUser(string $nik): void
    {
        $this->db->where('nik', $nik)->delete($this->userTable);
    }

    /**
     * Cek apakah NIK sudah terdaftar (untuk validasi).
     */
    public function isNikExists(string $nik): bool
    {
        return $this->db->where('nik', $nik)->count_all_results($this->userTable) > 0;
    }

    /**
     * Helper privat untuk filter pencarian.
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
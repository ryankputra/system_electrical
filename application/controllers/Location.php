<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Location extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }
        // Allow Admin full access, Manager OE view-only (enforced in methods)
        if (!(is_admin() || is_manajer_oe())) {
            $this->session->set_flashdata('action', ['danger', 'Akses ditolak. Hanya Admin dan Manager OE yang dapat mengakses fitur ini.']);
            redirect(base_url());
        }
        $this->load->model('Location_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'common']);
    }

    public function index(): void
    {
        $locations = $this->Location_model->get_all();

        // Compute how many electrics reference each location so the view can disable delete buttons
        $location_counts = [];
        if ($this->db->table_exists('as_electric')) {
            foreach ($locations as $loc) {
                $locId = $loc['id'] ?? null;
                $locName = $loc['location_name'] ?? ($loc['location'] ?? null);
                $conds = [];
                if ($this->db->field_exists('location', 'as_electric')) {
                    $conds[] = 'location = ' . $this->db->escape($locId);
                    $conds[] = 'location = ' . $this->db->escape($locName);
                }
                if ($this->db->field_exists('location_id', 'as_electric')) {
                    $conds[] = 'location_id = ' . $this->db->escape($locId);
                    $conds[] = 'location_id = ' . $this->db->escape($locName);
                }
                if ($this->db->field_exists('location_name', 'as_electric')) {
                    $conds[] = 'location_name = ' . $this->db->escape($locName);
                }

                if (!empty($conds)) {
                    $whereSql = '(' . implode(' OR ', $conds) . ')';
                    $count = $this->db->where($whereSql, null, false)->get('as_electric')->num_rows();
                } else {
                    $count = 0;
                }
                $location_counts[(string)$locId] = $count;
            }
        }

        $data = [
            'title' => 'Master Lokasi',
            'locations' => $locations,
            'location_counts' => $location_counts,
        ];
        render_view('location/index', $data);
    }

    public function add(): void
    {
        // Only Admin can add locations
        if (!is_admin()) {
            set_message(['danger', 'Akses ditolak. Hanya Admin yang dapat menambahkan lokasi.']);
            redirect('location');
            return;
        }
        
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('location_name', 'Nama Lokasi', 'required|trim|max_length[100]');
            if ($this->form_validation->run()) {
                $insert = $this->Location_model->insert(['location_name' => $this->input->post('location_name', true)]);
                if ($insert) {
                    set_message(['success', 'Lokasi berhasil ditambahkan.']);
                } else {
                    set_message(['danger', 'Gagal menambahkan lokasi.']);
                }
                redirect('location');
            }
        }
        redirect('location');
    }

    public function delete($id): void
    {
        // Only Admin can delete locations
        if (!is_admin()) {
            set_message(['danger', 'Akses ditolak. Hanya Admin yang dapat menghapus lokasi.']);
            redirect('location');
            return;
        }
        
        $id = urldecode($id);

        // 1. Cek apakah lokasi ada
        $lokasi = $this->db->get_where('as_location', ['id' => $id])->row_array();
        if (!$lokasi) {
            set_message(['danger', 'Lokasi tidak ditemukan.']);
            redirect('location');
            return;
        }

        // 2. Cek apakah ada barang yang masih memakai lokasi ini.
        // Beberapa skema menyimpan lokasi sebagai ID (location/location_id), beberapa sebagai nama.
        $cek_barang = 0;
        if ($this->db->table_exists('as_electric')) {
            $conds = [];
            // match by location column if exists (could store id or name)
            if ($this->db->field_exists('location', 'as_electric')) {
                $conds[] = "location = " . $this->db->escape($lokasi['id']);
                $conds[] = "location = " . $this->db->escape($lokasi['location_name']);
            }
            // match by location_id column if exists
            if ($this->db->field_exists('location_id', 'as_electric')) {
                $conds[] = "location_id = " . $this->db->escape($lokasi['id']);
                $conds[] = "location_id = " . $this->db->escape($lokasi['location_name']);
            }
            // match by explicit location_name column if present in electric table
            if ($this->db->field_exists('location_name', 'as_electric')) {
                $conds[] = "location_name = " . $this->db->escape($lokasi['location_name']);
            }

            if (!empty($conds)) {
                $whereSql = '(' . implode(' OR ', $conds) . ')';
                $cek_barang = $this->db->where($whereSql, null, false)->get('as_electric')->num_rows();
            }
        }

        if ($cek_barang > 0) {
            // Jika ada barang, jangan hapus
            set_message(['danger', 'Gagal hapus! Masih ada ' . $cek_barang . ' barang di lokasi ' . $lokasi['location_name']]);
        } else {
            // Jika kosong, gunakan model untuk hapus (mengikuti aturan manual ID)
            $this->Location_model->delete($id);
            set_message(['success', 'Lokasi berhasil dihapus!']);
        }
        redirect('location');
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Procurement extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        require_login();
        $this->load->database();
    }

    /**
     * AJAX endpoint: return JSON list of items available in a given location
     * URL: /procurement/get_barang_by_lokasi/{lokasi_id}
     * Response: [{id: electric_id, text: 'Nama - Brand - Spesifikasi'}, ...]
     */
    public function get_barang_by_lokasi($lokasi_id = null)
    {
        $lokasi_id = (int) ($lokasi_id ?? $this->input->get('lokasi_id', true));
        if ($lokasi_id <= 0) {
            return $this->output->set_content_type('application/json')->set_output(json_encode([]));
        }

        $tbl = 'as_electric';
        if (!$this->db->table_exists($tbl)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode([]));
        }

        // detect location column on electric table
        $whereCol = null;
        if ($this->db->field_exists('location_id', $tbl)) $whereCol = 'location_id';
        elseif ($this->db->field_exists('location', $tbl)) $whereCol = 'location';
        if ($whereCol === null) {
            return $this->output->set_content_type('application/json')->set_output(json_encode([]));
        }

        // Build select list with available spec columns
        $select = 'electric_id, nama';
        if ($this->db->field_exists('brand', $tbl)) $select .= ', brand';
        $specCols = ['voltage','voltage_unit','ampere','daya','model','type'];
        $pickedSpecs = [];
        foreach ($specCols as $c) {
            if ($this->db->field_exists($c, $tbl)) { $select .= ', ' . $c; $pickedSpecs[] = $c; }
        }

        $rows = $this->db->select($select, false)
                        ->from($tbl)
                        ->where($whereCol, $lokasi_id)
                        ->order_by('nama', 'ASC')
                        ->get()->result_array();

        $out = [];
        foreach ($rows as $r) {
            // Build text in the format: [NAMA_BARANG] | BRAND: [BRAND] | SPEC: [VOLT][UNIT], [AMPERE]A, [DAYA]W | MODEL: [MODEL]
            $name = trim((string)($r['nama'] ?? ''));
            $type = trim((string)($r['type'] ?? ''));
            
            $brandText = '';
            if (!empty($r['brand'])) {
                $brandText = ' | BRAND: ' . trim($r['brand']);
            }

            // SPEC parts
            $specParts = [];
            // voltage + unit
            if (in_array('voltage', $pickedSpecs) && trim((string)($r['voltage'] ?? '')) !== '') {
                $vol = trim((string)$r['voltage']);
                $unit = (in_array('voltage_unit', $pickedSpecs) && trim((string)($r['voltage_unit'] ?? '')) !== '') ? trim((string)$r['voltage_unit']) : '';
                $specParts[] = $vol . $unit;
            }
            // ampere
            if (in_array('ampere', $pickedSpecs) && trim((string)($r['ampere'] ?? '')) !== '' && floatval($r['ampere']) != 0) {
                $specParts[] = trim((string)$r['ampere']) . 'A';
            }
            // daya
            if (in_array('daya', $pickedSpecs) && trim((string)($r['daya'] ?? '')) !== '' && floatval($r['daya']) != 0) {
                $specParts[] = trim((string)$r['daya']) . 'W';
            }

            $specText = '';
            if (count($specParts) > 0) {
                $specText = ' | SPEC: ' . implode(', ', $specParts);
            }

            $label = htmlspecialchars(strtoupper($name) . ($type !== '' ? ' - ' . strtoupper($type) : '') . $brandText . $specText);
            $out[] = ['id' => $r['electric_id'], 'text' => $label];
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }
}

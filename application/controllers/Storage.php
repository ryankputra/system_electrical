<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Storage extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }
        $this->load->model([
            'Storage_model',
            'Report_model',
            'Electric_model',
            'Electric_type_model'
        ]);
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'common']);
    }

    public function index()
    {
        $data['title'] = 'Storage Overview';
        $data['user_data'] = $this->session->userdata('user_data');
        try {
            $keyword = $this->input->get('keyword', TRUE);
            $data['keyword'] = $keyword;
            $data['sort'] = $this->session->userdata('sort') ?: 'item_code';
            $data['recent_transactions'] = $this->Report_model->get_all_transactions(10);
            $data['locations'] = $this->Storage_model->get_all_locations();
            if ($keyword) {
                $rows = $this->Storage_model->search_storage($keyword);
                $location_ids = array_values(array_filter(array_unique(array_column($rows, 'location_id'))));
                if (!empty($location_ids)) {
                    $data['locations'] = array_map(fn($id) => ['location_id' => $id], $location_ids);
                } else {
                    $data['locations'] = [];
                }
                $agg = [];
                foreach ($rows as $r) {
                    $key = ($r['category'] ?? '') . '|' . ($r['type_id'] ?? '');
                    if (!isset($agg[$key])) {
                        $agg[$key] = [
                            'category' => $r['category'] ?? '',
                            'type_id' => $r['type_id'] ?? '',
                            'total_amount' => 0,
                            'location_count' => [],
                        ];
                    }
                    $agg[$key]['total_amount'] += (int)($r['amount'] ?? 0);
                    if (!empty($r['location_id'])) {
                        $agg[$key]['location_count'][$r['location_id']] = true;
                    }
                }
                $data['storage_overview'] = array_values(array_map(function ($v) {
                    $v['location_count'] = count($v['location_count']);
                    return $v;
                }, $agg));
            } else {
                $data['storage_overview'] = $this->Storage_model->get_storage_overview();
            }
            render_view('storage/index', $data);
        } catch (Exception $e) {
            $data['error'] = 'Storage system temporarily unavailable: ' . $e->getMessage();
            render_view('storage/index', $data);
        }
    }

    public function location($location_id = null)
    {
        if (!$location_id) {
            redirect('storage');
        }
        $location_id = trim(rawurldecode($location_id));
        $keyword = $this->input->get('keyword', TRUE);
        $data['title'] = 'Storage Location: ' . $location_id;
        $data['location_id'] = $location_id;
        $data['storage_items'] = $this->Storage_model->get_storage_by_location($location_id, $keyword);
        $data['location_transactions'] = $this->Report_model->get_transactions_by_location($location_id, 20);
        $data['electric_items'] = $this->Electric_model->getAllElectrics();
        $data['locations'] = $this->Storage_model->get_all_locations();
        $data['keyword'] = $keyword;
        $this->load->view('templates/header', $data);
        $this->load->view('storage/location', $data);
        $this->load->view('templates/footer');
    }

    public function store()
    {
        $data['title'] = 'Store Items';
        $data['electric_items'] = $this->Electric_model->getAllElectrics();
        $data['locations'] = $this->Electric_model->getElectricFilter('location');
        $data['electric_categories'] = $this->Electric_model->getCategories();
        $data['current_storage'] = $this->Storage_model->get_all_storage();
        $data['preselected_location'] = $this->input->get('location');
        $this->form_validation->set_rules('location_id', 'Location ID', 'required|max_length[64]');
        $this->form_validation->set_rules('category', 'Category', 'required|max_length[64]');
        $this->form_validation->set_rules('type_id', 'Type ID', 'required|max_length[64]');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('note', 'Note', 'max_length[255]');
        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('storage/store_form', $data);
            $this->load->view('templates/footer');
        } else {
            $this->process_store();
        }
    }

    private function process_store()
    {
        $location_id = $this->input->post('location_id');
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity');
        $note = $this->input->post('note');
        $editor_nik = $this->session->userdata('user_data')['nik'];
        $is_project_item = $this->input->post('is_project_item') ? true : false;
        $project_name = $this->input->post('project_name') ?: null;
        $db_type_id = $type_id;
        if ($is_project_item && substr($db_type_id, -8) !== '_PROJECT') {
            $db_type_id .= '_PROJECT';
        }
        $storage_data = null;
        if ($is_project_item) {
            $storage_data = ['project_name' => $project_name, 'project_flag' => true];
        }
        $store_result = $this->Storage_model->store_items($location_id, $category, $db_type_id, $quantity, $editor_nik, $storage_data);
        if ($store_result) {
            $this->Report_model->log_store_transaction($location_id, $category, $db_type_id, $editor_nik, $note, $project_name);
            $this->session->set_flashdata('success', 'Items stored successfully!');
            redirect('storage/location/' . rawurlencode($location_id));
        } else {
            $this->session->set_flashdata('error', 'Failed to store items!');
            redirect('storage/store');
        }
    }

    public function take()
    {
        $data['title'] = 'Take Items';
        $data['storage_items'] = $this->Storage_model->get_all_storage();
        $data['locations'] = $this->Storage_model->get_all_locations();
        $data['electric_items'] = $this->Electric_model->getAllElectrics();
        $data['electric_categories'] = $this->Electric_model->getCategories();
        $data['preselected_location'] = $this->input->get('location');
        $this->form_validation->set_rules('location_id', 'Location ID', 'required|max_length[64]');
        $this->form_validation->set_rules('category', 'Category', 'required|max_length[64]');
        $this->form_validation->set_rules('type_id', 'Type ID', 'required|max_length[64]');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('note', 'Note', 'max_length[255]');
        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('storage/take_form', $data);
            $this->load->view('templates/footer');
        } else {
            $this->process_take();
        }
    }

    private function process_take()
    {
        $location_id = $this->input->post('location_id');
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity');
        $note = $this->input->post('note');
        $editor_nik = $this->session->userdata('user_data')['nik'];
        $take_result = $this->Storage_model->take_items($location_id, $category, $type_id, $quantity, $editor_nik);
        if ($take_result['success']) {
            $this->Report_model->log_take_transaction($location_id, $category, $type_id, $editor_nik, $note);
            $this->session->set_flashdata('success', $take_result['message']);
            redirect('storage/location/' . rawurlencode($location_id));
        } else {
            $this->session->set_flashdata('error', $take_result['message']);
            redirect('storage/take');
        }
    }

    public function search()
    {
        $search_term = $this->input->get('q');
        $location_id = $this->input->get('location');
        $category = $this->input->get('category');
        $data['title'] = 'Search Storage';
        $data['search_term'] = $search_term;
        $data['selected_location'] = $location_id;
        $data['selected_category'] = $category;
        $data['locations'] = $this->Storage_model->get_all_locations();
        $data['electric_items'] = $this->Electric_model->getAllElectrics();
        if ($search_term || $location_id || $category) {
            $data['search_results'] = $this->Storage_model->search_storage($search_term, $location_id, $category);
        } else {
            $data['search_results'] = array();
        }
        $this->load->view('templates/header', $data);
        $this->load->view('storage/search', $data);
        $this->load->view('templates/footer');
    }

    public function get_stock()
    {
        $type_id = $this->input->get('type_id');
        if ($type_id) {
            $stock_locations = $this->Storage_model->get_available_stock($type_id);
            $total_stock = $this->Storage_model->get_total_stock($type_id);
            $response = [
                'success' => true, 
                'stock_locations' => $stock_locations, 
                'total_stock' => $total_stock
            ];
        } else {
            $response = ['success' => false, 'message' => 'Type ID is required'];
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function get_item_details()
    {
        $location_id = $this->input->get('location_id');
        $type_id = $this->input->get('type_id');
        
        if ($location_id) {
            $location_id = trim(rawurldecode($location_id));
        }
        if ($type_id) {
            $type_id = trim(rawurldecode($type_id));
        }
        
        if ($location_id && $type_id) {
            $item = $this->Storage_model->get_storage_item($location_id, $type_id);
            
            if ($item) {
                $clean_type_id = str_replace('_PROJECT', '', $item['type_id']);
                $electric_details = $this->Electric_model->getById($clean_type_id);
                
                if ($electric_details) {
                    $item['electric_details'] = $electric_details;
                } else {
                    $item['electric_details'] = null;
                }
                
                if (empty($item['editor_name']) && !empty($item['editor'])) {
                    $this->load->model('user_model');
                    $user_data = $this->user_model->getByNik((int)$item['editor']);
                    if ($user_data) {
                        $item['editor_name'] = $user_data['name'];
                    }
                }
                
                $response = ['success' => true, 'item' => $item];
            } else {
                $response = ['success' => false, 'message' => 'Item not found in storage'];
            }
        } else {
            $missing = [];
            if (!$location_id) $missing[] = 'location_id';
            if (!$type_id) $missing[] = 'type_id';
            $response = ['success' => false, 'message' => 'Missing required parameters: ' . implode(', ', $missing)];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function reports()
    {
        $data['title'] = 'Storage Reports';
        $filters = [];
        $get = $this->input->get();
        foreach (['start_date', 'end_date', 'action', 'location', 'category'] as $key) {
            if (!empty($get[$key])) {
                $filters[$key == 'location' ? 'location_id' : $key] = $get[$key];
            }
        }
        if (isset($filters['location_id'])) {
            $filters['location_id'] = trim(rawurldecode($filters['location_id']));
        }
        $data['filters'] = $filters;
        $data['locations'] = $this->Storage_model->get_all_locations();
        $data['electric_items'] = $this->Electric_model->getAllElectrics();
        $data['transactions'] = !empty($filters) ? $this->Report_model->search_transactions('', $filters) : $this->Report_model->get_all_transactions(50);
        $data['stats'] = $this->Report_model->get_transaction_stats($get['start_date'] ?? null, $get['end_date'] ?? null);
        $data['daily_summary'] = $this->Report_model->get_daily_summary($get['start_date'] ?? null, $get['end_date'] ?? null);
        $this->load->view('templates/header', $data);
        $this->load->view('storage/reports', $data);
        $this->load->view('templates/footer');
    }

    public function quick_action()
    {
        $action = $this->input->post('action');
        $location_id = trim($this->input->post('location_id'));
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity') ?: 1;
        $note = $this->input->post('note');
        $editor_nik = $this->session->userdata('user_data')['nik'];

        if (empty($location_id)) {
             $response = ['success' => false, 'message' => 'Lokasi tidak boleh kosong. Periksa data master barang.'];
        } else if ($action === 'store') {
            $result = $this->Storage_model->store_items($location_id, $category, $type_id, $quantity, $editor_nik);
            if ($result) {
                $this->Report_model->log_store_transaction($location_id, $category, $type_id, $editor_nik, $note);
                $response = ['success' => true, 'message' => 'Barang berhasil disimpan'];
            } else {
                $response = ['success' => false, 'message' => 'Gagal menyimpan barang'];
            }
        } elseif ($action === 'take') {
            $result = $this->Storage_model->take_items($location_id, $type_id, $quantity, $editor_nik);
            if ($result['success']) {
                $this->Report_model->log_take_transaction($location_id, $category, $type_id, $editor_nik, $note);
            }
            $response = $result;
        } else {
            $response = ['success' => false, 'message' => 'Aksi tidak valid'];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    public function export_location_excel($location_id = null)
    {
        if (!$location_id) show_404();
        $location_id = trim(rawurldecode($location_id));
        $items = $this->Storage_model->get_storage_by_location($location_id);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Category')->setCellValue('B1', 'Type ID')->setCellValue('C1', 'Amount')->setCellValue('D1', 'Updated At');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $row = 2;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $item['category'])->setCellValue('B' . $row, $item['type_id'])->setCellValue('C' . $row, $item['amount'])->setCellValue('D' . $row, $item['updated_at']);
            $row++;
        }
        foreach (range('A', 'D') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $filename = 'storage_location_' . $location_id . '_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function export_search_excel()
    {
        $search_term = $this->input->get('search');
        $location_id = $this->input->get('location');
        $category = $this->input->get('category');
        $results = $this->Storage_model->search_storage($search_term, $location_id, $category);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Location ID')->setCellValue('B1', 'Category')->setCellValue('C1', 'Type ID')->setCellValue('D1', 'Amount')->setCellValue('E1', 'Updated At');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $row = 2;
        foreach ($results as $item) {
            $sheet->setCellValue('A' . $row, $item['location_id'])->setCellValue('B' . $row, $item['category'])->setCellValue('C' . $row, $item['type_id'])->setCellValue('D' . $row, $item['amount'])->setCellValue('E' . $row, $item['updated_at']);
            $row++;
        }
        foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $filename = 'storage_search_results_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function export_transactions_excel()
    {
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        $transactions = ($start_date && $end_date) ? $this->Report_model->get_transactions_by_date($start_date, $end_date, 1000) : $this->Report_model->get_all_transactions(1000);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Storing ID')->setCellValue('B1', 'Location ID')->setCellValue('C1', 'DateTime')->setCellValue('D1', 'Category')->setCellValue('E1', 'Type ID')->setCellValue('F1', 'Action')->setCellValue('G1', 'Note')->setCellValue('H1', 'NIK');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $row = 2;
        foreach ($transactions as $transaction) {
            $sheet->setCellValue('A' . $row, $transaction['storing_id'])->setCellValue('B' . $row, $transaction['location_id'])->setCellValue('C' . $row, $transaction['datetime'])->setCellValue('D' . $row, $transaction['category'])->setCellValue('E' . $row, $transaction['type_id'])->setCellValue('F' . $row, $transaction['action'])->setCellValue('G' . $row, $transaction['note'])->setCellValue('H' . $row, $transaction['nik']);
            $row++;
        }
        foreach (range('A', 'H') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $filename = 'storage_transactions_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function get_all_locations_json()
    {
        $locations = $this->Storage_model->get_all_locations();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'locations' => $locations]);
    }
}
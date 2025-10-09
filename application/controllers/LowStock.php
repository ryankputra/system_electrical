<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LowStock extends CI_Controller {
    public function detail() {
        $this->load->model('Storage_model');
        $data['low_stock'] = $this->Storage_model->get_low_stock();
        $this->load->view('storage/low_stock_detail', $data);
    }

    public function export_csv() {
        $this->load->model('Storage_model');
        $data['low_stock'] = $this->Storage_model->get_low_stock();

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment;filename="Low_Stock_Report_' . date('Ymd') . '.csv"');
        header('Cache-Control: max-age=0');

        // Output BOM for Excel compatibility with UTF-8
        echo "\xEF\xBB\xBF";

        // Open output stream
        $output = fopen('php://output', 'w');

        // Add header row
        fputcsv($output, ['Tipe Electrical', 'Kategori', 'Jumlah Stok']);

        // Add data rows
        foreach ($data['low_stock'] as $item) {
            $type_id = trim($item['type_id']);
            $category = trim($item['category']);
            $total_amount = trim($item['total_amount']);

            fputcsv($output, [$type_id, $category, $total_amount]);
        }

        // Close output stream
        fclose($output);
        exit;
    }
}

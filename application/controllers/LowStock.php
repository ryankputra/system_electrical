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

        // Open output stream
        $output = fopen('php://output', 'w');

    // Output BOM for Excel compatibility with UTF-8 (write to stream)
    fwrite($output, "\xEF\xBB\xBF");

    // Hint for Excel to use comma as separator
    // This makes Excel parse the CSV into columns correctly in many locales
    fwrite($output, "sep=,\r\n");

        // Add header row (explicit delimiter/enclosure)
        fputcsv($output, ['Tipe Electrical', 'Kategori', 'Jumlah Stok'], ',', '"', "\\");

        // Add data rows (sanitize fields: remove newlines and trim)
        foreach ($data['low_stock'] as $item) {
            $type_id = isset($item['type_id']) ? trim($item['type_id']) : '';
            $category = isset($item['category']) ? trim($item['category']) : '';
            $total_amount = isset($item['total_amount']) ? trim($item['total_amount']) : '';

            // Remove any CR/LF to avoid breaking CSV layout (replace with space)
            $type_id = str_replace(["\r", "\n"], ' ', $type_id);
            $category = str_replace(["\r", "\n"], ' ', $category);
            $total_amount = str_replace(["\r", "\n"], ' ', $total_amount);

            // Ensure fields are strings and let fputcsv handle quoting/escaping
            fputcsv($output, [(string)$type_id, (string)$category, (string)$total_amount], ',', '"', "\\");
        }

        // Close output stream
        fclose($output);
        exit;
    }
}

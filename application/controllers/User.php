<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * @package ElectricalSystem
 * @subpackage Controllers
 * @category User
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_URI $uri
 * @property CI_Form_validation $form_validation
 * @property CI_Pagination $pagination
 * @property User_model $User_model
 */
class User extends CI_Controller
{
    /**
     * Configuration array for all settings.
     *
     * @var array
     */
    private const CONFIG = [
        'pagination' => [
            'items_per_page' => 7
        ],
        'validation' => [
            'nik' => [
                'field' => 'nik',
                'label' => 'NIK',
                'rules' => 'required|numeric|exact_length[9]|is_unique[as_user.nik]',
                'errors' => [
                    'required'     => '%s harus diisi',
                    'numeric'      => '%s hanya menggunakan angka',
                    'exact_length' => '%s berjumlah 9 digit',
                    'is_unique'    => '%s tidak boleh sama',
                ],
            ],
            'name' => [
                'field' => 'name',
                'label' => 'Nama',
                'rules' => 'trim|required',
                'errors' => [
                    'required' => '%s harus diisi',
                ],
            ],
        ],
    ];

    /**
     * Class constructor.
     *
     * Loads models, libraries and helpers. Verifies authentication.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Check authentication
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }

        $this->load->model('User_model');
        $this->load->library(['form_validation', 'pagination']);

        // Reset session if controller changed
        if ($this->session->userdata('controller') !== 'user') {
            $this->session->set_userdata('controller', 'user');
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * List users with pagination and optional search. Renders via render_view().
     *
     * @return void
     */
    public function index(): void
    {
        // Handle possible Excel file upload like ASRS implementation
        $this->handleFileUpload();
        $this->handleSessionState();

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort'),
        ];

        $config = [
            'base_url'   => site_url('user/index'),
            'total_rows' => $this->User_model->countUser($sessionData['search'], $sessionData['filter']),
            'per_page'   => self::CONFIG['pagination']['items_per_page'],
        ];
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ? $this->uri->segment(3) : 0);
        $users = $this->User_model->getUser(
            self::CONFIG['pagination']['items_per_page'],
            $startData,
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );

        // Prepare view data
        $userCount = count($users);
        $data = [
            'title'         => 'Data Pengguna',
            'users'         => $users,
            'display'       => ($startData + 1) . ' - ' . ($startData + $userCount) . ' dari ' . $config['total_rows'],
            'sortKeyword'   => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'searchKeyword' => $sessionData['search'],
            'filterKeyword' => $sessionData['filter'],
            'hasFilters'    => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
            'pagination'    => $this->pagination->create_links(),
        ];

        render_view('user/index', $data);
    }

    /**
     * Show add form and process create.
     *
     * @return void
     */
    public function add(): void
    {
        $this->form_validation->set_rules(
            self::CONFIG['validation']['nik']['field'],
            self::CONFIG['validation']['nik']['label'],
            self::CONFIG['validation']['nik']['rules'],
            self::CONFIG['validation']['nik']['errors']
        );
        $this->form_validation->set_rules(
            self::CONFIG['validation']['name']['field'],
            self::CONFIG['validation']['name']['label'],
            self::CONFIG['validation']['name']['rules'],
            self::CONFIG['validation']['name']['errors']
        );

        if ($this->form_validation->run() === false) {
            $data = ['title' => 'Tambah Pengguna Air System', 'user' => null];
            render_view('user/add', $data);
            return;
        }

        $this->User_model->addUser();
        set_message(['success', 'Pengguna berhasil ditambahkan']);

        redirect('user');
    }

    /**
     * Show edit form and process update.
     *
     * @param int $nik
     * @return void
     */
    public function edit($nik = null): void
    {
        if ($nik === null) {
            show_404();
            return;
        }

        $user = $this->User_model->getByNik((int) $nik);
        if (!$user) {
            set_message(['danger', 'Pengguna tidak ditemukan']);
            redirect('user');
            return;
        }

        $this->form_validation->set_rules(
            self::CONFIG['validation']['name']['field'],
            self::CONFIG['validation']['name']['label'],
            self::CONFIG['validation']['name']['rules'],
            self::CONFIG['validation']['name']['errors']
        );

        if ($this->form_validation->run() === false) {
            $data = ['title' => 'Edit Pengguna Air System', 'user' => $user];
            render_view('user/edit', $data);
            return;
        }

        $this->User_model->editUser((int) $nik);
        set_message(['success', 'Pengguna berhasil diperbarui']);
        redirect('user');
    }

    /**
     * Delete user by NIK.
     *
     * @param int $nik
     * @return void
     */
    public function delete($nik = null): void
    {
        if ($nik === null) {
            show_404();
            return;
        }

        $this->User_model->deleteUser((int) $nik);
        set_message(['success', 'Pengguna berhasil dihapus']);

        redirect('user');
    }

    /**
     * Show dashboard with storage and report statistics.
     *
     * @return void
     */
    public function dashboard(): void
    {
        // Load models needed for dashboard statistics
        $this->load->model(['Storage_model', 'Electric_type_model', 'Electric_model', 'Report_model']);

        // Total locations
        $locations = $this->Storage_model->get_all_locations();
        $total_locations = is_array($locations) ? count($locations) : 0;

        // Total items across storage (sum of total_amount from overview)
        $overview = $this->Storage_model->get_storage_overview();
        $total_items = 0;
        if (is_array($overview)) {
            foreach ($overview as $row) {
                $total_items += isset($row['total_amount']) ? (int)$row['total_amount'] : 0;
            }
        }

        // Total electrical types
        $total_types = count($this->Electric_type_model->getAllTypes());

        // Recent transactions
        $recent_transactions = $this->Report_model->get_all_transactions(10, 0);

        // Low stock (threshold: 5) — allow override via GET ?threshold=
        $thresholdParam = $this->input->get('threshold', true);
        $threshold = ($thresholdParam !== null && is_numeric($thresholdParam)) ? (int)$thresholdParam : 5;
        $low_stock = array_filter(is_array($overview) ? $overview : [], function ($r) use ($threshold) {
            return isset($r['total_amount']) && (int)$r['total_amount'] <= $threshold;
        });

        // Daily transactions for last 7 days for chart
        $start = date('Y-m-d', strtotime('-6 days'));
        $end = date('Y-m-d');
        $dailyRows = $this->Report_model->get_daily_summary($start, $end);
        // map by date
        $dailyMap = [];
        foreach ($dailyRows as $dr) {
            $dailyMap[$dr['transaction_date']] = (int)$dr['total_transactions'];
        }
        $chart_labels = [];
        $chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $chart_labels[] = date('d M', strtotime($d));
            $chart_data[] = isset($dailyMap[$d]) ? $dailyMap[$d] : 0;
        }

        $data = [
            'title' => 'Dashboard',
            'total_locations' => $total_locations,
            'total_items' => $total_items,
            'total_types' => $total_types,
            'recent_transactions' => $recent_transactions,
            'low_stock' => $low_stock,
            'threshold' => $threshold,
            'chart_labels' => $chart_labels,
            'chart_data' => $chart_data,
        ];

        render_view('user/dashboard', $data);
    }

    ## Private Helper Methods

    /**
     * Handles updating session data for search, filter, and sort keywords.
     *
     * @return void
     */
    private function handleSessionState(): void
    {
        if ($this->input->post('find')) {
            $this->session->set_userdata('keyword', $this->input->post('keyword', true));
        }

        if ($this->input->post('filter-submit')) {
            $this->session->set_userdata('filter', []);
        }

        if ($this->input->post('sort-send')) {
            $this->session->set_userdata('sort', $this->input->post('sort-send', true));
        }

        if ($this->input->post('reset')) {
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * Downloads user data as Excel file.
     *
     * @return void
     */
    public function download(): void
    {
        $users = $this->User_model->getUser(1000, 0); // Get all users

        try {
            $this->generateExcelFile($users);
        } catch (Exception $e) {
            log_message('error', 'Excel download error: ' . $e->getMessage());
            set_message(['danger', 'Error creating file: ' . $e->getMessage()]);
            redirect('user');
        }
    }

    /**
     * Downloads Excel template for user upload.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $this->generateTemplateFile();
        } catch (Exception $e) {
            log_message('error', 'Template download error: ' . $e->getMessage());
            set_message(['danger', 'Error creating template: ' . $e->getMessage()]);
            redirect('user');
        }
    }

    /**
     * Handles Excel file upload and user import.
     *
     * @return void
     */
    // Upload handling is performed in index() via handleFileUpload() to match ASRS (no separate public upload endpoint)
    /**
     * Public wrapper for upload POSTs — delegates to handleFileUpload().
     * Keeps a single implementation while preventing 404 when a form posts to /user/upload.
     *
     * @return void
     */
    public function upload(): void
    {
        // Delegate to the central handler which expects a file in \\$_FILES['file']
        $this->handleFileUpload();
    }

    ## Private Helper Methods

    /**
     * Generates Excel file for download.
     *
     * @param array $users Array of user data
     * @return void
     */
    private function generateExcelFile(array $users): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'NIK');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Dibuat');
        $sheet->setCellValue('D1', 'Diperbarui');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A{$row}", $user['nik']);
            $sheet->setCellValue("B{$row}", $user['name']);
            $sheet->setCellValue("C{$row}", date('d M Y H:i:s', strtotime($user['created_at'])));
            $sheet->setCellValue("D{$row}", date('d M Y H:i:s', strtotime($user['updated_at'])));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Output file
        $filename = 'data_pengguna_air_system_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generates Excel template for upload.
     *
     * @return void
     */
    private function generateTemplateFile(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'NIK');
        $sheet->setCellValue('B1', 'Nama');

        // Add example data
        $sheet->setCellValue('A2', '123456789');
        $sheet->setCellValue('B2', 'John Doe');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ];
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'B') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Output file
        $filename = 'template_pengguna_air_system.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Process uploaded Excel file and insert valid user records (mirrors ASRS implementation).
     *
     * This method will run when a file is POSTed to the index route.
     *
     * @return void
     */
    private function handleFileUpload(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_FILES['file'])) {
            return;
        }

        if (
            $_FILES['file']['error'] !== UPLOAD_ERR_OK ||
            empty($_FILES['file']['tmp_name']) ||
            !is_uploaded_file($_FILES['file']['tmp_name'])
        ) {
            set_message(['danger', 'File upload tidak valid']);
            return;
        }

        $file = $_FILES['file']['tmp_name'];

        try {
            $spreadsheet = @IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            array_shift($data); // remove header row

            $skippedData = [];
            $insertData  = [];

            foreach ($data as $row) {
                // Validate required fields
                if (!$row['A']) {
                    $skippedData[] = "NIK tidak boleh kosong";
                    continue;
                }

                if (!$row['B']) {
                    $skippedData[] = "Nama tidak boleh kosong";
                    continue;
                }

                $rowNik = trim($row['A'] ?? '');
                $rowName = ucwords(strtolower(trim($row['B'] ?? '')));

                // Validate NIK format
                if (!ctype_digit($rowNik)) {
                    $skippedData[] = "NIK harus angka: {$rowNik}";
                    continue;
                }
                if (strlen($rowNik) !== 9) {
                    $skippedData[] = "NIK harus berjumlah 9 digit: {$rowNik}";
                    continue;
                }

                // Check if NIK already exists
                if ($this->User_model->isNikExists($rowNik)) {
                    $skippedData[] = "NIK sudah terdaftar: {$rowNik}";
                    continue;
                }

                $insertData[] = [
                    'nik'        => $rowNik,
                    'name'       => $rowName,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'editor'     => $this->session->userdata('user_data')['nik'],
                ];
            }

            $insertCount  = count($insertData);
            $skippedCount = count($skippedData);

            if ($insertCount > 0 && $skippedCount > 0) {
                $this->User_model->insertBatch($insertData);
                set_message([
                    'warning',
                    "{$insertCount} data berhasil ditambahkan.<br>{$skippedCount} data gagal ditambahkan.<br>" . implode('<br>', $skippedData)
                ]);
            } elseif ($skippedCount > 0) {
                set_message([
                    'danger',
                    "{$skippedCount} data gagal ditambahkan.<br>" . implode('<br>', $skippedData)
                ]);
            } elseif ($insertCount > 0) {
                $this->User_model->insertBatch($insertData);
                set_message(['success', "Data berhasil ditambahkan! ({$insertCount} data baru)"]);
            } else {
                set_message(['danger', 'Data kosong!']);
            }

            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
            redirect('user');
        } catch (Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            set_message(['danger', 'Terjadi kesalahan dalam membaca file Excel.']);
        }
    }

    /**
     * Processes uploaded Excel file and imports users.
     *
     * @param string $filePath Path to uploaded Excel file
     * @return array Result with success status and counts
     */
    private function processExcelFile(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $inserted = 0;
        $errorMessages = [];
        $userData = [];

        // Skip header row, start from row 2
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNumber = $i + 1; // Add 1 to account for header row

            // Skip empty rows
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $nik = $row[0] ?? '';
            $name = $row[1] ?? '';

            // Validate required fields
            if (empty($nik)) {
                $errorMessages[] = "Baris {$rowNumber}: NIK tidak boleh kosong";
                continue;
            }

            if (empty($name)) {
                $errorMessages[] = "Baris {$rowNumber}: Nama tidak boleh kosong";
                continue;
            }

            // Validate NIK format
            if (!is_numeric($nik)) {
                $errorMessages[] = "Baris {$rowNumber}: NIK harus berupa angka: {$nik}";
                continue;
            }

            if (strlen($nik) !== 9) {
                $errorMessages[] = "Baris {$rowNumber}: NIK harus berjumlah 9 digit: {$nik}";
                continue;
            }

            // Check if NIK already exists
            if ($this->User_model->isNikExists($nik)) {
                $errorMessages[] = "Baris {$rowNumber}: NIK sudah terdaftar: {$nik}";
                continue;
            }

            $userData[] = [
                'nik' => $nik,
                'name' => ucwords(strtolower(trim($name))),
                'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                'editor' => $this->session->userdata('user_data')['nik']
            ];
            $inserted++;
        }

        // Batch insert
        if (!empty($userData)) {
            $this->User_model->insertBatch($userData);
        }

        return [
            'success' => count($errorMessages) === 0,
            'inserted' => $inserted,
            'errors' => count($errorMessages),
            'errorMessages' => $errorMessages
        ];
    }
}

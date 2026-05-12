<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Electric Controller
 *
 * Handles operations related to electric data, including CRUD operations,
 * file uploads, and Excel file generation.
 *
 * @package ElectricalSystem
 * @subpackage Controllers
 * @category Electric
 * @version 1.0.0
 */
class Electric extends CI_Controller
{
    /**
     * Configuration constants for the Electric controller.
     */
    private const CONFIG = [
        'pagination' => [
            'items_per_page' => 7
        ],
    ];

    /**
     * Constructor
     *
     * Initializes required models, libraries, and session data.
     */
    public function __construct()
    {
        parent::__construct();

        // Redirect to homepage if user is not logged in
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }

        // Load required models and libraries
        $this->load->model('Electric_model');
        $this->load->model('Electric_type_model');
        $this->load->model('Storage_model'); // PASTI KAN INI ADA
        $this->load->model('Location_model'); // Master lokasi
        $this->load->library(['form_validation', 'pagination']);
        $this->load->helper('common');

        // Set default session state for the controller
        if ($this->session->userdata('controller') !== 'electric') {
            $this->session->set_userdata('controller', 'electric');
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * Displays the list of electric data.
     *
     * Handles filtering, sorting, and pagination.
     *
     * @return void
     */
    public function index(): void
    {
        $this->handleFileUpload();
        $clearType = $this->input->get('clear_type', true);
        if (!empty($clearType)) {
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
            redirect('electric');
        }
        $getType = $this->input->get('type', true);
        if (!empty($getType)) {
            $existingTypes = $this->Electric_model->getElectricFilter('type', null, null);
            $existingNames = $this->Electric_model->getElectricFilter('nama', null, null);
            $normalized = strtoupper(trim($getType));
            $typeMatch = in_array($normalized, array_map('strtoupper', $existingTypes));
            $nameMatch = in_array($normalized, array_map('strtoupper', $existingNames));
            if ($typeMatch) {
                $this->session->set_userdata('filter', ['type' => [$normalized]]);
            } elseif ($nameMatch) {
                $this->session->set_userdata('filter', ['nama' => [$normalized]]);
            } else {
                $this->session->unset_userdata('filter');
            }
        }
        $this->handleSessionState();
        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort'),
        ];
        $nameOptions = $this->Electric_model->getElectricFilter('nama', $sessionData['search'], $sessionData['filter']);
        $typeOptions = $this->Electric_model->getElectricFilter('type', $sessionData['search'], $sessionData['filter']);
        $totalRows = $this->Electric_model->countElectric($sessionData['search'], $sessionData['filter']);
        $config = [
            'base_url'   => site_url('electric/index'),
            'total_rows' => $totalRows,
            'per_page'   => self::CONFIG['pagination']['items_per_page'],
        ];
        $this->pagination->initialize($config);
        $startData = (int) ($this->uri->segment(3) ?: 0);
        $electrics = $this->Electric_model->getElectric(
            self::CONFIG['pagination']['items_per_page'],
            $startData,
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );
        if (empty($electrics) && (!empty($sessionData['search']) || !empty($sessionData['filter']))) {
            $totalRowsWithoutFilter = $this->Electric_model->countElectric(null, null);
            if ($totalRowsWithoutFilter > 0) {
                $this->session->unset_userdata(['keyword', 'filter']);
                redirect('electric');
            }
        }
        
        // Retrieve location data for modal from master lokasi
        $locations = $this->Location_model->get_all();

        $data = [
            'title' => 'Data Electric',
            'electrics' => $electrics,
            'pagination' => ['links' => $this->pagination->create_links()],
            'total_rows' => $totalRows,
            'searchKeyword' => $sessionData['search'],
            'sortKeyword' => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'filterKeyword' => $sessionData['filter'],
            'hasFilters' => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
            'name_options' => $nameOptions,
            'type_options' => $typeOptions,
            'locations'    => $locations,
            'start' => $startData,
        ];
        render_view('electric/index', $data);
    }

    /**
     * Displays the list of electric types.
     *
     * Fetches all available electric types and prepares data for the view.
     *
     * @return void
     */
    public function type(): void
    {
        $allTypes = $this->Electric_type_model->getAllTypes();
        $typeOptions = [];
        $imgPath = FCPATH . 'assets/img/electric_types/';
        $defaultUrl = base_url('assets/img/electric-default.png');
        
        foreach ($allTypes as $row) {
            $imageUrl = $defaultUrl;
            if (!empty($row['image']) && is_file($imgPath . $row['image'])) {
                $imageUrl = base_url('assets/img/electric_types/' . $row['image']);
            }

            $typeOptions[] = [
                'id'        => $row['id'],
                'type'      => $row['type'],
                'image_url' => $imageUrl,
            ];
        }

        $data = [
            'title' => 'Pilih Jenis Electrical',
            'types' => $typeOptions,
        ];

        render_view('electric/type', $data);
    }

    /**
     * Adds a new electric entry.
     *
     * Handles form submission, validation, and file uploads.
     *
     * @return void
     */
    public function add(): void
    {
        $typeIdFromGet = $this->input->get('type_id', true);
        $typeNameFromGet = $this->input->get('type', true);
        $typeData = null;

        // Prefer numeric type_id if provided
        if ($typeIdFromGet) {
            $typeData = $this->Electric_type_model->getById((int)$typeIdFromGet);
        }

        // If no type_id was provided or not found, allow passing the type name as fallback
        if (!$typeData && !empty($typeNameFromGet)) {
            // Electric_type_model::getByType expects the type string (it uppercases internally)
            $typeData = $this->Electric_type_model->getByType($typeNameFromGet);
        }

        if ($this->input->method() === 'post') {
            $this->setValidationRules();
            // Tambahkan validasi lokasi
            $this->form_validation->set_rules('location', 'Lokasi', 'required');

            if ($this->form_validation->run()) {
                $typeId = (int)$this->input->post('type_id', true);
                $categoryData = $this->Electric_type_model->getById($typeId);
                if (!$categoryData) {
                    set_message(['danger', 'Kategori Jenis Electrical tidak valid!']);
                    redirect('electric/add');
                    return;
                }
                $imageFilename = null;
                if (!empty($_FILES['image']['name'])) {
                    $uploadPath = FCPATH . 'assets/img/electric/';
                    if (!is_dir($uploadPath)) {
                        if (!mkdir($uploadPath, 0755, true)) {
                            set_message(['danger', 'Tidak dapat membuat direktori upload']);
                            redirect('electric/add?type_id=' . $typeId);
                            return;
                        }
                    }
                    
                    $cleanName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $this->input->post('nama'));
                    $fileName = strtolower($cleanName) . '-' . time();
                    
                    $config = [
                        'upload_path' => $uploadPath,
                        'allowed_types' => 'jpg|jpeg|png|gif',
                        'max_size' => 2048, // 2MB
                        'file_name' => $fileName,
                        'overwrite' => FALSE,
                        'remove_spaces' => TRUE
                    ];
                    
                    $this->load->library('upload', $config);
                    
                    if ($this->upload->do_upload('image')) {
                        $uploadData = $this->upload->data();
                        $imageFilename = $uploadData['file_name'];
                    } else {
                        $error = $this->upload->display_errors('', '');
                        set_message(['danger', 'Upload gambar gagal: ' . $error]);
                        redirect('electric/add?type_id=' . $typeId);
                        return;
                    }
                }
                $dataToSave = [
                    'nama'      => $this->input->post('nama', true),
                    'brand'     => $this->input->post('brand', true),
                    'type_id'   => $typeId,
                    'type'      => $this->input->post('type', true),
                    'voltage'   => $this->input->post('voltage', true) ?: null,
                    'voltage_unit' => $this->input->post('voltage_unit', true),
                    'ampere'    => $this->input->post('ampere', true) ?: null,
                    'daya'      => $this->input->post('daya', true) ?: null,
                    'daya_unit' => $this->input->post('daya_unit', true),
                    'location'  => $this->input->post('location', true),
                    'image'     => $imageFilename,
                    'editor'    => $this->session->userdata('user_data')['nama'] ?? 'SYSTEM',
                ];
                $this->Electric_model->addElectric($dataToSave);
                set_message(['success', 'Data electric berhasil ditambahkan!']);
                redirect('electric');
            } else {
                $postedTypeId = $this->input->post('type_id', true);
                if ($postedTypeId) {
                    $typeData = $this->Electric_type_model->getById((int)$postedTypeId);
                }
            }
        }

        // If no specific type has been provided, provide list of types to the form
        if (!$typeData) {
            $data['types'] = $this->Electric_type_model->getAllTypes();
        }

        // Ambil data lokasi untuk dropdown
        $data['locations'] = $this->Location_model->get_all();
        $data['title'] = 'Tambah Electric';
        $data['typeData'] = $typeData;
        render_view('electric/add', $data);
    }

    /**
     * Edits an existing electric entry.
     *
     * Handles form submission, validation, and file uploads.
     *
     * @param string $electricId The ID of the electric entry to edit.
     * @return void
     */
    public function edit(string $electricId): void
    {
        $electricId = urldecode($electricId);
        $electric = $this->Electric_model->getById($electricId);
        if (!$electric) show_404();

        $typeData = $this->Electric_type_model->getById((int)$electric['type_id']);

        if ($this->input->method() === 'post') {
            $this->setValidationRules();

            if ($this->form_validation->run()) {
                $typeId = (int)$this->input->post('type_id', true);
                $categoryData = $this->Electric_type_model->getById($typeId);
                if (!$categoryData) {
                    set_message(['danger', 'Kategori Jenis Electrical tidak valid!']);
                    redirect('electric/edit/' . urlencode($electricId));
                    return;
                }
                
                $imageFilename = $electric['image'];
                
                if ($this->input->post('remove_image') == '1') {
                    if ($electric['image']) {
                        $currentImagePath = FCPATH . 'assets/img/electric/' . $electric['image'];
                        if (file_exists($currentImagePath)) {
                            @unlink($currentImagePath);
                        }
                    }
                    $imageFilename = null;
                }
                
                if (!empty($_FILES['image']['name'])) {
                    $uploadPath = FCPATH . 'assets/img/electric/';
                    if (!is_dir($uploadPath)) {
                        if (!mkdir($uploadPath, 0755, true)) {
                            set_message(['danger', 'Tidak dapat membuat direktori upload']);
                            redirect('electric/edit/' . urlencode($electricId));
                            return;
                        }
                    }
                    
                    $cleanName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $this->input->post('nama'));
                    $fileName = strtolower($cleanName) . '-' . time();
                    
                    $config = [
                        'upload_path' => $uploadPath,
                        'allowed_types' => 'jpg|jpeg|png|gif',
                        'max_size' => 2048, // 2MB
                        'file_name' => $fileName,
                        'overwrite' => FALSE,
                        'remove_spaces' => TRUE
                    ];
                    
                    $this->load->library('upload');
                    $this->upload->initialize($config);
                    
                    if ($this->upload->do_upload('image')) {
                        if ($electric['image']) {
                            $oldImagePath = FCPATH . 'assets/img/electric/' . $electric['image'];
                            if (file_exists($oldImagePath)) {
                                @unlink($oldImagePath);
                            }
                        }
                        
                        $uploadData = $this->upload->data();
                        $imageFilename = $uploadData['file_name'];
                    } else {
                        $error = $this->upload->display_errors('', '');
                        set_message(['danger', 'Upload gambar gagal: ' . $error]);
                        redirect('electric/edit/' . urlencode($electricId));
                        return;
                    }
                }
                
                $dataToUpdate = [
                    'nama'      => $this->input->post('nama', true),
                    'brand'     => $this->input->post('brand', true),
                    'type_id'   => $typeId,
                    'type'      => $this->input->post('type', true),
                    'voltage'   => $this->input->post('voltage', true) ?: null,
                    'voltage_unit' => $this->input->post('voltage_unit', true),
                    'ampere'    => $this->input->post('ampere', true) ?: null,
                    'daya'      => $this->input->post('daya', true) ?: null,
                    'daya_unit' => $this->input->post('daya_unit', true),
                    'location'  => $this->input->post('location', true),
                    'image'     => $imageFilename,
                    'editor'    => $this->session->userdata('user_data')['nama'] ?? 'SYSTEM',
                ];
                
                $newElectricId = $this->Electric_model->generateElectricId(
                    $dataToUpdate['nama'],
                    $dataToUpdate['type'],
                    $dataToUpdate['voltage'],
                    $dataToUpdate['ampere']
                );
                $dataToUpdate['electric_id'] = $newElectricId;
                
                $result = $this->Electric_model->editElectric($electricId, $dataToUpdate);
                if ($result) {
                    set_message(['success', 'Data electric berhasil diperbarui!']);
                    redirect('electric');
                } else {
                    set_message(['danger', 'Gagal memperbarui data electric!']);
                    redirect('electric/edit/' . urlencode($electricId));
                }
            } else {
                $validationErrors = validation_errors();
                if (!empty($validationErrors)) {
                    set_message(['danger', 'Validasi gagal: ' . strip_tags($validationErrors)]);
                }
                
                $postedTypeId = $this->input->post('type_id', true);
                if ($postedTypeId) {
                    $typeData = $this->Electric_type_model->getById((int)$postedTypeId);
                }
            }
        }
        
        // Ambil data lokasi agar dropdown muncul saat edit
        $data['locations'] = $this->Location_model->get_all();
        $data['electric'] = $electric;
        $data['typeData'] = $typeData;
        $data['title'] = 'Edit Electric';
        render_view('electric/edit', $data);
    }

    /**
     * Deletes an electric entry.
     *
     * Removes the specified electric entry from the database.
     *
     * @param string $electricId The ID of the electric entry to delete.
     * @return void
     */
    public function delete(string $electricId): void
    {
        $electricId = urldecode($electricId);
        $electric = $this->Electric_model->getById($electricId);
        if (!$electric) {
            set_message(['danger', 'Data electric tidak ditemukan!']);
        } else {
            $this->Electric_model->deleteElectric($electricId);
            set_message(['success', 'Data electric berhasil dihapus!']);
        }
        redirect('electric');
    }

    /**
     * Downloads electric data as an Excel file.
     *
     * Generates and serves an Excel file containing all electric data.
     *
     * @return void
     */
    public function download(): void
    {
        try {
            $electrics = $this->Electric_model->getAllElectrics();
            $this->generateExcelFile($electrics);
        } catch (Exception $e) {
            log_message('error', 'Excel download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('electric');
        }
    }

    /**
     * Downloads a template for electric data.
     *
     * Generates and serves an Excel template file for electric data.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Nama');
            $sheet->setCellValue('B1', 'Type');
            $sheet->setCellValue('C1', 'Voltage');
            $sheet->setCellValue('D1', 'Ampere');
            $sheet->setCellValue('E1', 'Daya');
            $filename = 'Template Data Electric.xlsx';
            $this->outputExcelFile($spreadsheet, $filename);
        } catch (Exception $e) {
            log_message('error', 'Template download error: ' . $e->getMessage());
            show_error('Error generating template file: ' . $e->getMessage());
        }
    }

    /**
     * Handles file uploads for electric data.
     *
     * Processes uploaded Excel files and inserts valid data into the database.
     *
     * @return void
     */
    public function upload(): void
    {
        $this->handleFileUpload();
    }

    /**
     * Generates an Excel file from electric data.
     *
     * @param array $electrics The electric data to include in the file.
     * @return void
     */
    private function generateExcelFile(array $electrics): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Electric ID')->setCellValue('B1', 'Nama')->setCellValue('C1', 'Type')->setCellValue('D1', 'Voltage')->setCellValue('E1', 'Ampere')->setCellValue('F1', 'Daya')->setCellValue('G1', 'Created At')->setCellValue('H1', 'Updated At')->setCellValue('I1', 'Editor');
        $headerStyle = ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']]];
        $sheet->getStyle("A1:{$sheet->getHighestColumn()}1")->applyFromArray($headerStyle);
        $row = 2;
        foreach ($electrics as $electric) {
            $sheet->setCellValue("A{$row}", $electric['electric_id'])->setCellValue("B{$row}", $electric['nama'])->setCellValue("C{$row}", $electric['type'])->setCellValue("D{$row}", $electric['voltage'])->setCellValue("E{$row}", $electric['ampere'])->setCellValue("F{$row}", $electric['daya'])->setCellValue("G{$row}", $electric['created_at'])->setCellValue("H{$row}", $electric['updated_at'])->setCellValue("I{$row}", $electric['editor']);
            $row++;
        }
        $sheet->setAutoFilter('A1:I1');
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $filename = 'Data Electric.xlsx';
        $this->outputExcelFile($spreadsheet, $filename);
    }

    /**
     * Outputs an Excel file to the browser.
     *
     * @param Spreadsheet $spreadsheet The spreadsheet to output.
     * @param string $filename The name of the file to output.
     * @return void
     */
    private function outputExcelFile(Spreadsheet $spreadsheet, string $filename): void
    {
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Handles file upload for electric data.
     *
     * Processes uploaded Excel files and inserts valid data into the database.
     *
     * @return void
     */
    private function handleFileUpload(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_FILES['file'])) return;
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK || empty($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            set_message(['danger', 'File upload tidak valid']);
            return;
        }
        $file = $_FILES['file']['tmp_name'];
        try {
            $spreadsheet = @IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            array_shift($data);
            $skippedData = [];
            $insertData  = [];
            foreach ($data as $row) {
                if ((!isset($row['A']) || $row['A'] === null || $row['A'] === '') && (!isset($row['B']) || $row['B'] === null || $row['B'] === '') && (!isset($row['C']) || $row['C'] === null || $row['C'] === '') && (!isset($row['D']) || $row['D'] === null || $row['D'] === '') && (!isset($row['E']) || $row['E'] === null || $row['E'] === '')) continue;
                if (empty($row['A'])) { $skippedData[] = "Nama tidak boleh kosong"; continue; }
                if (empty($row['B'])) { $skippedData[] = "Type tidak boleh kosong"; continue; }
                if (empty($row['C'])) { $skippedData[] = "Voltage tidak boleh kosong"; continue; }
                $nama = $row['A'] ?? ''; $type = $row['B'] ?? ''; $voltage = $row['C'] ?? ''; $ampere = $row['D'] ?? ''; $daya = $row['E'] ?? '';
                if (strlen($nama) > 50) { $skippedData[] = "Nama maksimal 50 karakter: {$nama}"; continue; }
                if (strlen($type) > 15) { $skippedData[] = "Type maksimal 15 karakter: {$type}"; continue; }
                if (!is_numeric($voltage) || $voltage <= 0) { $skippedData[] = "Voltage harus berupa angka positif: {$voltage}"; continue; }
                if ($ampere !== '' && (!is_numeric($ampere) || $ampere < 0)) { $skippedData[] = "Ampere harus kosong atau angka (>=0): {$ampere}"; continue; }
                if ($daya !== '' && (!is_numeric($daya) || $daya < 0)) { $skippedData[] = "Daya harus kosong atau angka (>=0): {$daya}"; continue; }
                $electricId = 'elc-' . preg_replace('/\s+/', '-', strtolower(trim($nama))) . '-' . preg_replace('/\s+/', '-', strtolower(trim($type)));
                if ($this->Electric_model->isElectricIdExists($electricId)) {
                    $skippedData[] = "Kombinasi electric sudah terdaftar (Nama: {$nama}, Type: {$type})";
                    continue;
                }
                $insertData[] = ['electric_id' => $electricId, 'nama' => $nama, 'type' => strtoupper(trim($type)), 'voltage' => $voltage, 'ampere' => ($ampere === '' ? null : (float)$ampere), 'daya' => ($daya === '' ? null : (float)$daya), 'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')), 'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')), 'editor' => $this->session->userdata('user_data')['nik']];
            }
            $insertCount = 0; $skippedCount = count($skippedData); $validInsertData = [];
            foreach ($insertData as $row) {
                $type = $row['type'] ?? ''; $typeObj = $this->Electric_type_model->getByType($type);
                if (empty($type) || !$typeObj) {
                    $skippedCount++; $skippedData[] = "Type tidak tersedia atau tidak terdaftar: {$type}"; continue;
                }
                $row['type_id'] = $typeObj['id']; $validInsertData[] = $row;
            }
            $insertCount = count($validInsertData);
            if ($insertCount > 0) $this->Electric_model->insertBatch($validInsertData);
            if ($insertCount > 0 && $skippedCount > 0) set_message(['warning', "{$insertCount} data berhasil ditambahkan.<br>{$skippedCount} data gagal.<br>" . implode('<br>', $skippedData)]);
            elseif ($skippedCount > 0) set_message(['danger', "{$skippedCount} data gagal.<br>" . implode('<br>', $skippedData)]);
            elseif ($insertCount > 0) set_message(['success', "Data berhasil ditambahkan! ({$insertCount} data baru)"]);
            else set_message(['danger', 'Data kosong!']);
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
            redirect('electric');
        } catch (Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            set_message(['danger', 'Terjadi kesalahan dalam membaca file Excel.']);
        }
    }

    /**
     * Handles session state for filtering, sorting, and searching.
     *
     * Updates session data based on user input.
     *
     * @return void
     */
    private function handleSessionState(): void
    {
        if ($this->input->post('find')) {
            $this->session->set_userdata('keyword', $this->input->post('keyword', true));
        }
        if ($this->input->post('sort-send')) {
            $sortRaw = $this->input->post('sort-send', true);
            if (is_string($sortRaw) && preg_match('/^[a-z0-9_\-]+-(ASC|DESC)$/i', $sortRaw)) {
                [$field, $direction] = explode('-', $sortRaw, 2);
                $this->session->set_userdata('sort', $field . '-' . strtoupper($direction));
            } elseif ($sortRaw === '') {
                $this->session->unset_userdata('sort');
            }
        }
        if ($this->input->post('reset')) {
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
        if ($this->input->post('filter')) {
            $filterRaw = $this->input->post('filter', true);
            if (is_string($filterRaw) && ($json = json_decode($filterRaw, true)) !== null) {
                $this->session->set_userdata('filter', $json);
            } elseif (is_array($filterRaw)) {
                $this->session->set_userdata('filter', $filterRaw);
            }
        }
    }

    /**
     * Sets validation rules for electric data forms.
     *
     * @return void
     */
    private function setValidationRules(): void
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('brand', 'Brand', 'trim|max_length[50]');
        $this->form_validation->set_rules('type_id', 'Kategori', 'required|integer');
        $this->form_validation->set_rules('type', 'Type/Model', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('voltage', 'Voltage', 'callback_validate_optional_positive');
        $this->form_validation->set_rules('ampere', 'Ampere', 'callback_validate_optional_positive');
        $this->form_validation->set_rules('daya', 'Daya', 'callback_validate_optional_positive');
    }

    /**
     * Validates optional positive numeric fields.
     *
     * @param mixed $value The value to validate.
     * @return bool
     */
    public function validate_optional_positive($value)
    {
        if ($value === null || $value === '') return TRUE;
        if (is_numeric($value) && $value >= 0) return TRUE;
        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)\s*$/', $value, $matches)) {
            $min = floatval($matches[1]);
            $max = floatval($matches[2]);
            if ($min >= 0 && $max >= 0 && $min < $max) return TRUE;
        }
        $this->form_validation->set_message('validate_optional_positive', '%s harus kosong, berupa angka positif, atau rentang dua angka positif (misal: 4-30)');
        return FALSE;
    }

    /**
     * Resets session data for filtering, sorting, and searching.
     *
     * @return void
     */
    public function reset_session(): void
    {
        $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        redirect('electric');
    }
}
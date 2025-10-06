<?php
defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Electric Type Controller
 * 
 * Handles CRUD operations for electrical types in the system.
 * Provides functionality to manage electrical type categories with image upload support.
 * 
 * @package ElectricalSystem
 * @subpackage Controllers
 * @category User
 * @author Apparel One Indonesia
 * @version    1.0.0
 */
class Electric_type extends CI_Controller
{
    /**
     * Class constructor
     * 
     * Initializes the controller, checks user authentication,
     * and loads required models, libraries, and helpers.
     */
    public function __construct()
    {
        parent::__construct();
        
        // Check if user is authenticated
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }

        // Load required dependencies
        $this->load->model('Electric_type_model');
        $this->load->library(['form_validation', 'upload']);
        $this->load->helper(['common']);
    }

    /**
     * Display the main page for managing all electrical types
     * 
     * Retrieves all electrical types with their usage statistics
     * and displays them in a management interface.
     * 
     * @return void
     */
    public function index(): void
    {
        $rows = $this->Electric_type_model->getAllTypes();
        $types = [];
        
        foreach ($rows as $row) {
            $types[] = [
                'id'          => (int) $row['id'],
                'type'        => (string) ($row['type'] ?? ''),
                'image'       => $row['image'] ?? null,
                'usage_count' => (int) $row['usage_count'],
                'is_in_use'   => $row['usage_count'] > 0
            ];
        }

        $data = [
            'title' => 'Manage Electrical Types',
            'types' => $types,
        ];

        render_view('electric/manage_types', $data);
    }

    /**
     * Handle adding new electrical type
     * 
     * Processes form submission for creating new electrical types,
     * including validation, image upload, and database insertion.
     * 
     * @return void
     */
    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $this->_processTypeForm();
        }

        $data = [
            'title'       => 'Add Electrical Type',
            'type'        => null,
            'form_action' => site_url('electric_type/add')
        ];
        
        render_view('electric/type_form', $data);
    }

    /**
     * Handle editing existing electrical type
     * 
     * Processes form submission for updating electrical types,
     * including validation, image upload, and database update.
     * 
     * @param int $id The electrical type ID to edit
     * @return void
     */
    public function edit(int $id = 0): void
    {
        $row = $this->Electric_type_model->getById($id);
        
        if (!$row) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $this->_processTypeForm($id, $row);
        }

        $data = [
            'title'       => 'Edit Electrical Type',
            'type'        => $row,
            'form_action' => site_url('electric_type/edit/' . $id)
        ];
        
        render_view('electric/type_form', $data);
    }

    /**
     * Delete electrical type
     * 
     * Removes an electrical type from the system after ensuring
     * it's not currently being used by any electrical items.
     * 
     * @param int $id The electrical type ID to delete
     * @return void
     */
    public function delete(int $id = 0): void
    {
        $row = $this->Electric_type_model->getById($id);
        
        if (!$row) {
            set_message(['danger', 'Electrical type not found']);
            redirect('electric_type');
        }

        // Check if the type is currently in use
        if ($row['usage_count'] > 0) {
            set_message([
                'danger', 
                'Electrical type "' . htmlspecialchars($row['type']) . '" is currently in use and cannot be deleted'
            ]);
            redirect('electric_type');
        }

        // Delete associated image file if exists
        if (!empty($row['image'])) {
            $this->_deleteImageFile($row['image']);
        }
        
        $this->Electric_type_model->deleteType($id);
        set_message([
            'success', 
            'Electrical type "' . htmlspecialchars($row['type']) . '" has been successfully deleted'
        ]);
        redirect('electric_type');
    }

    /**
     * Process form submission for both add and edit operations
     * 
     * Handles validation, image upload, and database operations
     * for electrical type forms.
     * 
     * @param int|null $id The electrical type ID (null for new types)
     * @param array|null $existingData Existing type data (for edit operations)
     * @return void
     */
    private function _processTypeForm(?int $id = null, ?array $existingData = null): void
    {
        $type = trim($this->input->post('type', true));
        $isEdit = ($id !== null);
        $redirectUrl = $isEdit ? 'electric_type/edit/' . $id : 'electric_type/add';

        // Validate type name
        if (empty($type)) {
            set_message(['danger', 'Electrical type name is required']);
            redirect($redirectUrl);
        }
        
        if (strlen($type) > 50) {
            set_message(['danger', 'Electrical type name cannot exceed 50 characters']);
            redirect($redirectUrl);
        }
        
        // Check for duplicate type names
        if ($this->Electric_type_model->isTypeExists($type, $id)) {
            set_message(['danger', 'Electrical type "' . htmlspecialchars($type) . '" already exists']);
            redirect($redirectUrl);
        }

        // Handle image upload
        $imageFilename = $isEdit ? $existingData['image'] : null;
        
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->_handleImageUpload($type);
            
            if ($uploadResult['success']) {
                // Delete old image if editing and new upload successful
                if ($isEdit && !empty($existingData['image'])) {
                    $this->_deleteImageFile($existingData['image']);
                }
                $imageFilename = $uploadResult['filename'];
            }
            // Note: Upload errors are handled within _handleImageUpload method
        }

        // Save to database
        if ($isEdit) {
            $this->Electric_type_model->editType($id, $type, $imageFilename);
            set_message(['success', 'Electrical type "' . htmlspecialchars($type) . '" has been successfully updated']);
        } else {
            $this->Electric_type_model->addType($type, $imageFilename);
            set_message(['success', 'Electrical type "' . htmlspecialchars($type) . '" has been successfully added']);
        }

        redirect('electric_type');
    }

    /**
     * Handle image file upload
     * 
     * Processes image upload with validation and proper file naming.
     * 
     * @param string $typeName The electrical type name for filename generation
     * @return array Upload result with success status and filename
     */
    private function _handleImageUpload(string $typeName): array
    {
        $uploadPath = FCPATH . 'assets/img/electric_types/';
        
        // Ensure upload directory exists
        if (!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        // Configure upload settings
        $config = [
            'upload_path'   => $uploadPath,
            'allowed_types' => 'jpg|jpeg|png|gif|avif|webp',
            'max_size'      => 2048, // 2MB
            'file_name'     => $this->_generateImageFilename($typeName),
            'encrypt_name'  => false,
            'remove_spaces' => true
        ];

        $this->upload->initialize($config);

        if ($this->upload->do_upload('image')) {
            return [
                'success'  => true,
                'filename' => $this->upload->data('file_name')
            ];
        }

        // Handle upload errors
        $error = $this->upload->display_errors('', '');
        set_message(['warning', 'Image upload failed: ' . $error]);
        
        return [
            'success'  => false,
            'filename' => null
        ];
    }

    /**
     * Generate safe filename for uploaded images
     * 
     * Creates a sanitized filename based on the electrical type name
     * with timestamp to avoid conflicts.
     * 
     * @param string $typeName The electrical type name
     * @return string Generated filename (without extension)
     */
    private function _generateImageFilename(string $typeName): string
    {
        $sanitized = preg_replace('/[^a-z0-9\-]/', '-', strtolower($typeName));
        $sanitized = preg_replace('/-+/', '-', $sanitized); // Remove multiple dashes
        $sanitized = trim($sanitized, '-'); // Remove leading/trailing dashes
        
        return $sanitized . '-' . time();
    }

    /**
     * Delete image file from filesystem
     * 
     * Safely removes an image file from the electric types directory.
     * 
     * @param string $filename The filename to delete
     * @return bool True if deletion was successful or file doesn't exist
     */
    private function _deleteImageFile(string $filename): bool
    {
        if (empty($filename)) {
            return true;
        }

        $filePath = FCPATH . 'assets/img/electric_types/' . $filename;
        
        if (file_exists($filePath)) {
            return @unlink($filePath);
        }
        
        return true; // File doesn't exist, consider it "deleted"
    }
}
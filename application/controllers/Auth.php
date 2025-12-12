<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authentication Controller
 *
 * Handles user authentication processes such as login and logout.
 *
 * @package ElectricalSystem
 * @subpackage Controllers
 * @category User
 * @author Apparel One Indonesia
 * @version 1.0.0
 */
class Auth extends CI_Controller
{
    /**
     * Constructor
     *
     * Loads the required model for user authentication.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    /**
     * Default method for the Auth controller
     *
     * Displays the login page and handles login form submission.
     * Redirects authenticated users to the dashboard.
     *
     * @return void
     */
    public function index(): void
    {
        // Check if the user is already logged in
        if ($this->session->userdata('user_data')) {
            redirect('user/dashboard');
        }

        // Load form validation library
        $this->load->library('form_validation');

        // Define validation rules for NIK
        $config = [
            [
                'field' => 'nik',
                'label' => 'NIK',
                'rules' => 'required|numeric|exact_length[9]',
                'errors' => [
                    'required' => 'Please enter %s',
                    'numeric' => '%s is not valid',
                    'exact_length' => '%s is not valid',
                ],
            ],
        ];

        $this->form_validation->set_rules($config);

        // Check if the form validation passes
        if (!$this->form_validation->run()) {
            // Load the login view if validation fails
            $this->load->view('auth/index');
        } else {
            // Proceed to login if validation succeeds
            $this->_login();
        }
    }

    /**
     * Handles the login process
     *
     * Validates the NIK against the database and sets session data for the user.
     * Redirects to the dashboard upon successful login.
     *
     * @return void
     */
    private function _login(): void
    {
        // Retrieve NIK from the POST request
        $nik = $this->input->post('nik', true);

        // Fetch user details by NIK
        $userDetail = $this->user_model->getByNik((int) $nik);
        if (!$userDetail) {
            // Set error message if NIK is not registered
            set_message(['danger', 'NIK is not registered']);
            redirect(base_url());
            return;
        }

        // Prepare user data for the session
        $userData = [
            'nik' => $userDetail['nik'],
            'name' => $userDetail['name'],
        ];

        $data = [
            'user_data' => $userData,
            'machine' => null, // Machine data is not used in this system
        ];

        // Set session data
        $this->session->set_userdata($data);

        // Redirect to the user dashboard
        redirect('user/dashboard');
    }

    /**
     * Handles the logout process
     *
     * Destroys the user session and redirects to the homepage.
     *
     * @return void
     */
    public function logout(): void
    {
        // Destroy the session
        session_destroy();

        // Set success message for logout
        set_message(['success', 'You have successfully logged out']);

        // Redirect to the homepage
        redirect(base_url());
    }
}

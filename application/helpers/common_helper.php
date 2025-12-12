<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Common helper functions shared across controllers.
 *
 * This file contains small, re-usable helpers that rely on CodeIgniter's
 * global instance (get_instance()). Keep helpers focused and side-effect
 * aware — several helpers will modify session state and may redirect.
 *
 * Provided helpers:
 * - render_view(string $view, array $data = []) : render header, view, footer
 * - set_message(array $message)                 : set flash message under 'action'
 * - handle_session_state(string $redirect, array $filterMap = [])
 *                                               : generic search/filter/sort/reset handler
 * - get_rules(array $validationConfig, array $fields)
 *                                               : extract validation rules from a config map
 */

if (!function_exists('render_view')) {
    /**
     * Render a view with the standard header and footer.
     *
     * Renders a header, the requested view and a footer. This helper is a
     * small convenience wrapper and has the side-effect of sending output.
     *
     * @param string $view Path to the view (for example: 'user/index').
     * @param array  $data Data passed to the header, view and footer.
     *
     * @return void
     */
    function render_view(string $view, array $data = []): void
    {
        $ci =& get_instance();
        $ci->load->view('templates/header', $data);
        $ci->load->view($view, $data);
        $ci->load->view('templates/footer');
    }
}

if (!function_exists('set_message')) {
    /**
     * Set a flash message under the 'action' key.
     *
     * Expected message shape: [string $type, string $text]. Example: ['success', 'Saved']
     * This helper sets flashdata (side-effect) and does not redirect.
     *
     * @param array $message Tuple of (type, text). Example: ['success', 'Saved']
     *
     * @return void
     */
    function set_message(array $message): void
    {
        $ci =& get_instance();
        $ci->session->set_flashdata('action', $message);
    }
}

if (!function_exists('handle_session_state')) {
    /**
     * Generic session state handler used by multiple controllers.
     *
     * Centralizes common POST actions: search, filter submit, sort-send and reset.
     * This helper performs redirects when it handles an action (side-effect).
     *
     * @param string $redirect  Route to redirect to after handling (e.g. 'user').
     * @param array  $filterMap Map of session filter keys to POST keys. Example: ['user_level' => 'user-level']
     *
     * @return void
     */
    function handle_session_state(string $redirect, array $filterMap = []): void
    {
        $ci =& get_instance();

        // Handle search (keyword)
        if ($ci->input->post('find')) {
            $ci->session->set_userdata('keyword', $ci->input->post('keyword', true));
            $ci->session->unset_userdata(['sort', 'filter']);
            redirect($redirect);
        }

        // Handle filter submissions
        $filter = $ci->session->userdata('filter') ?: [];
        foreach ($filterMap as $sessionKey => $postKey) {
            if ($ci->input->post($postKey)) {
                $filter[$sessionKey] = $ci->input->post('filter-' . $postKey, true);
                $ci->session->set_userdata('filter', $filter);
                redirect($redirect);
            }
        }

        // Handle sort submissions
        if ($ci->input->post('sort-send')) {
            $ci->session->set_userdata('sort', $ci->input->post('sort', true));
            redirect($redirect);
        }

        // Handle reset action
        if ($ci->input->post('reset')) {
            $ci->session->unset_userdata(['keyword', 'sort', 'filter']);
            redirect($redirect);
        }
    }
}

if (!function_exists('get_rules')) {
    /**
     * Extract validation rules from a controller's validation config map.
     *
     * Returns an array suitable for Form_validation::set_rules().
     *
     * Example:
     * $this->form_validation->set_rules(
     *     get_rules(self::CONFIG['validation'], ['nik', 'name'])
     * );
     *
     * @param array $validationConfig Associative map (CONFIG['validation']).
     * @param array $fields           List of validation keys to extract.
     *
     * @return array                  Array of rule definitions for set_rules().
     */
    function get_rules(array $validationConfig, array $fields): array
    {
        $rules = [];
        foreach ($fields as $field) {
            if (isset($validationConfig[$field])) {
                $rules[] = $validationConfig[$field];
            }
        }

        return $rules;
    }
}

if (!function_exists('format_numeric_display')) {
    /**
     * Format numeric values for display:
     * - returns '-' for null/empty
     * - removes trailing .00 for whole numbers (240.00 -> 240)
     * - trims unnecessary trailing zeros for decimals
     * - ensures non-numeric values are escaped
     *
     * @param mixed $val
     * @return string
     */
    function format_numeric_display($val): string
    {
        if ($val === null || $val === '') {
            return '-';
        }

        if (!is_numeric($val)) {
            return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
        }

        $f = (float) $val;
        if (fmod($f, 1.0) === 0.0) {
            return (string) (int) $f;
        }

        $s = rtrim(rtrim(sprintf('%.8F', $f), '0'), '.');
        return $s;
    }
}

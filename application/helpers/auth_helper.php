<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authentication / Authorization helper
 *
 * Provides simple role checks and guard helpers used across controllers and views.
 */

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        $ci = get_instance();

        // Prefer explicit numeric role_id from session when available
        $roleId = $ci->session->userdata('role_id');
        if (!is_null($roleId) && $roleId !== '') {
            return (int) $roleId === 1;
        }

        // Fallback to role string (from session or user_data)
        $role = $ci->session->userdata('role') ?? ($ci->session->userdata('user_data')['role'] ?? null);
        if ($role === null || $role === '') return false;
        $r = strtolower(trim((string)$role));

        // Numeric string representation (eg. '1')
        if (is_numeric($r)) {
            return (int) $r === 1;
        }

        return in_array($r, ['staf gudang', 'staf_gudang', 'stafgudang'], true);
    }
}

if (!function_exists('is_teknisi')) {
    function is_teknisi(): bool
    {
        $ci = get_instance();
        $roleId = $ci->session->userdata('role_id');
        if (!is_null($roleId) && $roleId !== '') return (int)$roleId === 3;
        
        $role = $ci->session->userdata('role') ?? ($ci->session->userdata('user_data')['role'] ?? null);
        if ($role === null) return false;
        $r = strtolower(trim((string)$role));
        if (is_numeric($r)) return (int)$r === 3;
        return in_array($r, ['teknisi', 'staff lapangan', 'staff_lapangan', 'engineer'], true);
    }
}

if (!function_exists('is_manajer_oe')) {
    function is_manajer_oe(): bool
    {
        $ci = get_instance();
        $roleId = $ci->session->userdata('role_id');
        if (!is_null($roleId) && $roleId !== '') return (int)$roleId === 2;
        $role = $ci->session->userdata('role') ?? ($ci->session->userdata('user_data')['role'] ?? null);
        if ($role === null) return false;
        $r = strtolower(trim((string)$role));
        if (is_numeric($r)) return (int)$r === 2;
        return in_array($r, ['manajer oe', 'manajer_oe', 'manager oe', 'manager_oe', 'manajer', 'manager'], true);
    }
}

if (!function_exists('require_login')) {
    function require_login(): void
    {
        $ci = get_instance();
        if (!$ci->session->userdata('user_data')) {
            redirect(base_url());
            exit;
        }
    }
}

if (!function_exists('require_admin')) {
    function require_admin(): void
    {
        $ci = get_instance();
        $isAdmin = is_admin();
        if (!$ci->session->userdata('user_data') || !$isAdmin) {
            $ci->session->set_flashdata('action', ['danger', 'Akses ditolak. Anda tidak memiliki izin untuk melakukan aksi ini.']);
            redirect(base_url());
            exit;
        }
    }
}

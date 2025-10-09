<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) && $title ? $title : 'Electrical System'; ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css'); ?>">
</head>

<body style="background-color: #caeefb;">
    <nav class="navbar navbar-dark fixed-top" style="background-color: #004274 !important;">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3">
                <!-- Offcanvas toggle always available in air-system (no user levels) -->
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Apparel One Indonesia Logo -->
                <a href="<?= base_url(); ?>">
                    <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- User Name Display (guarded) -->
                <span class="text-white">Hi, <?= isset($this->session->userdata('user_data')['name']) ? explode(' ', $this->session->userdata('user_data')['name'])[0] : 'Guest'; ?></span>

                <!-- Divider -->
                <div class="vr" style="height: 24px; background-color: #ffffff;"></div>

                <!-- Logout Button -->
                <a href="<?= site_url('auth/logout'); ?>" class="text-white text-decoration-none">
                    Logout
                </a>
            </div>

            <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel" style="background-color: #004274 !important;">
                <!-- Off Canvas Header -->
                <div class="offcanvas-header">
                    <!-- Apparel One Indonesia Logo -->
                    <a href="<?= base_url(); ?>">
                        <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                    </a>

                    <!-- Off Canvas Close Button -->
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                <!-- Off Canvas Body -->
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <!-- Link to Beranda (Home dashboard) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('user/dashboard'); ?>">Beranda</a>
                        </li>

                        <!-- Link to User (list/manage users) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('user'); ?>">User</a>
                        </li>

                        <!-- Link to Electric Controller (type selection) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('electric/type'); ?>">Electrical</a>
                        </li>

                        <!-- Link to manage types (available to all users) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('electric_type'); ?>">Kelola Type</a>
                        </li>

                        <!-- Link to Storage Reports -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('storage'); ?>">Storage</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main>
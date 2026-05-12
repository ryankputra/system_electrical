<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) && $title ? $title : 'Electrical System'; ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?= base_url('assets/css/style.css'); ?>">

    <style>
        /* Mengganti background agar sesuai dengan template modern */
        body {
            background-color: #f4f7f6;
        }
        .navbar-logo-aoi {
            height: 30px;
        }
    </style>
</head>

<body>
    <?php $_user_data_for_header = $this->session->userdata('user_data') ?? []; $displayName = !empty($_user_data_for_header['name']) ? explode(' ', $_user_data_for_header['name'])[0] : 'Guest'; ?>
    <nav class="navbar navbar-dark fixed-top shadow-sm" style="background-color: #004274;">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a href="<?= base_url(); ?>">
                    <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="text-white">Hi, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                <div class="vr" style="height: 24px; background-color: #ffffff40;"></div>
                <a href="<?= site_url('auth/logout'); ?>" class="text-white text-decoration-none">
                    Logout
                </a>
            </div>

            <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel" style="background-color: #004274 !important;">
                <div class="offcanvas-header">
                    <a href="<?= base_url(); ?>">
                        <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                    <div class="offcanvas-body">
                        <?php $userInfo = $this->session->userdata('user_data') ?? []; ?>
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item"><a class="nav-link" href="<?= site_url('user/dashboard'); ?>">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= site_url('user'); ?>">Manajemen User</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= site_url('electric'); ?>">Katalog Electrical</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= site_url('location'); ?>"><i class="fas fa-map-marker-alt me-2"></i>Master Lokasi</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= site_url('history'); ?>">Riwayat Stok</a></li>
                        </ul>
                    </div>
            </div>
        </div>
    </nav>

    <main>
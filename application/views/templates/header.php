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
            padding-top: 64px; /* reserve space for fixed-top navbar */
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

                <a href="<?= site_url('dashboard'); ?>">
                    <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="text-white">Hi, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                <div class="vr" style="height: 24px; background-color: #ffffff40;"></div>
                <!-- DEBUG: show current session role and role_id -->
                <div>
                    <span class="badge bg-info text-dark" title="Session role">Role: <?= htmlspecialchars($this->session->userdata('role') ?? ''); ?></span>
                    <span class="badge bg-secondary" title="Session role_id">ID: <?= htmlspecialchars((string)($this->session->userdata('role_id') ?? '')); ?></span>
                </div>
                <a href="<?= site_url('auth/logout'); ?>" class="text-white text-decoration-none">
                    Logout
                </a>
            </div>

            <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel" style="background-color: #004274 !important;">
                <div class="offcanvas-header">
                    <a href="<?= site_url('dashboard'); ?>">
                        <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                    <div class="offcanvas-body">
                        <?php $userInfo = $this->session->userdata('user_data') ?? []; ?>
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <?php if (is_manajer_oe()): ?>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard'); ?>"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('electric'); ?>"><i class="fas fa-microchip me-2"></i>Katalog Electrical</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('audit'); ?>"><i class="fas fa-clipboard-check me-2"></i>Audit / Stock Opname</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history'); ?>"><i class="fas fa-history me-2"></i>Riwayat & Laporan</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history/stock_card'); ?>"><i class="fas fa-address-card me-2"></i>Kartu Stok Digital</a></li>
                                <hr class="border-secondary my-2">
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('location'); ?>"><i class="fas fa-map-marker-alt me-2"></i>Master Lokasi</a></li>
                            <?php elseif (is_teknisi()): ?>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard'); ?>"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('electric'); ?>"><i class="fas fa-microchip me-2"></i>Katalog Electrical</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('wo'); ?>"><i class="fas fa-clipboard-list me-2"></i>Buat Work Order (WO)</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history/out'); ?>"><i class="fas fa-sign-out-alt me-2"></i>Ambil Barang (Keluar)</a></li>
                                <hr class="border-secondary my-2">
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history/mine'); ?>"><i class="fas fa-user-clock me-2"></i>Riwayat Saya</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history'); ?>"><i class="fas fa-history me-2"></i>Laporan Mutasi Global</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history/stock_card'); ?>"><i class="fas fa-address-card me-2"></i>Kartu Stok Digital</a></li>
                            <?php elseif (is_admin()): ?>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard'); ?>"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('electric'); ?>"><i class="fas fa-microchip me-2"></i>Katalog Electrical</a></li>
                                
                                <hr class="border-secondary my-2">
                                <li class="nav-item px-3 text-white-50 small fw-bold mt-2 mb-1">TRANSAKSI & LAPORAN</li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('po'); ?>"><i class="fas fa-shopping-cart me-2"></i>Purchase Order (PO)</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history/in'); ?>"><i class="fas fa-file-import me-2"></i>Procurement / Masuk</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('wo'); ?>"><i class="fas fa-clipboard-list me-2"></i>Work Order (WO)</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('audit'); ?>"><i class="fas fa-clipboard-check me-2"></i>Audit / Stock Opname</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history'); ?>"><i class="fas fa-history me-2"></i>Riwayat & Laporan</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('history/stock_card'); ?>"><i class="fas fa-address-card me-2"></i>Kartu Stok Digital</a></li>

                                <hr class="border-secondary my-2">
                                <li class="nav-item px-3 text-white-50 small fw-bold mt-2 mb-1">MASTER DATA</li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('electric_type'); ?>"><i class="fas fa-tag me-2"></i>Kategori Barang</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('location'); ?>"><i class="fas fa-map-marker-alt me-2"></i>Master Lokasi</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('supplier'); ?>"><i class="fas fa-truck me-2"></i>Master Supplier</a></li>

                                <hr class="border-secondary my-2">
                                <li class="nav-item px-3 text-white-50 small fw-bold mt-2 mb-1">PENGATURAN</li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('user'); ?>"><i class="fas fa-users-cog me-2"></i>Manajemen User</a></li>
                            <?php else: ?>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard'); ?>"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('electric'); ?>"><i class="fas fa-microchip me-2"></i>Katalog Electrical</a></li>
                            <?php endif; ?>
                            
                            <hr class="border-secondary">
                            <li class="nav-item"><a class="nav-link" href="<?= site_url('profile'); ?>"><i class="fas fa-user-circle me-2"></i>Profil & Password</a></li>
                        </ul>
                    </div>
            </div>
        </div>
    </nav>

    <main>
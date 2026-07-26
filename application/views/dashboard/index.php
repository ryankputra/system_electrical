<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper" style="background-color:#faf9f5;padding:1.25rem 1rem;">
    <div class="container-fluid mb-5">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
            .content-wrapper { font-family: 'Inter', sans-serif; }
            .hero-banner {
                background: linear-gradient(135deg, #004274 0%, #0056a6 100%);
                position: relative;
                overflow: hidden;
                border-radius: 16px !important;
            }
            .hero-banner::after {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: radial-gradient(circle at top right, rgba(255,255,255,0.1) 0%, transparent 60%);
                pointer-events: none;
            }
        </style>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm hero-banner">
                    <div class="card-body text-white p-4">
                        <div class="row align-items-center position-relative" style="z-index: 1;">
                            <?php
                                $hour = (int)date('H');
                                if ($hour >= 5 && $hour < 11) {
                                    $greeting = 'Selamat Pagi';
                                } elseif ($hour >= 11 && $hour < 15) {
                                    $greeting = 'Selamat Siang';
                                } elseif ($hour >= 15 && $hour < 18) {
                                    $greeting = 'Selamat Sore';
                                } else {
                                    $greeting = 'Selamat Malam';
                                }
                                $userData = $this->session->userdata('user_data') ?? [];
                                $fullName = !empty($userData['name']) ? $userData['name'] : 'Guest';
                                $firstName = explode(' ', trim($fullName))[0];
                            ?>
                            <div class="col-md-8">
                                <h5 class="mb-1 text-white-50 fw-normal"><?= $greeting; ?>, <?= htmlspecialchars($firstName); ?></h5>
                                <h3 class="mb-2 fw-bold" style="letter-spacing: -0.5px;">Selamat Datang di Workshop Automation</h3>
                                <p class="mb-0 text-white-50">Sistem Informasi Manajemen Stok, Pengadaan, Audit dan Penggunaan Material Electrical.</p>
                                <small class="text-white-50 mt-2 d-block text-uppercase fw-semibold tracking-wide" style="font-size: 0.7rem;">Apparel One Indonesia</small>
                            </div>
                            <div class="col-md-4 text-end mt-3 mt-md-0">
                                <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="AOI Logo" class="img-fluid mb-2" style="max-height: 35px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                                <div class="text-white-50 small fw-medium d-flex flex-column align-items-end gap-1">
                                    <div class="px-3 py-1 bg-white bg-opacity-10 rounded-pill"><i class="fas fa-calendar-alt me-2"></i><?= date('d M Y'); ?></div>
                                    <div class="px-3 py-1 bg-white bg-opacity-10 rounded-pill"><i class="fas fa-clock me-2"></i><span id="current-time"><?= date('H:i:s'); ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php 
            $pending_wo_count = 0;
            $pending_po_count = 0;
            if ($this->session->userdata('role') === 'Staf Gudang' && $this->db->table_exists('as_wo_details')) {
                $pending_wo_count = $this->db->where('status', 'Pending')->count_all_results('as_wo_details');
            }
            if (is_manajer_oe() && $this->db->table_exists('as_purchase_orders')) {
                $pending_po_count = $this->db->where('status', 'Pending')->count_all_results('as_purchase_orders');
            }
        ?>
        <?php if($pending_wo_count > 0): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex justify-content-between align-items-center p-3 mb-0" role="alert" style="background: linear-gradient(to right, #fff3cd, #ffecb5);">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-25 p-2 rounded-circle me-3">
                            <i class="fas fa-bell text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="alert-heading mb-1 fw-bold text-dark">Perhatian! Ada Pengajuan WO</h6>
                            <p class="mb-0 text-dark small">Terdapat <strong><?= $pending_wo_count ?></strong> permintaan barang dari teknisi yang menunggu <strong>Approval</strong> Anda.</p>
                        </div>
                    </div>
                    <a href="<?= site_url('wo'); ?>" class="btn btn-warning fw-bold px-4 rounded-pill shadow-sm">Cek Sekarang</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($pending_po_count > 0): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex justify-content-between align-items-center p-3 mb-0" role="alert" style="background: linear-gradient(to right, #fff3cd, #ffecb5);">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-25 p-2 rounded-circle me-3">
                            <i class="fas fa-file-invoice text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="alert-heading mb-1 fw-bold text-dark">Perhatian! Ada Draf PO Baru</h6>
                            <p class="mb-0 text-dark small">Terdapat <strong><?= $pending_po_count ?></strong> dokumen Purchase Order (PO) yang menunggu <strong>Persetujuan (Approval)</strong> Anda.</p>
                        </div>
                    </div>
                    <a href="<?= site_url('po'); ?>" class="btn btn-warning fw-bold px-4 rounded-pill shadow-sm">Review PO</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-pill overflow-hidden">
                    <div class="card-body p-2">
                        <form id="searchForm" class="row g-2 align-items-center m-0" onsubmit="event.preventDefault(); performSearch();">
                            <div class="col-lg-10 col-md-9 col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0 text-muted ps-4"><i class="fas fa-search"></i></span>
                                    <input type="search" class="form-control border-0 shadow-none bg-transparent" id="globalSearch" placeholder="Cari transaksi berdasarkan ID, waktu, aksi, atau lokasi..." aria-label="Cari transaksi">
                                </div>
                            </div>
                            <div class="col-lg-1 col-md-3 col-6 pe-1">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-medium"><i class="fas fa-search d-md-none"></i><span class="d-none d-md-inline">Cari</span></button>
                            </div>
                            <div class="col-lg-1 col-md-3 col-6 ps-1">
                                 <button type="button" class="btn btn-light w-100 rounded-pill fw-medium text-muted" onclick="clearSearch()"><i class="fas fa-times d-md-none"></i><span class="d-none d-md-inline">Reset</span></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Row -->
        <div class="row mb-4">
            <div class="col-12">
                <style>
                    .btn-quick-action {
                        border-radius: 50px;
                        padding: 10px 24px;
                        font-weight: 500;
                        transition: all 0.3s ease;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        border: 2px solid transparent;
                    }
                    .btn-quick-action:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
                    }
                    /* Customizing specific colors based on user's request to keep base colors */
                    .btn-qa-success { background: #e6f8f0; color: #198754; border-color: #e6f8f0; }
                    .btn-qa-success:hover { background: #198754; color: #fff; }
                    
                    .btn-qa-warning { background: #fff8e6; color: #ffc107; border-color: #fff8e6; }
                    .btn-qa-warning:hover { background: #ffc107; color: #212529; }
                    
                    .btn-qa-info { background: #e0f6f9; color: #0dcaf0; border-color: #e0f6f9; }
                    .btn-qa-info:hover { background: #0dcaf0; color: #212529; }
                    
                    .btn-qa-secondary { background: #f8f9fa; color: #6c757d; border-color: #e9ecef; }
                    .btn-qa-secondary:hover { background: #e9ecef; color: #495057; border-color: #dee2e6; }
                </style>
                <div class="d-flex gap-3 flex-wrap">
                    <?php if (is_admin()): ?>
                    <a href="<?= site_url('po'); ?>" class="btn-quick-action btn-qa-success text-decoration-none" title="Buat Purchase Order (PO)">
                        <i class="fas fa-box-open"></i> Masuk Barang (PO)
                    </a>
                    <?php endif; ?>
                    
                    <?php if (is_admin() || is_teknisi()): ?>
                    <a href="<?= site_url('wo'); ?>" class="btn-quick-action btn-qa-warning text-decoration-none" title="Buat Work Order (WO)">
                        <i class="fas fa-hand-holding-box"></i> Ambil Barang (WO)
                    </a>
                    <?php endif; ?>
                    <?php if (is_admin() || is_manajer_oe()): ?>
                    <a href="<?= site_url('audit'); ?>" class="btn-quick-action btn-qa-info text-decoration-none" title="Opname / Verifikasi stok">
                        <i class="fas fa-clipboard-check"></i> Audit Stok
                    </a>
                    <?php endif; ?>
                    <?php if (is_admin()): ?>
                    <a href="<?= site_url('electric'); ?>" class="btn-quick-action btn-qa-secondary text-decoration-none" title="Kelola data barang">
                        <i class="fas fa-tools"></i> Manajemen Barang
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
            .stat-cards { display:flex; gap:0.75rem; align-items:stretch; margin-bottom:1.5rem; flex-wrap:wrap }
            .stat-card { 
                flex:1 1 0; min-width:150px; border-radius:12px; 
                transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease; 
                background: #fff;
            }
            .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
            .stat-icon { 
                width: 36px; height: 36px; border-radius: 10px; 
                display: flex; align-items: center; justify-content: center; 
                font-size: 16px; margin-right: 12px;
                transition: all 0.3s ease;
            }
            .stat-card:hover .stat-icon { transform: scale(1.05) rotate(5deg); }
            
            /* Softer colors with solid text for elegant look */
            .stat-blue { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
            .stat-cyan { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
            .stat-green { background: rgba(32, 201, 151, 0.1); color: #20c997; }
            .stat-red { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
            .stat-yellow { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
            
            .stat-value { font-size: 1.15rem; font-weight: 700; letter-spacing: -0.2px; margin-top: -2px; }
            .stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.3px; color: #6c757d; font-weight: 600; margin-bottom: 0; }
            @media (max-width:767px) { .stat-card { min-width:48%; } }
        </style>

        <div class="stat-cards">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center px-3 py-2">
                    <div class="stat-icon stat-blue"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="stat-label">Total Lokasi</div>
                        <div class="text-dark stat-value"><?= isset($total_locations) ? number_format($total_locations) : 0; ?></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center px-3 py-2">
                    <div class="stat-icon stat-cyan"><i class="fas fa-boxes"></i></div>
                    <div>
                        <div class="stat-label">Total Barang</div>
                        <div class="text-dark stat-value"><?= isset($total_items) ? number_format($total_items) : 0; ?></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center px-3 py-2">
                    <div class="stat-icon stat-green"><i class="fas fa-tags"></i></div>
                    <div>
                        <div class="stat-label">Tipe Electrical</div>
                        <div class="text-dark stat-value"><?= isset($total_types) ? number_format($total_types) : 0; ?></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center px-3 py-2">
                    <div class="stat-icon stat-red"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <div class="stat-label">Stok Rendah</div>
                        <div class="text-dark stat-value"><?= isset($low_stock) ? number_format(count($low_stock)) : 0; ?></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center px-3 py-2">
                    <div class="stat-icon stat-yellow"><i class="fas fa-history"></i></div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Barang Terlama</div>
                        <?php if (!empty($barang_terlama_list) && is_array($barang_terlama_list)): ?>
                            <div class="small mt-1">Daftar Batch Tertua per Tipe:</div>
                            <div class="small mt-2" style="max-height:160px; overflow:auto;">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tipe</th>
                                            <th>Batch</th>
                                            <th>Tgl</th>
                                            <th>Lokasi</th>
                                            <th class="text-end">Sisa</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($barang_terlama_list as $b): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($b['type_name'] ?? '-'); ?></td>
                                                <td><?= htmlspecialchars($b['batch_number'] ?? '-'); ?></td>
                                                <td><?= isset($b['date_stored']) && $b['date_stored'] ? date('d M Y', strtotime($b['date_stored'])) : '-'; ?></td>
                                                <td><?= htmlspecialchars($b['location_name'] ?? '-'); ?></td>
                                                <td class="text-end"><?= number_format($b['quantity_remaining'] ?? 0); ?></td>
                                                <td><?= $b['status_batch'] ?? ''; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="h5 mb-0" style="font-weight:600;"> <?= htmlspecialchars($barang_terlama ?? '-'); ?></div>
                            <div class="small mt-1"><?= htmlspecialchars($barang_terlama_name ?? '-'); ?></div>
                            <div class="small">Lokasi: <?= htmlspecialchars($barang_terlama_location ?? '-'); ?></div>
                            <div class="small mt-1">Sisa Batch: <?= number_format($barang_terlama_quantity ?? 0); ?></div>
                            <div class="small mt-1">Status: <?= $barang_terlama_status ?? ''; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Aktivitas Transaksi (7 Hari Terakhir)</h5>
                            <p class="text-muted small mb-0">Jumlah transaksi penyimpanan dan pengambilan per hari</p>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ((function_exists('is_admin') && is_admin()) || (function_exists('is_manajer_oe') && is_manajer_oe())): ?>
                                <a href="<?= site_url('dashboard/download_monthly'); ?>" class="btn btn-sm btn-outline-success" title="Download Laporan Bulanan (CSV)">
                                    <i class="fas fa-file-csv me-1"></i>
                                    <span class="d-none d-md-inline">Download</span>
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="location.reload()" title="Segarkan data">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div style="position: relative; height: 320px;">
                            <canvas id="transactionsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        

        <div class="row mb-4">
            <div class="col-lg-7 mb-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Transaksi Terbaru</h5>
                        <a href="<?= site_url('history'); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="transactionTable">
                                <thead>
                                    <tr class="table-light">
                                        <th>Waktu</th>
                                        <th>Nama Barang</th>
                                        <th>Aksi</th>
                                        <th class="text-center">Qty</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_transactions)): ?>
                                        <?php foreach ($recent_transactions as $tx): ?>
                                            <tr>
                                                <td><small class="text-muted"><?= htmlspecialchars($tx['datetime'] ?? $tx['created_at'] ?? $tx['date'] ?? $tx['tanggal_terima'] ?? ''); ?></small></td>
                                                <td>
                                                    <?php
                                                        $namaBarang = $tx['spec_type'] ?? $tx['nama_barang'] ?? '-';
                                                        $kategori   = $tx['nama_barang'] ?? '';
                                                        $brand      = $tx['brand'] ?? '';
                                                        // Jika spec_type sama dengan nama_barang, cukup tampilkan satu
                                                        $subInfo = [];
                                                        if (!empty($kategori) && $kategori !== $namaBarang) $subInfo[] = $kategori;
                                                        if (!empty($brand) && $brand !== '-') $subInfo[] = $brand;
                                                    ?>
                                                    <div class="fw-semibold" style="font-size:0.85rem;"><?= htmlspecialchars($namaBarang) ?></div>
                                                    <?php if (!empty($subInfo)): ?>
                                                        <small class="text-muted"><?= htmlspecialchars(implode(' — ', $subInfo)) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <?php
                                                    $typeRaw = strtolower(trim((string)($tx['action'] ?? $tx['type'] ?? $tx['keterangan'] ?? '')));
                                                    $inValues  = ['masuk','in','store','simpan','inbound'];
                                                    $outValues = ['keluar','out','ambil','take','pengambilan','outgoing'];
                                                    $isIn = false; $isOut = false;
                                                    if (isset($tx['type'])) {
                                                        $tLower = strtolower((string)$tx['type']);
                                                        if (in_array($tLower, ['masuk','in','store','simpan'])) { $isIn = true; }
                                                        elseif (in_array($tLower, ['keluar','out','ambil'])) { $isOut = true; }
                                                    }
                                                    if (!$isIn && !$isOut) {
                                                        foreach ($inValues  as $v) { if (strpos($typeRaw, $v) !== false) { $isIn  = true; break; } }
                                                        foreach ($outValues as $v) { if (strpos($typeRaw, $v) !== false) { $isOut = true; break; } }
                                                    }
                                                    $badgeClass = $isIn ? 'success' : ($isOut ? 'warning' : 'secondary');
                                                    $icon  = $isIn ? 'plus' : 'minus';
                                                    $label = $isIn ? 'Masuk' : ($isOut ? 'Keluar' : 'Lainnya');
                                                ?>
                                                <td>
                                                    <span class="badge rounded-pill text-bg-<?= $badgeClass; ?>">
                                                        <i class="fas fa-<?= $icon; ?> me-1"></i><?= $label; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <small class="fw-bold <?= $isIn ? 'text-success' : 'text-danger' ?>">
                                                        <?= ($isIn ? '+' : '-') . number_format((int)($tx['display_amount'] ?? $tx['qty'] ?? 0)) ?>
                                                    </small>
                                                </td>
                                                <td><small class="text-muted"><?= htmlspecialchars($tx['location_name'] ?? $tx['location'] ?? '-'); ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-5 text-muted">
                                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                                <p class="mb-0">Belum ada transaksi terbaru.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
				<div class="card border-0 shadow-sm rounded-4 h-100 border-start border-danger border-4">
					<div class="card-header bg-danger bg-opacity-10 border-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
						<div>
							<h5 class="mb-0 text-danger"><i class="fas fa-exclamation-circle me-2"></i>Detail Stok Rendah</h5>
							<small class="text-muted">Threshold: stok &le; <?= (int)($threshold ?? 5); ?> unit</small>
						</div>
						<a href="<?= site_url('dashboard/low_stock_detail'); ?>" class="btn btn-sm btn-outline-danger">Lihat Detail</a>
					</div>
					<div class="card-body p-4" style="max-height: 450px; overflow-y: auto;">
						<?php if (!empty($low_stock)): ?>
							<ul class="list-group list-group-flush">
								<?php foreach ($low_stock as $item):
									$stok = (int)($item['total_amount'] ?? $item['stock'] ?? 0);
									$badgeClass = $stok === 0 ? 'bg-danger' : ($stok <= 2 ? 'bg-warning text-dark' : 'bg-orange text-dark');
									// Build spec parts
									$sp = [];
									if (!empty($item['spec_type'])) $sp[] = $item['spec_type'];
									$vv = trim(($item['voltage'] ?? '') . ($item['voltage_unit'] ?? ''));
									if ($vv !== '') $sp[] = $vv;
									if (!empty($item['ampere'])) $sp[] = $item['ampere'] . 'A';
									$dd = trim(($item['daya'] ?? '') . ($item['daya_unit'] ?? ''));
									if ($dd !== '') $sp[] = $dd;
									$specStr = implode(' | ', $sp);
								?>
								<li class="list-group-item px-1 py-2 <?= $stok === 0 ? 'bg-danger bg-opacity-10' : ''; ?>">
									<div class="d-flex justify-content-between align-items-start">
										<div style="max-width:75%;">
											<div class="fw-bold text-dark" style="font-size:0.85rem;"><?= htmlspecialchars($item['category'] ?? ''); ?></div>
											<small class="text-muted"><?= htmlspecialchars($item['type_id'] ?? ''); ?></small>
											<?php if (!empty($item['brand'])): ?>
											<div><small class="text-secondary"><i class="fas fa-tag me-1"></i><?= htmlspecialchars($item['brand']); ?></small></div>
											<?php endif; ?>
											<?php if ($specStr !== ''): ?>
											<div class="mt-1"><?php foreach ($sp as $s): ?><span class="badge bg-light text-dark border me-1" style="font-size:0.68rem;"><?= htmlspecialchars($s); ?></span><?php endforeach; ?></div>
											<?php endif; ?>
											<?php if (!empty($item['location_name'])): ?>
											<div><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($item['location_name']); ?></small></div>
											<?php endif; ?>
										</div>
										<span class="badge <?= $stok === 0 ? 'bg-danger' : ($stok <= 2 ? 'bg-warning text-dark' : 'bg-danger bg-opacity-75'); ?> rounded-pill fs-6 ms-2 flex-shrink-0">
											<?= $stok === 0 ? '<i class="fas fa-ban me-1"></i>Habis' : $stok . ' unit'; ?>
										</span>
									</div>
								</li>
								<?php endforeach; ?>
							</ul>
						<?php else: ?>
							<div class="text-center p-5 text-muted">
								<i class="fas fa-check-circle fa-3x text-success mb-3"></i>
								<p class="mb-0">Semua stok aman (di atas <?= (int)($threshold ?? 5); ?> unit).</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
            const clockElement = document.getElementById('current-time');
            if (clockElement) clockElement.textContent = timeString;
        }
        updateClock();
        setInterval(updateClock, 1000);

        function initTransactionsChart() {
            const ctx = document.getElementById('transactionsChart');
            if (!ctx) return;

            const chartData = <?= json_encode($chart_data ?? []); ?>;
            const chartLabels = <?= json_encode($chart_labels ?? []); ?>;

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Jumlah Transaksi',
                        data: chartData,
                        backgroundColor: 'rgba(0, 86, 166, 0.7)',
                        borderColor: 'rgba(0, 86, 166, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)'}, ticks: { precision: 0 } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }
        initTransactionsChart();

        const searchInput = document.getElementById('globalSearch');
        const transactionTableBody = document.querySelector('#transactionTable tbody');

        window.performSearch = function() {
            if (!searchInput || !transactionTableBody) return;
            const searchTerm = searchInput.value.trim().toUpperCase();
            Array.from(transactionTableBody.querySelectorAll('tr')).forEach(row => {
                const rowText = row.textContent.trim().toUpperCase();
                row.style.display = rowText.includes(searchTerm) ? '' : 'none';
            });
        };

        if (searchInput) {
            searchInput.addEventListener('input', function() { this.value = this.value.toUpperCase(); });
        }

        window.clearSearch = function() {
            if (!searchInput || !transactionTableBody) return;
            searchInput.value = '';
            Array.from(transactionTableBody.querySelectorAll('tr')).forEach(row => { row.style.display = ''; });
        };
    });
    </script>
</div>
            </script>

<?php
// =================================================================
// File View Dashboard Lengkap
// Path: application/views/dashboard/index.php (atau sesuaikan)
// =================================================================
?>

<?php if ($this->session->flashdata('action')) : ?>
    <div style="position: fixed; top: 5rem; right: 1.5rem; z-index: 1050; min-width: 300px;">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show shadow-lg" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="container-fluid mb-5" style="margin-top: 5rem;">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #004274 0%, #0056a6 100%);">
                <div class="card-body text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">Selamat Datang di Workshop Automation!</h2>
                            <p class="mb-0 opacity-90">Monitor dan kelola inventori electrical dengan mudah.</p>
                            <small class="opacity-75">Apparel One Indonesia</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="AOI Logo" class="img-fluid mb-2" style="max-height: 60px;">
                            <div class="text-white opacity-75 small">
                                <div><i class="fas fa-calendar-alt me-1"></i><?= date('d M Y'); ?></div>
                                <div><i class="fas fa-clock me-1"></i><span id="current-time"><?= date('H:i:s'); ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <form id="searchForm" class="row g-2 align-items-center" onsubmit="event.preventDefault(); performSearch();">
                        <div class="col-lg-10 col-md-9 col-12">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                                <input type="search" class="form-control border-0 bg-light" id="globalSearch" placeholder="Cari transaksi berdasarkan ID, waktu, aksi, atau lokasi..." aria-label="Cari transaksi">
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-3 col-6">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search d-md-none"></i><span class="d-none d-md-inline">Cari</span></button>
                        </div>
                        <div class="col-lg-1 col-md-3 col-6">
                             <button type="button" class="btn btn-outline-secondary w-100" onclick="clearSearch()"><i class="fas fa-times d-md-none"></i><span class="d-none d-md-inline">Reset</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <i class="fas fa-map-marker-alt fa-3x text-primary me-4"></i>
                    <div>
                        <h6 class="mb-1 text-muted">Total Lokasi</h6>
                        <h2 class="mb-0 text-dark fw-bold"><?= isset($total_locations) ? number_format($total_locations) : 0; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <i class="fas fa-boxes fa-3x text-info me-4"></i>
                    <div>
                        <h6 class="mb-1 text-muted">Total Barang</h6>
                        <h2 class="mb-0 text-dark fw-bold"><?= isset($total_items) ? number_format($total_items) : 0; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <i class="fas fa-tags fa-3x text-success me-4"></i>
                    <div>
                        <h6 class="mb-1 text-muted">Total Tipe Electrical</h6>
                        <h2 class="mb-0 text-dark fw-bold"><?= isset($total_types) ? number_format($total_types) : 0; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger me-4"></i>
                    <div>
                        <h6 class="mb-1 text-muted">Stok Rendah</h6>
                        <h2 class="mb-0 text-dark fw-bold"><?= isset($low_stock) ? number_format(count($low_stock)) : 0; ?></h2>
                    </div>
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
                    <button class="btn btn-sm btn-outline-primary" onclick="location.reload()" title="Segarkan data">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
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
                    <a href="<?= site_url('transaksi_stok/reports'); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="transactionTable">
                            <thead>
                                <tr class="table-light">
                                    <th>ID Transaksi</th>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_transactions)): ?>
                                    <?php foreach ($recent_transactions as $tx): ?>
                                        <tr>
                                            <td class="text-truncate" style="max-width:160px;"><small class="font-monospace"><?= htmlspecialchars($tx['storing_id']); ?></small></td>
                                            <td><small><?= htmlspecialchars($tx['datetime']); ?></small></td>
                                            <td>
                                                <span class="badge rounded-pill text-bg-<?= $tx['action'] === 'store' ? 'success' : 'warning'; ?>">
                                                    <i class="fas fa-<?= $tx['action'] === 'store' ? 'plus' : 'minus'; ?> me-1"></i>
                                                    <?= $tx['action'] === 'store' ? 'Simpan' : 'Ambil'; ?>
                                                </span>
                                            </td>
                                            <td><small><?= htmlspecialchars($tx['location_id']); ?></small></td>
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
                    <h5 class="mb-0 text-danger"><i class="fas fa-exclamation-circle me-2"></i>Detail Stok Rendah</h5>
                    <a href="<?= site_url('transaksi_stok/low_stock_detail'); ?>" class="btn btn-sm btn-outline-danger">Lihat Detail</a>
                </div>
                <div class="card-body p-4" style="max-height: 450px; overflow-y: auto;">
                    <?php if (!empty($low_stock)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($low_stock as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-1 py-3">
                                    <div>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 250px;"><?= htmlspecialchars($item['type_id']); ?></div>
                                        <small class="text-muted">Kategori: <?= htmlspecialchars(ucfirst($item['category'])); ?></small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill fs-6">
                                        Stok: <?= htmlspecialchars($item['total_amount']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center p-5 text-muted">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="mb-0">Tidak ada item di bawah stok minimum.</p>
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
    
    // Fungsi untuk jam real-time
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

    // Fungsi untuk inisialisasi Grafik Transaksi
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
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(0,0,0,0.05)'},
                        ticks: { precision: 0 } 
                    }
                },
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#002b52',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                }
            }
        });
    }
    initTransactionsChart();

    // Fungsi untuk Pencarian Global
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
        searchInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    window.clearSearch = function() {
        if (!searchInput || !transactionTableBody) return;
        searchInput.value = '';
        Array.from(transactionTableBody.querySelectorAll('tr')).forEach(row => {
            row.style.display = '';
        });
    };
});

</script>


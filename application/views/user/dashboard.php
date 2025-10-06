<?php
// Basic dashboard view with summary widgets and recent transactions
?>

<link rel="stylesheet" href="<?= base_url('assets/css/style.css'); ?>">

<div class="container mt-5 pt-5 dashboard-container" style="max-width:1200px;">
    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Selamat Datang di Workshop Automation</h2>
                    <p class="mb-0 opacity-75">Monitor dan kelola inventori electrical dengan mudah</p>
                </div>
                <div class="text-end">
                    <small class="opacity-75">Diperbarui: <?= date('d M Y H:i'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body">
            <form id="searchForm" class="row g-2 align-items-center" onsubmit="event.preventDefault(); performSearch();">
                <div class="col-md-9 col-12">
                    <label for="globalSearch" class="visually-hidden">Cari</label>
                    <div class="input-group search-bar">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-search" aria-hidden="true"></i></span>
                        <input type="search" class="form-control rounded-start" id="globalSearch" placeholder="Cari lokasi, tipe electrical, atau transaksi..." aria-label="Cari lokasi, tipe electrical, atau transaksi">
                        <button type="button" id="clearInline" class="btn btn-outline-secondary d-none d-md-inline" onclick="clearSearch()" title="Reset pencarian">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3 col-6 text-md-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1" aria-hidden="true"></i> Cari
                    </button>
                </div>
                <div class="col-12 d-md-none">
                    <div class="d-flex justify-content-end mt-2 gap-2">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="clearSearch()">
                            <i class="fas fa-times me-1" aria-hidden="true"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 m-0 text-primary">Dashboard Ringkas</h1>
                <small class="text-muted">Ringkasan cepat metrik utama dan aktivitas terbaru.</small>
            </div>
            <div class="text-end">
                <button class="btn btn-sm btn-outline-primary mt-1" onclick="location.reload()" title="Segarkan dashboard">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card h-100 shadow-sm border-0 bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-title mb-2">Total Lokasi</h6>
                    <p class="h3 mb-0 fw-bold"><?= isset($total_locations) ? $total_locations : 0; ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card h-100 shadow-sm border-0 bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title mb-2">Total Barang</h6>
                    <p class="h3 mb-0 fw-bold"><?= isset($total_items) ? $total_items : 0; ?></p>
                    <small>(Semua lokasi)</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card h-100 shadow-sm border-0 bg-secondary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title mb-2">Total Tipe Electrical</h6>
                    <p class="h3 mb-0 fw-bold"><?= isset($total_types) ? $total_types : 0; ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card h-100 shadow-sm border-0 bg-warning text-dark">
                <div class="card-body text-center">
                    <h6 class="card-title mb-2">Minimum Stok (<= <?= htmlspecialchars($threshold); ?>)</h6>
                    <p class="h3 mb-0 fw-bold"><?= isset($low_stock) ? count($low_stock) : 0; ?></p>
                    <a href="#minStockDetail" id="linkLowStockDetail" class="btn btn-sm btn-outline-dark mt-2">Lihat detail</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="row my-4 mb-5">
        <div class="col-12">
            <div class="card mb-4 shadow-lg border-0" style="background: #ffffff; border-radius: 15px;">
                <div class="card-header d-flex justify-content-between align-items-center text-dark" style="background: transparent; border-bottom: none;">
                    <h5 class="mb-0 fw-bold">Transaksi (7 Hari Terakhir)</h5>
                    <small class="opacity-75">Jumlah transaksi per hari</small>
                </div>
                <div class="card-body pb-4 px-3 px-md-4 text-dark">
                    <div class="chart-wrapper mb-4">
                        <canvas id="transactionsChart"></canvas>
                    </div>
                    <div class="row mt-4 pt-3 border-top border-light">
                        <div class="col-md-4 text-center">
                            <h6 class="text-muted mb-1">Rata-rata Harian</h6>
                            <p class="h5 mb-0 fw-bold"><?= (isset($chart_data) && count($chart_data) > 0) ? round(array_sum($chart_data) / count($chart_data), 1) : 0; ?></p>
                            <small class="text-muted">transaksi/hari</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6 class="text-muted mb-1">Hari Paling Aktif</h6>
                            <p class="h5 mb-0 fw-bold"><?= (isset($chart_data) && !empty($chart_data)) ? $chart_labels[array_keys($chart_data, max($chart_data))[0]] : '-'; ?></p>
                            <small class="text-muted"><?= (isset($chart_data) && !empty($chart_data)) ? max($chart_data) . ' transaksi' : '0 transaksi'; ?></small>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6 class="text-muted mb-1">Total Transaksi</h6>
                            <p class="h5 mb-0 fw-bold"><?= isset($chart_data) ? array_sum($chart_data) : 0; ?></p>
                            <small class="text-muted">dalam 7 hari</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-2" style="margin-top: -20px;">
            <div class="card h-100" style="border-radius: 20px; padding: 20px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Transaksi Terbaru</strong>
                    <a href="<?= site_url('storage/reports'); ?>" class="btn btn-sm btn-outline-primary">Lihat semua</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recent_transactions)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Waktu</th>
                                        <th>Action</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_transactions as $tx): ?>
                                        <tr>
                                            <td class="text-truncate" style="max-width:160px;"><small><?= htmlspecialchars($tx['storing_id']); ?></small></td>
                                            <td><small class="text-muted"><?= htmlspecialchars($tx['datetime']); ?></small></td>
                                            <td><span class="badge bg-<?= $tx['action'] === 'store' ? 'success' : 'warning'; ?> text-white">
                                                <?= $tx['action'] === 'store' ? 'simpan' : 'ambil'; ?></span></td>
                                            <td><small><?= htmlspecialchars($tx['location_id']); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-3 text-muted">Tidak ada transaksi terbaru.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-2" style="margin-top: -20px;">
            <div class="card h-100" id="minStockDetail" style="border-radius: 20px; padding: 20px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Minimum Stok Detail</strong>
                    <a href="<?= site_url('storage/low_stock_detail'); ?>" class="btn btn-sm btn-outline-primary">Lihat semua</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($low_stock)): ?>
                        <div id="lowStockList">
                            <ul class="list-group list-group-flush">
                                <?php foreach (array_slice($low_stock, 0, 5) as $row): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center low-stock-item" data-category="<?= htmlspecialchars($row['category']); ?>" data-amount="<?= htmlspecialchars($row['total_amount']); ?>" data-type="<?= htmlspecialchars($row['type_id']); ?>">
                                        <div>
                                            <div class="fw-semibold">Tipe: <?= htmlspecialchars($row['type_id']); ?></div>
                                            <small class="text-muted">Kategori: <?= htmlspecialchars($row['category']); ?></small>
                                        </div>
                                        <span class="badge bg-danger">Stok: <?= htmlspecialchars($row['total_amount']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Tidak ada item Minimum Stok.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // =================================================================
    // ALL JAVASCRIPT FUNCTIONS
    // =================================================================
    
    // --- Chart.js Initialization ---
    function initTransactionsChart() {
        const ctx = document.getElementById('transactionsChart');
        if (!ctx) return;
        const chartData = <?= json_encode($chart_data ?? []); ?>;
        const chartLabels = <?= json_encode($chart_labels ?? []); ?>;
        const average = chartData.length > 0 ? chartData.reduce((a, b) => a + b, 0) / chartData.length : 0;

        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Transaksi',
                        data: chartData,
                        backgroundColor: 'rgba(54,162,235,0.75)',
                        borderRadius: 10,
                        order: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, title: { display: true, text: 'Jumlah transaksi' } }
                },
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    // --- Global Search ---
    function performSearch() {
        const searchInput = document.getElementById('globalSearch');
        const searchTerm = (searchInput ? searchInput.value : '').trim();

        if (!searchTerm) {
            alert('Masukkan kata kunci pencarian');
            return;
        }

        let txCard = null;
        document.querySelectorAll('.card-header strong').forEach(header => {
            if (header.textContent.trim().toUpperCase().includes('TRANSAKSI TERBARU')) {
                txCard = header.closest('.card');
            }
        });

        if (txCard) {
            txCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        const txTbody = document.querySelector('.table-responsive table tbody');
        if (!txTbody) return;

        Array.from(txTbody.querySelectorAll('tr')).forEach(row => {
            const rowText = row.textContent.trim().toUpperCase();
            row.style.display = rowText.includes(searchTerm.toUpperCase()) ? '' : 'none';
        });
    }

    // Tambahkan event listener untuk auto kapitalisasi
    const searchInput = document.getElementById('globalSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    function clearSearch() {
        document.getElementById('globalSearch').value = '';
        const txTbody = document.querySelector('.table-responsive table tbody');
        if (!txTbody) return;
        Array.from(txTbody.querySelectorAll('tr')).forEach(row => row.style.display = '');
    }

    // --- Low Stock Card Functions ---
    function downloadLowStock() {
        // Fungsi download sekarang men-download semua item yang terlihat
        const items = document.querySelectorAll('.low-stock-item');
        let csvContent = "Tipe Electrical,Kategori,Jumlah Stok\n";
        items.forEach(item => {
            csvContent += `"${item.dataset.type}","${item.dataset.category}","${item.dataset.amount}"\n`;
        });
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `stok_rendah_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }
    

    // =================================================================
    // EVENT LISTENERS (RUN AFTER DOM IS LOADED)
    // =================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the chart
        initTransactionsChart();

        // Attach listener for smooth scroll to low stock details
        const linkLowStock = document.getElementById('linkLowStockDetail');
        if (linkLowStock) {
            linkLowStock.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.getElementById('minStockDetail');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    target.classList.add('border-highlight');
                    setTimeout(() => target.classList.remove('border-highlight'), 3000);
                }
            });
        }
    });
    </script>
</div>
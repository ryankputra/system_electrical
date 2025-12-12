<?php if ($this->session->flashdata('action')) : ?>
    <!-- Flash Notification Alert -->
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; width: 98%;">
    <!-- Card Header with Title -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-dark m-0">Laporan Penyimpanan</h3>
                <p class="text-muted mb-0">Riwayat transaksi dan analitik</p>
            </div>
            <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Penyimpanan
            </a>
        </div>
    </div>

    <!-- Card Body with Main Content -->
    <div class="card-body px-lg-5 px-4 py-4">

        <!-- Filter Toggle -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Filter & Laporan</h6>
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fas fa-filter"></i> Toggle Filter
            </button>
        </div>

        <!-- Filter Form -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border rounded-4 collapse" id="filterCollapse">
                    <div class="card-header">
                        <h6 class="mb-0">Filter</h6>
                    </div>
                    <div class="card-body">
                        <?= form_open('storage/reports', ['method' => 'GET', 'class' => 'row g-3']); ?>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                value="<?= $filters['start_date'] ?? ''; ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Tanggal Berakhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                value="<?= $filters['end_date'] ?? ''; ?>">
                        </div>

                        <div class="col-md-2">
                            <label for="action" class="form-label">Aksi</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">Semua Aksi</option>
                                <option value="store" <?= isset($filters['action']) && $filters['action'] == 'store' ? 'selected' : ''; ?>>Simpan</option>
                                <option value="take" <?= isset($filters['action']) && $filters['action'] == 'take' ? 'selected' : ''; ?>>Ambil</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="location" class="form-label">Lokasi</label>
                            <select class="form-select" id="location" name="location">
                                <option value="">Semua Lokasi</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['location_id']; ?>"
                                        <?= isset($filters['location_id']) && $filters['location_id'] == $location['location_id'] ? 'selected' : ''; ?>>
                                        <?= $location['location_id']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="category" class="form-label">Kategori</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Semua Kategori</option>
                                <option value="pneumatic" <?= isset($filters['category']) && $filters['category'] == 'pneumatic' ? 'selected' : ''; ?>>Pneumatic</option>
                                <option value="valve" <?= isset($filters['category']) && $filters['category'] == 'valve' ? 'selected' : ''; ?>>Valve</option>
                                <option value="fitting" <?= isset($filters['category']) && $filters['category'] == 'fitting' ? 'selected' : ''; ?>>Fitting</option>
                                <option value="sensor" <?= isset($filters['category']) && $filters['category'] == 'sensor' ? 'selected' : ''; ?>>Sensor</option>
                                <option value="other" <?= isset($filters['category']) && $filters['category'] == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                            <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Terapkan Filter
                            </button>
                            <a href="<?= site_url('storage/reports'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Hapus Filter
                            </a>
                            <button type="button" class="btn btn-success" onclick="exportToCsv()">
                                <i class="fas fa-download"></i> Ekspor CSV
                            </button>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>



            <!-- Transactions Table -->
            <?php if (!empty($transactions)): ?>
                <div class="table-responsive">
                    <table class="table table-sm" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>ID Penyimpanan</th>
                                <th>Tanggal/Waktu</th>
                                <th>Aksi</th>
                                <th>Jumlah</th>
                                <th>Lokasi</th>
                                <th>Kategori</th>
                                <th>ID Tipe</th>
                                <th>Project</th>
                                <th>Pengguna</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td>
                                        <code><?= htmlspecialchars($transaction['storing_id']); ?></code>
                                    </td>
                                    <td>
                                        <?= date('M d, Y H:i', strtotime($transaction['datetime'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($transaction['action'] == 'store'): ?>
                                            <span class="badge bg-success">Simpan</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Ambil</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= isset($transaction['amount']) ? (int)$transaction['amount'] : 1; ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('storage/location/' . urlencode($transaction['location_id'])); ?>"
                                            class="text-decoration-none"
                                            title="Lihat detail lokasi">
                                            <?= htmlspecialchars($transaction['location_id']); ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($transaction['category']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?= htmlspecialchars($transaction['type_id']); ?>
                                            <?php if (isset($transaction['comment']) && $transaction['comment'] === 'PROJECT'): ?>
                                                <i class="fas fa-project-diagram text-primary ms-2" title="Barang Project"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($transaction['project_name'])): ?>
                                            <span class="text-primary" title="Project: <?= htmlspecialchars($transaction['project_name']); ?>">
                                                <?= strlen($transaction['project_name']) > 15
                                                    ? substr(htmlspecialchars($transaction['project_name']), 0, 15) . '...'
                                                    : htmlspecialchars($transaction['project_name']); ?>
                                            </span>
                                        <?php elseif (!empty($transaction['batch_id'])): ?>
                                            <span class="text-muted">Batch</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($transaction['user_name'] ?? 'Tidak Diketahui'); ?></td>
                                    <td>
                                        <?php if ($transaction['note']): ?>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted me-2" title="<?= htmlspecialchars($transaction['note']); ?>">
                                                    <?= strlen($transaction['note']) > 20 ? substr(htmlspecialchars($transaction['note']), 0, 20) . '...' : htmlspecialchars($transaction['note']); ?>
                                                </span>
                                                <?php if (strlen($transaction['note']) > 20): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="showFullNote('<?= htmlspecialchars(addslashes($transaction['storing_id'])); ?>', '<?= htmlspecialchars(addslashes($transaction['note'])); ?>')"
                                                        title="Lihat catatan lengkap">
                                                        <img src="<?= base_url('assets/img/eye.svg'); ?>" alt="View" style="width: 14px; height: 14px;">
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <?php if (isset($pagination_links) && !empty($pagination_links)): ?>
                    <div class="card-footer bg-white border-0 px-lg-5 px-4
                        <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center gap-3">
                            <!-- Record Count Display -->
                            <div class="text-muted">
                                Menampilkan <strong><?= $display; ?></strong>
                            </div>

                            <!-- Pagination Links -->
                            <?= $pagination_links; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                    <h5>Tidak ada transaksi ditemukan</h5>
                    <p>Tidak ada transaksi yang cocok dengan filter saat ini. Coba sesuaikan filter atau <a href="<?= site_url('storage/store'); ?>">mulai dengan menyimpan beberapa barang</a>.</p>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <?php if (!empty($stats)): ?>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white border-0">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-white-50">Total Transaksi</small>
                                        <h4 class="mb-0"><?= number_format($stats['total_transactions'] ?? 0); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exchange-alt fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-success text-white border-0">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-white-50">Operasi Simpan</small>
                                        <h4 class="mb-0"><?= number_format($stats['store_transactions'] ?? 0); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-plus fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-warning text-white border-0">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-white-50">Operasi Ambil</small>
                                        <h4 class="mb-0"><?= number_format($stats['take_transactions'] ?? 0); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-minus fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-info text-white border-0">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-white-50">Pengguna Aktif</small>
                                        <h4 class="mb-0"><?= number_format($stats['users_involved'] ?? 0); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</div>
</div>

<!-- Full Note Modal -->
<div class="modal fade" id="fullNoteModal" tabindex="-1" aria-labelledby="fullNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullNoteModalLabel">Catatan Lengkap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>ID Penyimpanan:</strong>
                    <code id="modalStoringId"></code>
                </div>
                <div>
                    <strong>Catatan:</strong>
                    <div class="mt-2 p-3 bg-light rounded" id="modalNoteContent" style="white-space: pre-wrap; word-wrap: break-word;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= base_url('assets/css/style.css'); ?>">
<script>
    function exportToCsv() {
        // Get current filter parameters
        const startDate = document.querySelector('input[name="start_date"]')?.value || '';
        const endDate = document.querySelector('input[name="end_date"]')?.value || '';

        // Build URL with parameters
        let url = '<?= site_url("storage/export_transactions_csv"); ?>';
        let params = [];

        if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
        if (endDate) params.push('end_date=' + encodeURIComponent(endDate));

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        // Open in new window to trigger download
        window.open(url, '_blank');
    }

    // Function to show full note in modal
    function showFullNote(storingId, note) {
        document.getElementById('modalStoringId').textContent = storingId;
        document.getElementById('modalNoteContent').textContent = note;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('fullNoteModal'));
        modal.show();
    }

    // Auto-set end date when start date is selected
    document.getElementById('start_date').addEventListener('change', function() {
        const endDate = document.getElementById('end_date');
        if (!endDate.value && this.value) {
            endDate.value = this.value;
        }
    });

    // Quick date filters
    function setDateFilter(days) {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(endDate.getDate() - days);

        document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
        document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    }

    // Add quick filter buttons
    document.addEventListener('DOMContentLoaded', function() {
        const filterCard = document.querySelector('.card-body');
        const quickFilters = document.createElement('div');
        quickFilters.className = 'mb-3';
        quickFilters.innerHTML = `
        <label class="form-label">Filter Cepat:</label><br>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary" onclick="setDateFilter(7)">7 hari terakhir</button>
            <button type="button" class="btn btn-outline-primary" onclick="setDateFilter(30)">30 hari terakhir</button>
            <button type="button" class="btn btn-outline-primary" onclick="setDateFilter(90)">3 bulan terakhir</button>
        </div>
    `;

        const firstRow = filterCard.querySelector('.row');
        filterCard.insertBefore(quickFilters, firstRow);
    });
</script>
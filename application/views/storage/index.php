<?php if ($this->session->flashdata('success')): ?>
    <div class="cust-notification m-3">
        <div class="alert alert-success alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="cust-notification m-3">
        <div class="alert alert-danger alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
        <div class="row mb-3">
            <div class="col-12">
                <form method="get" action="<?= site_url('storage'); ?>" class="d-flex gap-2">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari penyimpanan..." value="<?= htmlspecialchars($keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if (!empty($keyword)): ?>
                        <a href="<?= site_url('storage'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-6">
                <h3 class="text-dark m-0">Overview Penyimpanan</h3>
                <p class="text-muted mb-0">Kelola inventaris Anda di semua lokasi penyimpanan</p>
            </div>

            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 flex-wrap justify-content-lg-end">
                    <a href="<?= site_url('storage/store'); ?>" class="btn btn-success">
                        <i class="fas fa-plus"></i> Simpan Barang
                    </a>
                    <a href="<?= site_url('storage/take'); ?>" class="btn btn-warning">
                        <i class="fas fa-minus"></i> Ambil Barang
                    </a>
                    <a href="<?= site_url('storage/reports'); ?>" class="btn btn-secondary">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body px-lg-5 px-4 py-4">

        <div class="row mb-4">
            <div class="col-12">
                <div class="card rounded-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Lokasi Penyimpanan
                            <?php if (!empty($keyword)): ?>
                                <small class="text-muted">(difilter untuk: "<?= htmlspecialchars($keyword); ?>")</small>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($locations)): ?>
                            <div class="row">
                                <?php foreach ($locations as $location): ?>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card border-primary rounded-3">
                                            <div class="card-body text-center">
                                                <h6 class="card-title"><?= htmlspecialchars($location['location_id']); ?></h6>
                                                
                                                <a href="<?= site_url('storage/location/' . rawurlencode($location['location_id'])); ?>" class="btn btn-primary btn-sm">
                                                    Lihat Detail
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php if (!empty($keyword)): ?>
                                    Tidak ada lokasi penyimpanan yang mengandung item dengan kata kunci "<?= htmlspecialchars($keyword); ?>".
                                <?php else: ?>
                                    Tidak ada lokasi penyimpanan ditemukan. Mulai dengan menyimpan beberapa barang!
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4" id="inventoryOverviewCard">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Overview Inventaris
                            <?php if (!empty($keyword)): ?>
                                <small class="text-muted">(difilter untuk: "<?= htmlspecialchars($keyword); ?>")</small>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($storage_overview)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kategori</th>
                                            <th>ID Tipe</th>
                                            <th>Total Stok</th>
                                            <th>Lokasi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($storage_overview as $item): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($item['category']); ?></span>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(str_replace('_PROJECT', '', $item['type_id'])); ?>
                                                    <?php if (str_ends_with($item['type_id'], '_PROJECT')): ?>
                                                        <i class="fas fa-project-diagram text-primary ms-1" title="Barang Project"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><span class="badge bg-success"><?= number_format($item['total_amount'] ?? 0); ?></span></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $item['location_count']; ?> lokasi</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-success" onclick="quickStore('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                            <i class="fas fa-plus"></i> Simpan
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning" onclick="quickTake('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                            <i class="fas fa-minus"></i> Ambil
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info" onclick="manageItem('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')" title="Kelola & lihat detail lengkap barang">
                                                            <i class="fas fa-cogs"></i> Detail
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                 <?php if (!empty($keyword)): ?>
                                    Tidak ada barang di penyimpanan yang mengandung kata kunci "<?= htmlspecialchars($keyword); ?>".
                                <?php else: ?>
                                    Belum ada barang di penyimpanan. <a href="<?= site_url('storage/store'); ?>">Mulai simpan barang</a>!
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Transaksi Terbaru
                        </h5>
                        <a href="<?= site_url('storage/reports'); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_transactions)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID Penyimpanan</th>
                                            <th>Tanggal/Waktu</th>
                                            <th>Aksi</th>
                                            <th>Lokasi</th>
                                            <th>Kategori</th>
                                            <th>ID Tipe</th>
                                            <th>Project</th>
                                            <th>Pengguna</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_transactions as $transaction): ?>
                                            <tr>
                                                <td><code><?= htmlspecialchars($transaction['storing_id']); ?></code></td>
                                                <td><?= date('M d, Y H:i', strtotime($transaction['datetime'])); ?></td>
                                                <td>
                                                    <?php if ($transaction['action'] == 'store'): ?>
                                                        <span class="badge bg-success">Simpan</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Ambil</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= site_url('storage/location/' . rawurlencode($transaction['location_id'])); ?>"
                                                        class="text-decoration-none"
                                                        title="Lihat detail lokasi">
                                                        <?= htmlspecialchars($transaction['location_id']); ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($transaction['category']); ?></td>
                                                <td><?= htmlspecialchars($transaction['type_id']); ?></td>
                                                <td>
                                                    <?php if (!empty($transaction['project_name'])): ?>
                                                        <span class="text-primary" title="Project: <?= htmlspecialchars($transaction['project_name']); ?>">
                                                            <img src="<?= base_url('assets/img/project-icon.png'); ?>" alt="Project Icon" style="width: 14px; height: 14px;">
                                                            <?= strlen($transaction['project_name']) > 15
                                                                ? substr(htmlspecialchars($transaction['project_name']), 0, 15) . '...'
                                                                : htmlspecialchars($transaction['project_name']); ?>
                                                        </span>
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
                        <?php else: ?>
                            <div class="alert alert-info">
                                Belum ada transaksi.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="quickStoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Simpan Cepat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickStoreForm" method="post" action="<?= site_url('storage/quick_action'); ?>">
                        <input type="hidden" id="storeCategory" name="category">
                        <input type="hidden" id="storeTypeId" name="type_id">
                        <input type="hidden" name="action" value="store">
                        <div class="mb-3">
                            <label class="form-label">Barang:</label>
                            <div id="storeItemInfo" class="form-control-plaintext"></div>
                        </div>
                        <div class="mb-3">
                            <label for="storeLocationId" class="form-label">ID Lokasi:</label>
                            <select class="form-select" id="storeLocationId" name="location_id" required>
                                <option value="">Pilih Lokasi</option>
                                <?php if (!empty($locations)): ?>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?= $location['location_id']; ?>"><?= $location['location_id']; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="storeQuantity" class="form-label">Jumlah:</label>
                            <input type="number" class="form-control" id="storeQuantity" name="quantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="storeNote" class="form-label">Catatan (Opsional):</label>
                            <textarea class="form-control" id="storeNote" name="note" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="document.getElementById('quickStoreForm').submit();">Simpan Barang</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="quickTakeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ambil Cepat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickTakeForm" method="post" action="<?= site_url('storage/quick_action'); ?>">
                        <input type="hidden" id="takeCategory" name="category">
                        <input type="hidden" id="takeTypeId" name="type_id">
                        <input type="hidden" name="action" value="take">

                        <div class="mb-3">
                            <label class="form-label">Barang:</label>
                            <div id="takeItemInfo" class="form-control-plaintext"></div>
                        </div>

                        <div class="mb-3">
                            <label for="takeLocationId" class="form-label">ID Lokasi:</label>
                            <select class="form-select" id="takeLocationId" name="location_id" required>
                                <option value="">Memuat lokasi...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="takeQuantity" class="form-label">Jumlah:</label>
                            <input type="number" class="form-control" id="takeQuantity" name="quantity" min="1" required>
                            <div class="form-text">Tersedia: <span id="availableStock">-</span></div>
                        </div>

                        <div class="mb-3">
                            <label for="takeNote" class="form-label">Catatan (Opsional):</label>
                            <textarea class="form-control" id="takeNote" name="note" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" onclick="document.getElementById('quickTakeForm').submit();">Ambil Barang</button>
                </div>
            </div>
        </div>
    </div>
    
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
    
    <!-- Item Details Modal -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="fas fa-cogs text-info"></i> Kelola & Detail Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="itemDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat detail barang...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for viewing image -->
<div class="modal fade" id="imageViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Gambar Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Gambar Barang" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

<script>
    // SCRIPT MODALS (Sesuai dengan template yang Anda inginkan)
    function quickStore(category, typeId) {
        document.getElementById('storeCategory').value = category;
        document.getElementById('storeTypeId').value = typeId;
        document.getElementById('storeItemInfo').textContent = category + ' - ' + typeId.replace('_PROJECT', '');
        var modal = new bootstrap.Modal(document.getElementById('quickStoreModal'));
        modal.show();
    }

    function quickTake(category, typeId) {
        const takeCategoryEl = document.getElementById('takeCategory');
        const takeTypeEl = document.getElementById('takeTypeId');
        const takeInfoEl = document.getElementById('takeItemInfo');
        const takeLocationSelect = document.getElementById('takeLocationId');
        const availableStockEl = document.getElementById('availableStock');
        const takeQuantityEl = document.getElementById('takeQuantity');

        takeCategoryEl.value = category;
        takeTypeEl.value = typeId; 
        takeInfoEl.textContent = category + ' - ' + typeId.replace('_PROJECT', '');

        takeLocationSelect.innerHTML = '<option value="">Memuat lokasi...</option>';
        availableStockEl.textContent = '-';
        if (takeQuantityEl) takeQuantityEl.value = '';

        fetch('<?= site_url('storage/get_stock'); ?>?category=' + encodeURIComponent(category) + '&type_id=' + encodeURIComponent(typeId))
            .then(res => res.json())
            .then(data => {
                takeLocationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';
                if (data.success && Array.isArray(data.stock_locations) && data.stock_locations.length > 0) {
                    data.stock_locations.forEach(loc => {
                        let opt = document.createElement('option');
                        opt.value = loc.location_id;
                        opt.textContent = loc.location_id + ' (' + (loc.amount || 0) + ' tersedia)';
                        opt.dataset.amount = loc.amount || 0;
                        takeLocationSelect.appendChild(opt);
                    });
                } else {
                    let opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'Tidak ada stok tersedia';
                    opt.disabled = true;
                    takeLocationSelect.appendChild(opt);
                }
            })
            .catch(err => {
                console.error('Gagal memuat stok:', err);
                takeLocationSelect.innerHTML = '<option value="">Gagal memuat</option>';
            });

        var modal = new bootstrap.Modal(document.getElementById('quickTakeModal'));
        modal.show();
    }

    document.getElementById('takeLocationId').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const amt = selected ? parseInt(selected.dataset.amount || '0', 10) : 0;
        document.getElementById('availableStock').textContent = amt;
        const takeQuantityEl = document.getElementById('takeQuantity');
        if (takeQuantityEl) {
            takeQuantityEl.max = amt;
            if (takeQuantityEl.value && parseInt(takeQuantityEl.value, 10) > amt) {
                takeQuantityEl.value = amt;
            }
        }
    });

    function showFullNote(storingId, note) {
        document.getElementById('modalStoringId').textContent = storingId;
        document.getElementById('modalNoteContent').textContent = note;
        var modal = new bootstrap.Modal(document.getElementById('fullNoteModal'));
        modal.show();
    }

    function manageItem(category, typeId) {
        // Since we're on the overview page, we need to show item details across all locations
        showItemDetailsFromOverview(category, typeId);
    }
    
    function showItemDetailsFromOverview(category, typeId) {
        // Get detailed info about this item from the first available location
        fetch('<?= site_url('storage/get_stock'); ?>?category=' + encodeURIComponent(category) + '&type_id=' + encodeURIComponent(typeId))
            .then(response => response.json())
            .then(stockData => {
                console.log('Stock data received:', stockData); // Debug log
                
                if (stockData.success && stockData.stock_locations.length > 0) {
                    // Calculate total stock from API response first, fallback to manual calculation
                    let totalStock = stockData.total_stock || 0;
                    
                    // If API didn't provide total_stock, calculate manually
                    if (!totalStock) {
                        totalStock = stockData.stock_locations.reduce((sum, loc) => sum + (parseInt(loc.amount) || 0), 0);
                    }
                    
                    const totalLocations = stockData.stock_locations.length;
                    
                    console.log('Calculated totals:', { totalStock, totalLocations }); // Debug log
                    
                    // Use the first location to get details
                    const firstLocation = stockData.stock_locations[0];
                    return fetch(`<?= site_url('storage/get_item_details'); ?>?location_id=${firstLocation.location_id}&category=${category}&type_id=${typeId}`)
                        .then(response => response.json())
                        .then(detailData => {
                            console.log('Detail data received:', detailData); // Debug log
                            
                            // Merge stock data with detail data
                            if (detailData.success) {
                                detailData.total_stock = totalStock;
                                detailData.total_locations = totalLocations;
                                detailData.stock_locations = stockData.stock_locations;
                            }
                            return detailData;
                        });
                } else {
                    throw new Error('No stock locations found');
                }
            })
            .then(data => {
                if (data.success) {
                    const item = data.item;
                    let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-box text-primary"></i> Informasi Penyimpanan</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Kategori:</strong></td><td><span class="badge bg-primary">${item.category}</span></td></tr>
                                <tr><td><strong>ID Tipe:</strong></td><td><code>${item.type_id}</code></td></tr>
                                <tr><td><strong>Lokasi Sampel:</strong></td><td>${item.location_id}</td></tr>
                                <tr><td><strong>Stok di Lokasi:</strong></td><td><span class="badge bg-success fs-6">${item.amount}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-chart-bar text-success"></i> Statistik Total</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Total Stok:</strong></td><td><span class="badge bg-info fs-6">${data.total_stock || 0}</span></td></tr>
                                <tr><td><strong>Jumlah Lokasi:</strong></td><td><span class="badge bg-warning">${data.total_locations || 0} lokasi</span></td></tr>
                            </table>
                            
                            <!-- Show all locations -->
                            <div class="mt-3">
                                <small class="text-muted"><strong>Lokasi Penyimpanan:</strong></small>
                                <div class="mt-1">
                                    ${data.stock_locations ? data.stock_locations.map(loc => 
                                        `<span class="badge bg-secondary me-1 mb-1" title="${loc.amount} items">${loc.location_id} (${loc.amount})</span>`
                                    ).join('') : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                    // Add electrical details if available
                    if (item.electric_details) {
                        const elec = item.electric_details;
                        html += `
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6><i class="fas fa-bolt text-warning"></i> Detail Barang Elektrik</h6>
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <tr><td><strong>Electric ID:</strong></td><td><code class="text-primary">${elec.electric_id || '-'}</code></td></tr>
                                                        <tr><td><strong>Nama:</strong></td><td><span class="fw-bold text-dark">${elec.nama || '-'}</span></td></tr>
                                                        ${elec.type ? `<tr><td><strong>Type/Model:</strong></td><td><span class="badge bg-secondary">${elec.type}</span></td></tr>` : ''}
                                                        ${elec.brand ? `<tr><td><strong>Brand:</strong></td><td><span class="text-info">${elec.brand}</span></td></tr>` : ''}
                                                        ${elec.location ? `<tr><td><strong>Location:</strong></td><td>${elec.location}</td></tr>` : ''}
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        ${elec.voltage ? `
                                                            <tr><td><strong>Voltage:</strong></td><td>
                                                                <span class="badge bg-info">${elec.voltage} ${elec.voltage_unit || 'V'}</span>
                                                            </td></tr>
                                                        ` : ''}
                                                        ${elec.ampere ? `
                                                            <tr><td><strong>Ampere:</strong></td><td>
                                                                <span class="badge bg-warning text-dark">${elec.ampere} A</span>
                                                            </td></tr>
                                                        ` : ''}
                                                        ${elec.daya ? `
                                                            <tr><td><strong>Daya:</strong></td><td>
                                                                <span class="badge bg-success">${elec.daya} ${elec.daya_unit || 'W'}</span>
                                                            </td></tr>
                                                        ` : ''}
                                                        ${elec.type_id ? `
                                                            <tr><td><strong>Type ID:</strong></td><td>
                                                                <span class="badge bg-outline-primary">${elec.type_id}</span>
                                                            </td></tr>
                                                        ` : ''}
                                                        <tr><td><strong>Gambar:</strong></td><td>
                                                            ${elec.image ? 
                                                                `<span class="text-success"><i class="fas fa-check-circle"></i> Tersedia</span>` : 
                                                                '<span class="text-muted"><i class="fas fa-times-circle"></i> Tidak ada</span>'
                                                            }
                                                        </td></tr>
                                                    </table>
                                                </div>
                                            </div>
                                            ${elec.image ? `
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <h6><i class="fas fa-image text-success"></i> Gambar Barang</h6>
                                                        <div class="text-center p-3 bg-white rounded border">
                                                            <img src="<?= base_url('assets/img/electric/'); ?>${elec.image}" 
                                                                 alt="${elec.nama || 'Gambar Barang'}" 
                                                                 class="img-thumbnail rounded shadow-sm" 
                                                                 style="max-height: 250px; max-width: 350px; cursor: pointer; transition: transform 0.2s;"
                                                                 onclick="showImageModal('<?= base_url('assets/img/electric/'); ?>${elec.image}', '${elec.nama || 'Gambar Barang'}')"
                                                                 onmouseover="this.style.transform='scale(1.05)'"
                                                                 onmouseout="this.style.transform='scale(1)'">
                                                            <div class="mt-2">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-search-plus"></i> Klik untuk memperbesar
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ` : `
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <h6><i class="fas fa-image text-muted"></i> Gambar Barang</h6>
                                                        <div class="text-center p-3 bg-light rounded border">
                                                            <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                                            <p class="text-muted mb-0">Tidak ada gambar tersedia</p>
                                                            <small class="text-muted">Tambahkan gambar saat edit barang</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            `}
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="card border-info">
                                                        <div class="card-header bg-info bg-opacity-10">
                                                            <small class="text-info fw-bold">
                                                                <i class="fas fa-info-circle"></i> Informasi Sistem
                                                            </small>
                                                        </div>
                                                        <div class="card-body py-2">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <small class="text-muted">
                                                                        <strong>Dibuat:</strong><br>
                                                                        <span class="text-primary">
                                                                            ${elec.created_at ? 
                                                                                new Date(elec.created_at).toLocaleDateString('id-ID', {
                                                                                    weekday: 'short',
                                                                                    year: 'numeric', 
                                                                                    month: 'short', 
                                                                                    day: 'numeric',
                                                                                    hour: '2-digit',
                                                                                    minute: '2-digit'
                                                                                }) : '-'
                                                                            }
                                                                        </span>
                                                                    </small>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <small class="text-muted">
                                                                        <strong>Diperbarui:</strong><br>
                                                                        <span class="text-success">
                                                                            ${elec.updated_at ? 
                                                                                new Date(elec.updated_at).toLocaleDateString('id-ID', {
                                                                                    weekday: 'short',
                                                                                    year: 'numeric', 
                                                                                    month: 'short', 
                                                                                    day: 'numeric',
                                                                                    hour: '2-digit',
                                                                                    minute: '2-digit'
                                                                                }) : '-'
                                                                            }
                                                                        </span>
                                                                    </small>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <small class="text-muted">
                                                                        <strong>Editor:</strong><br>
                                                                        <span class="text-warning">
                                                                            <i class="fas fa-user"></i> ${elec.editor || 'SYSTEM'}
                                                                        </span>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    // Add project/storage data if available
                    if (item.storage_data) {
                        html += `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6><i class="fas fa-database text-secondary"></i> Data Penyimpanan Tambahan</h6>
                                    <div class="card border-secondary">
                                        <div class="card-body">
                                            <pre class="bg-light p-3 rounded border mb-0" style="font-size: 12px;">${JSON.stringify(item.storage_data, null, 2)}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    document.getElementById('itemDetailsContent').innerHTML = html;
                } else {
                    document.getElementById('itemDetailsContent').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Gagal memuat detail barang: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('itemDetailsContent').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Terjadi kesalahan saat memuat detail barang.</div>';
                console.error('Error:', error);
            });

        var modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
        modal.show();
    }
    
    // =================================================================
    // PERBAIKAN: SCRIPT UNTUK AUTO-SCROLL SETELAH PENCARIAN
    // =================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil parameter dari URL
        const urlParams = new URLSearchParams(window.location.search);
        const keyword = urlParams.get('keyword');

        // Jika ada parameter 'keyword' dan tidak kosong
        if (keyword && keyword.trim() !== '') {
            // Cari elemen kartu inventaris
            const inventoryCard = document.getElementById('inventoryOverviewCard');
            
            // Jika elemen ditemukan, scroll ke elemen tersebut
            if (inventoryCard) {
                // Menggunakan setTimeout untuk memberi sedikit jeda agar animasi lebih terlihat
                setTimeout(() => {
                    inventoryCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 200);
            }
        }
    });
    
    function showImageModal(imageSrc, title) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModalTitle').textContent = title;
        var imageModal = new bootstrap.Modal(document.getElementById('imageViewModal'));
        imageModal.show();
    }

</script>
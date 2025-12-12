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
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-dark m-0">Lokasi Penyimpanan: <?= htmlspecialchars($location_id); ?></h3>
                <p class="text-muted mb-0">Barang yang disimpan di lokasi ini</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('storage/store?location=' . urlencode($location_id)); ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Simpan Barang
                </a>
                <a href="<?= site_url('storage/take?location=' . urlencode($location_id)); ?>" class="btn btn-warning">
                    <i class="fas fa-minus"></i> Ambil Barang
                </a>
                <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Overview
                </a>
            </div>
        </div>
    </div>

    <div class="card-body px-lg-5 px-4 py-4">

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Jenis Barang</h6>
                                <h3><?= count($storage_items); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Jumlah</h6>
                                <h3><?= array_sum(array_column($storage_items, 'amount')); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cubes fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Kategori</h6>
                                <h3><?= count(array_unique(array_column($storage_items, 'category'))); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tags fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Terakhir Diperbarui</h6>
                                <small>
                                    <?php
                                    $latest = '';
                                    if (!empty($storage_items)) {
                                        foreach ($storage_items as $item) {
                                            if ($item['updated_at'] > $latest) {
                                                $latest = $item['updated_at'];
                                            }
                                        }
                                    }
                                    echo $latest ? date('M d, Y', strtotime($latest)) : 'Tidak ada data';
                                    ?>
                                </small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <form method="get" action="" class="d-flex gap-2">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari barang di lokasi ini..." value="<?= htmlspecialchars($keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if (!empty($keyword)): ?>
                        <a href="<?= site_url('storage/location/' . rawurlencode($location_id)); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Barang di Penyimpanan</h5>
                        <div class="btn-group">
                            <a href="<?= site_url('storage/export_location_excel/' . $location_id); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Ekspor Excel
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($storage_items)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="storageTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kategori</th>
                                            <th>ID Tipe</th>
                                            <th>Jumlah</th>
                                            <th>Dibuat</th>
                                            <th>Terakhir Diperbarui</th>
                                            <th>Editor</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($storage_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($item['category']); ?></span>
                                                </td>
                                                <td>
                                                    <strong>
                                                        <?= htmlspecialchars($item['type_id']); ?>
                                                    </strong>
                                                    <?php if (strpos($item['type_id'], '_PROJ') !== false): ?>
                                                        <span class="badge bg-info ms-2" title="Barang untuk proyek">
                                                            <i class="fas fa-hard-hat me-1"></i>Project
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success fs-6"><?= number_format($item['amount']); ?></span>
                                                </td>
                                                <td>
                                                    <small><?= date('M d, Y H:i', strtotime($item['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <small><?= date('M d, Y H:i', strtotime($item['updated_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($item['editor_name'] ?? 'Tidak Diketahui'); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-success" onclick="quickStoreItem('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                            <i class="fas fa-plus"></i> Simpan
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning" onclick="quickTakeItem('<?= $item['category']; ?>', '<?= $item['type_id']; ?>', <?= $item['amount']; ?>)">
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
                            <div class="alert alert-info text-center">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <?php if (!empty($keyword)): ?>
                                    <h5>Tidak ada barang yang cocok dengan "<?= htmlspecialchars($keyword); ?>"</h5>
                                    <p>Coba gunakan kata kunci lain atau <a href="<?= site_url('storage/location/' . rawurlencode($location_id)); ?>">reset pencarian</a>.</p>
                                <?php else: ?>
                                    <h5>Tidak ada barang disimpan di lokasi ini</h5>
                                    <p>Mulai dengan <a href="<?= site_url('storage/store?location=' . urlencode($location_id)); ?>">menyimpan beberapa barang</a> di lokasi ini.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Transaksi Terbaru</h5>
                        <a href="<?= site_url('storage/reports?location=' . $location_id); ?>" class="btn btn-sm btn-outline-primary">
                            Lihat Semua Transaksi
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($location_transactions)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tanggal/Waktu</th>
                                            <th>Aksi</th>
                                            <th>Kategori</th>
                                            <th>ID Tipe</th>
                                            <th>Pengguna</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($location_transactions as $transaction): ?>
                                            <tr>
                                                <td><?= date('M d, Y H:i', strtotime($transaction['datetime'])); ?></td>
                                                <td>
                                                    <?php if ($transaction['action'] == 'store'): ?>
                                                        <span class="badge bg-success">Simpan</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Ambil</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($transaction['category']); ?></td>
                                                <td><?= htmlspecialchars($transaction['type_id']); ?></td>
                                                <td><?= htmlspecialchars($transaction['user_name'] ?? 'Tidak Diketahui'); ?></td>
                                                <td>
                                                    <?php if ($transaction['note']): ?>
                                                        <span class="text-muted" title="<?= htmlspecialchars($transaction['note']); ?>">
                                                            <?= strlen($transaction['note']) > 30 ? substr(htmlspecialchars($transaction['note']), 0, 30) . '...' : htmlspecialchars($transaction['note']); ?>
                                                        </span>
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
                                Belum ada transaksi tercatat untuk lokasi ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="quickActionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickActionTitle">Aksi Cepat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickActionForm">
                        <input type="hidden" id="actionType" name="action">
                        <input type="hidden" id="actionCategory" name="category">
                        <input type="hidden" id="actionTypeId" name="type_id">
                        <input type="hidden" name="location_id" value="<?= $location_id; ?>">
                        <div class="mb-3">
                            <label class="form-label">Barang:</label>
                            <div id="actionItemInfo" class="form-control-plaintext"></div>
                        </div>
                        <div class="mb-3" id="availableStockDiv" style="display: none;">
                            <label class="form-label">Stok Tersedia:</label>
                            <div id="actionAvailableStock" class="form-control-plaintext text-primary"></div>
                        </div>
                        <div class="mb-3">
                            <label for="actionQuantity" class="form-label">Jumlah:</label>
                            <input type="number" class="form-control" id="actionQuantity" name="quantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="actionNote" class="form-label">Catatan (Opsional):</label>
                            <textarea class="form-control" id="actionNote" name="note" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn" id="quickActionSubmit" onclick="submitQuickAction()">Aksi</button>
                </div>
            </div>
        </div>
    </div>

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
    // Script for auto-scroll
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        // Check if there is a 'keyword' parameter in the URL
        if (urlParams.get('keyword')) {
            const tableElement = document.getElementById('storageTable');
            if (tableElement) {
                // Smoothly scroll to the table element
                tableElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });

    function quickStoreItem(category, typeId) {
        document.getElementById('actionType').value = 'store';
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionItemInfo').textContent = category + ' - ' + typeId;
        document.getElementById('quickActionTitle').textContent = 'Simpan Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Simpan Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-success';
        document.getElementById('availableStockDiv').style.display = 'none';

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function quickTakeItem(category, typeId, availableStock) {
        document.getElementById('actionType').value = 'take';
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionItemInfo').textContent = category + ' - ' + typeId;
        document.getElementById('quickActionTitle').textContent = 'Ambil Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Ambil Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-warning';
        document.getElementById('availableStockDiv').style.display = 'block';
        document.getElementById('actionAvailableStock').textContent = availableStock + ' items';
        document.getElementById('actionQuantity').max = availableStock;

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function submitQuickAction() {
        const form = document.getElementById('quickActionForm');
        const formData = new FormData(form);

        fetch('<?= site_url('storage/quick_action'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }

    function viewItemDetails(category, typeId) {
        // Add loading indicator
        document.getElementById('itemDetailsContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat detail barang...</p>
            </div>
        `;
        
        fetch(`<?= site_url('storage/get_item_details'); ?>?location_id=<?= $location_id; ?>&category=${encodeURIComponent(category)}&type_id=${encodeURIComponent(typeId)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data); // Debug log
                
                if (data.success) {
                    const item = data.item;
                    
                    //  BUILD HTML CONTENT FOR PROJECT INDICATOR
                    let projectIndicatorHtml = '';
                    if (item.is_project) {
                        projectIndicatorHtml = `
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="fas fa-hard-hat fa-2x me-3"></i>
                            <div>
                                <h5 class="alert-heading mb-0">Barang Proyek</h5>
                                <small>Barang ini ditandai sebagai item yang digunakan untuk keperluan proyek.</small>
                            </div>
                        </div>
                        `;
                    }
                    
                    
                    let html = `
                    ${projectIndicatorHtml}
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-box text-primary"></i> Informasi Penyimpanan</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Lokasi:</strong></td><td>${item.location_id}</td></tr>
                                <tr><td><strong>Kategori:</strong></td><td><span class="badge bg-primary">${item.category}</span></td></tr>
                                <tr>
                                    <td><strong>ID Tipe:</strong></td>
                                    <td>
                                        <code>${item.type_id}</code>
                                        ${item.is_project ? '<span class="badge bg-info ms-2">Project</span>' : ''}
                                    </td>
                                </tr>
                                <tr><td><strong>Jumlah Stok:</strong></td><td><span class="badge bg-success fs-6">${item.amount}</span></td></tr>
                                <tr><td><strong>Editor:</strong></td><td>${item.editor_name || 'Tidak Diketahui'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-clock text-info"></i> Informasi Waktu</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Dibuat:</strong></td><td>${new Date(item.created_at).toLocaleString()}</td></tr>
                                <tr><td><strong>Diperbarui:</strong></td><td>${new Date(item.updated_at).toLocaleString()}</td></tr>
                            </table>
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
                    document.getElementById('itemDetailsContent').innerHTML = html;
                } else {
                    console.error('API Error:', data); // Debug log
                    document.getElementById('itemDetailsContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> 
                            Gagal memuat detail barang: ${data.message || 'Unknown error'}
                            <br><small>Category: ${category}, Type ID: ${typeId}</small>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error); // Debug log
                document.getElementById('itemDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> 
                        Terjadi kesalahan saat memuat detail barang.
                        <br><small>Error: ${error.message}</small>
                        <br><small>Category: ${category}, Type ID: ${typeId}</small>
                    </div>
                `;
            });

        var modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
        modal.show();
    }

    function manageItem(category, typeId) {
        // Use the same viewItemDetails function for manage functionality
        viewItemDetails(category, typeId);
    }
    
    function showImageModal(imageSrc, title) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModalTitle').textContent = title;
        var imageModal = new bootstrap.Modal(document.getElementById('imageViewModal'));
        imageModal.show();
    }
</script>
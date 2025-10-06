<?php if ($this->session->flashdata('action')) : ?>
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="card mx-auto rounded-5 shadow border-0 table-responsive mb-5" style="margin-top: 5rem; max-width: 95%;">
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-6">
                <div class="mb-2">
                    <a href="<?= site_url('electric/type'); ?>" class="btn btn-secondary rounded-pill px-4">Kembali ke Pilih Type</a>
                </div>
                <h3 class="m-0">Data Electrical</h3>
            </div>
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <form action="" method="post" class="flex-grow-1" id="search-form">
                        <div class="input-group">
                            <div class="position-relative flex-grow-1">
                                <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan ID, nama, type..." name="keyword" value="<?= isset($searchKeyword) ? htmlspecialchars($searchKeyword, ENT_QUOTES, 'UTF-8') : '' ?>" id="search-bar" autocomplete="off">
                            </div>
                            <input type="hidden" name="find" value="1">
                            <input type="hidden" name="filter" id="filter-input" value="<?= isset($filterKeyword) ? htmlspecialchars(json_encode($filterKeyword), ENT_QUOTES, 'UTF-8') : '' ?>">
                            <input type="hidden" name="sort" id="sort-input" value="<?= isset($sortKeyword) ? htmlspecialchars($sortKeyword[0] . '-' . $sortKeyword[1]) : '' ?>">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>
                        <div class="mt-2 d-flex gap-2">
                            <select class="form-select form-select-sm" id="name-filter" aria-label="Filter Nama">
                                <option value="">Semua Nama</option>
                                <?php foreach (($name_options ?? []) as $nama) : ?>
                                    <?php $selected = (!empty($filterKeyword['nama']) && in_array($nama, (array)$filterKeyword['nama'])) ? 'selected' : ''; ?>
                                    <option value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>" <?= $selected; ?>><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="form-select form-select-sm" id="type-filter" aria-label="Filter Type">
                                <option value="">Semua Type</option>
                                <?php foreach (($type_options ?? []) as $type) : ?>
                                    <?php $selected = (!empty($filterKeyword['type']) && in_array($type, (array)$filterKeyword['type'])) ? 'selected' : ''; ?>
                                    <option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" <?= $selected; ?>><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                    <?php
                    // Determine the query parameter for the main Add button based on the current filter
                    $topAddUrl = site_url('electric/add');
                    if (!empty($filterKeyword['type'])) {
                        // Get the first type from the filter (already in array form)
                        $firstType = is_array($filterKeyword['type']) ? $filterKeyword['type'][0] : $filterKeyword['type'];
                        $topAddUrl .= '?type=' . urlencode($firstType);
                    } elseif (!empty($filterKeyword['nama'])) {
                        $firstNama = is_array($filterKeyword['nama']) ? $filterKeyword['nama'][0] : $filterKeyword['nama'];
                        $topAddUrl .= '?type=' . urlencode($firstNama);
                    }
                    ?>
                    <a id="add-new-btn" href="<?= $topAddUrl; ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($electrics)) : ?>
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <p>Electric tidak ditemukan. Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
        </div>
    <?php else : ?>
        <?php
        $showBrand = $showVoltage = $showAmpere = $showDaya = $showLocation = false;
        foreach (($electrics ?? []) as $e) {
            if (!empty($e['brand'])) $showBrand = true;
            if (!empty($e['voltage'])) $showVoltage = true;
            if (!empty($e['ampere'])) $showAmpere = true;
            if (!empty($e['daya'])) $showDaya = true;
            if (!empty($e['location'])) $showLocation = true;
        }
        ?>
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0 align-middle">
                <thead>
                    <tr class="text-center">
                        <th>ID</th>
                        <?php if ($showBrand) : ?><th>Brand</th><?php endif; ?>
                        <th>Nama</th>
                        <th>Type</th>
                        <?php if ($showVoltage) : ?><th>Voltage</th><?php endif; ?>
                        <?php if ($showAmpere) : ?><th>Ampere</th><?php endif; ?>
                        <?php if ($showLocation) : ?><th>Location</th><?php endif; ?>
                        <th>Stok</th>
                        <th class="pe-lg-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($electrics as $electric) : ?>
                        <tr class="text-center">
                            <td>
                                <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($electric['electric_id']) ?>">
                                    <?= htmlspecialchars(substr($electric['electric_id'], 0, 15)) . (strlen($electric['electric_id']) > 15 ? '...' : '') ?>
                                </span>
                            </td>
                            <?php if ($showBrand) : ?><td><?= htmlspecialchars($electric['brand'] ?? '-') ?></td><?php endif; ?>
                            <td><?= htmlspecialchars($electric['nama']) ?></td>
                            <td><?= htmlspecialchars($electric['type']) ?></td>
                            <?php if ($showVoltage) : ?><td><?= htmlspecialchars($electric['voltage'] ?? '-') ?></td><?php endif; ?>
                            <?php if ($showAmpere) : ?><td><?= htmlspecialchars($electric['ampere'] ?? '-') ?></td><?php endif; ?>
                            <?php if ($showLocation) : ?><td><?= htmlspecialchars($electric['location'] ?? '-') ?></td><?php endif; ?>
                            <td><span class="badge bg-success fs-6"><?= $electric['total_stock'] ?? 0 ?></span></td>
                            
                            <td class="pe-lg-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-success" onclick="quickStoreItem('<?= htmlspecialchars($electric['nama']) ?>', '<?= htmlspecialchars($electric['electric_id']) ?>', '<?= htmlspecialchars($electric['location'] ?? '') ?>')">
                                        <i class="fas fa-plus"></i> Simpan
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" onclick="quickTakeItem('<?= htmlspecialchars($electric['nama']) ?>', '<?= htmlspecialchars($electric['electric_id']) ?>', '<?= htmlspecialchars($electric['location'] ?? '') ?>', <?= $electric['total_stock'] ?? 0 ?>)">
                                        <i class="fas fa-minus"></i> Ambil
                                    </button>
                                    <a href="<?= site_url('electric/edit/' . urlencode($electric['electric_id'])) ?>" class="btn <?= ($electric['total_stock'] ?? 0) > 0 ? 'btn-outline-secondary disabled' : 'btn-outline-primary' ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php  ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                 </tbody>
             </table>
         </div>
        <div class="card-footer bg-white border-0 px-lg-5 px-4 py-3">
             <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
                <div class="text-muted">Menampilkan <strong><?= $total_rows ?? 0 ?></strong> data</div>
                <?= $pagination['links'] ?? '' ?>
                <div class="btn-group">
                    <?php if (!empty($hasFilters)) : ?>
                        <form action="<?= site_url('electric/reset_session'); ?>" method="post" class="d-inline">
                            <button type="submit" class="btn btn-outline-secondary rounded-start-pill">Reset Filter</button>
                        </form>
                    <?php endif; ?>
                    <a href="<?= site_url('electric/download'); ?>" class="btn btn-primary <?= !empty($hasFilters) ? '' : 'rounded-start-pill' ?>">Download</a>
                    <button type="button" class="btn btn-primary rounded-end-pill" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    </div>

<div class="modal fade" id="quickActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickActionTitle">Aksi Cepat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickActionForm" onsubmit="submitQuickAction(); return false;">
                    <input type="hidden" id="actionType" name="action">
                    <input type="hidden" id="actionCategory" name="category">
                    <input type="hidden" id="actionTypeId" name="type_id">
                    <input type="hidden" id="actionLocationId" name="location_id">
                    <div class="mb-3">
                        <label class="form-label">Barang:</label>
                        <div id="actionItemInfo" class="form-control-plaintext fw-bold"></div>
                    </div>
                    <div class="mb-3" id="availableStockDiv" style="display: none;">
                        <label class="form-label">Stok Tersedia di Lokasi Ini:</label>
                        <div id="actionAvailableStock" class="form-control-plaintext text-primary fw-bold"></div>
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
                <button type="submit" form="quickActionForm" class="btn" id="quickActionSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi sortTable dan fungsi-fungsi lainnya tetap sama
    function sortTable(sortValue) { /* ... */ }

    function submitQuickAction() {
        const form = document.getElementById('quickActionForm');
        const submitButton = document.getElementById('quickActionSubmit');
        submitButton.disabled = true;

        fetch('<?= site_url('storage/quick_action'); ?>', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Terjadi kesalahan.'));
                submitButton.disabled = false;
            }
        })
        .catch(error => {
            alert('Terjadi error koneksi.');
            submitButton.disabled = false;
        });
    }

    function quickStoreItem(category, typeId, locationId) {
        document.getElementById('quickActionForm').reset();
        document.getElementById('actionType').value = 'store';
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionLocationId').value = locationId;
        document.getElementById('actionItemInfo').textContent = `${category} - ${typeId}`;
        
        document.getElementById('quickActionTitle').textContent = 'Simpan Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Simpan Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-success';
        document.getElementById('availableStockDiv').style.display = 'none';

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function quickTakeItem(category, typeId, locationId, availableStock) {
        document.getElementById('quickActionForm').reset();
        document.getElementById('actionType').value = 'take';
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionLocationId').value = locationId;
        document.getElementById('actionItemInfo').textContent = `${category} - ${typeId}`;

        document.getElementById('quickActionTitle').textContent = 'Ambil Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Ambil Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-warning';
        document.getElementById('availableStockDiv').style.display = 'block';
        document.getElementById('actionAvailableStock').textContent = availableStock + ' items';
        document.getElementById('actionQuantity').max = availableStock;

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<!-- Links for add actions now navigate normally; AJAX modal loader removed intentionally -->
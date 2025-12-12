<?php if ($this->session->flashdata('success')): ?>
    <!-- Flash Notification Alert -->
    <div class="cust-notification m-3">
        <div class="alert alert-success alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <!-- Flash Notification Alert -->
    <div class="cust-notification m-3">
        <div class="alert alert-danger alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-dark m-0">Ambil Barang</h3>
                <p class="text-muted mb-0">Hapus barang dari lokasi penyimpanan</p>
            </div>
            <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Penyimpanan
            </a>
        </div>
    </div>

    <!-- Card Body with Main Content -->
    <div class="card-body px-lg-5 px-4 py-4">

        <!-- Take Form -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h5 class="mb-4">Form Ambil Barang</h5>
                <?= form_open('storage/take', ['class' => 'needs-validation', 'novalidate' => '']); ?>

                <!-- Location ID (moved up to match store flow) -->
                <div class="mb-3">
                    <label for="location_id" class="form-label">ID Lokasi <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('location_id') ? 'is-invalid' : ''; ?>"
                        id="location_id" name="location_id" required>
                        <option value="">Pilih Lokasi</option>
                        <!-- Options will be populated on load, then refined when type/category change -->
                    </select>
                    <div class="form-text">Pilih lokasi untuk mengambil barang</div>
                    <?php if (form_error('location_id')): ?>
                        <div class="invalid-feedback"><?= form_error('location_id'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Category (will be filtered by selected location) -->
                <div class="mb-3">
                    <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('category') ? 'is-invalid' : ''; ?>"
                        id="category" name="category" required>
                        <option value="">Pilih Kategori</option>
                        <!-- Options populated from JS based on location -->
                    </select>
                    <?php if (form_error('category')): ?>
                        <div class="invalid-feedback"><?= form_error('category'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Type ID -->
                <div class="mb-3">
                    <label for="type_id" class="form-label">ID Tipe <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('type_id') ? 'is-invalid' : ''; ?>"
                        id="type_id" name="type_id" required>
                        <option value="">Pilih ID Tipe</option>
                        <!-- Options will be populated based on category + location -->
                    </select>
                    <div class="form-text">Pilih tipe/model spesifik dari barang</div>
                    <?php if (form_error('type_id')): ?>
                        <div class="invalid-feedback"><?= form_error('type_id'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Available Stock Display -->
                <div class="mb-3">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <small class="text-muted">Stok Tersedia: </small>
                            <strong id="availableStock" class="text-primary">-</strong>
                            <div id="projectIndicator" class="mt-1" style="display: none;">
                                <small class="badge bg-warning text-dark">
                                    <i class="fas fa-project-diagram"></i> Barang untuk Project
                                </small>
                            </div>
                            <div id="projectNotes" class="mt-2" style="display: none;">
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-sticky-note"></i> <strong>Project Batches Available:</strong>
                                    </small>
                                </div>
                                <div id="projectNotesText"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Batch Selection (only for project items) -->
                <div class="mb-3" id="batchSelectionField" style="display: none;">
                    <label for="batch_id" class="form-label">Pilih Batch Project <span class="text-danger">*</span></label>
                    <select class="form-select" id="batch_id" name="batch_id" onchange="updateBatchQuantity()">
                        <option value="">Pilih batch yang akan diambil</option>
                        <!-- Options will be populated via JavaScript -->
                    </select>
                    <div class="form-text">Pilih batch project spesifik untuk diambil barangnya</div>
                </div>

                <!-- Quantity -->
                <div class="mb-3">
                    <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?= form_error('quantity') ? 'is-invalid' : ''; ?>"
                        id="quantity" name="quantity" value="<?= set_value('quantity'); ?>"
                        min="1" step="1" placeholder="Masukkan jumlah yang akan diambil" required>
                    <div class="form-text">Masukkan jumlah barang yang akan diambil</div>
                    <?php if (form_error('quantity')): ?>
                        <div class="invalid-feedback"><?= form_error('quantity'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Note -->
                <div class="mb-3">
                    <label for="note" class="form-label">Catatan (Opsional)</label>
                    <textarea class="form-control <?= form_error('note') ? 'is-invalid' : ''; ?>"
                        id="note" name="note" rows="3" maxlength="255"
                        placeholder="Tambahkan catatan tambahan tentang operasi pengambilan ini"><?= set_value('note'); ?></textarea>
                    <div class="form-text">Catatan opsional tentang operasi pengambilan</div>
                    <?php if (form_error('note')): ?>
                        <div class="invalid-feedback"><?= form_error('note'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-minus"></i> Ambil Barang
                    </button>
                </div>

                <?= form_close(); ?>
            </div>
        </div>

        <!-- Available Items Preview -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Barang Tersedia di Penyimpanan</h6>
                    </div>
                    <div class="card-body">
                        <div id="availableItemsPreview">
                            <div class="text-muted">Pilih kategori untuk melihat barang yang tersedia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Storage items data from PHP
    const storageItems = <?= json_encode($storage_items ?? []); ?>;

    // Populate initial location options (unique locations) on load
    function populateInitialLocations() {
        const locationSelect = document.getElementById('location_id');
        // Get the preselected location passed from the controller
        const preselectedLocation = '<?= $preselected_location ?? ''; ?>';

        // Gather unique location ids from all storage items
        const locSet = new Set();
        storageItems.forEach(item => {
            if (item.location_id) locSet.add(item.location_id);
        });

        // Clear and add default option
        locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';

        locSet.forEach(loc => {
            const option = document.createElement('option');
            option.value = loc;
            option.textContent = loc;
            
            // Logic to pre-select the location
            // It is selected if:
            // 1. It was the old value from a failed validation.
            // 2. OR if there's no old value, it matches the preselected_location from the URL.
            const oldVal = '<?= set_value('location_id'); ?>';
            if (oldVal === loc || (!oldVal && preselectedLocation === loc)) {
                option.selected = true;
            }
            
            locationSelect.appendChild(option);
        });
    }

    // When a location is chosen, filter categories available at that location
    function filterCategoriesByLocation() {
        // Accept and normalize possible compound location values (e.g. "LOC1_project")
        const rawLoc = document.getElementById('location_id').value;
        const loc = rawLoc ? (rawLoc.endsWith('_project') ? rawLoc.slice(0, -8) : rawLoc) : rawLoc;
        const categorySelect = document.getElementById('category');

        categorySelect.innerHTML = '<option value="">Pilih Kategori</option>';

        if (!loc) {
            // Clear type as well
            document.getElementById('type_id').innerHTML = '<option value="">Pilih ID Tipe</option>';
            document.getElementById('availableItemsPreview').innerHTML = '<div class="text-muted">Pilih lokasi untuk melihat kategori yang tersedia</div>';
            return;
        }

        const filtered = storageItems.filter(item => item.location_id === loc);
        const catSet = new Set();
        filtered.forEach(it => { if (it.category) catSet.add(it.category); });

        const cats = Array.from(catSet).sort();
        cats.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            if (cat === '<?= set_value('category'); ?>') option.selected = true;
            categorySelect.appendChild(option);
        });

        // After categories set, update types
        updateAvailableTypes();
        updateAvailableItemsPreview();
    }

    // Populate types based on selected category + location
    function updateAvailableTypes() {
        const category = document.getElementById('category').value;
        const loc = document.getElementById('location_id').value;
        const typeSelect = document.getElementById('type_id');

        typeSelect.innerHTML = '<option value="">Pilih ID Tipe</option>';

        if (!category || !loc) return;

        const filtered = storageItems.filter(item => {
            if (!item.type_id) return false;
            // category match
            if (item.category !== category) return false;
            if (item.location_id !== loc) return false;
            return true;
        });

        // Unique base type ids (remove _PROJECT)
        const types = [...new Set(filtered.map(i => i.type_id.replace('_PROJECT', '')))];

        types.forEach(t => {
            const option = document.createElement('option');
            option.value = t;
            option.textContent = t;
            if (t === '<?= set_value('type_id'); ?>') option.selected = true;
            typeSelect.appendChild(option);
        });

        // After types set, refine location options to include project variants if needed
        updateLocationOptions();
    }

    // Refine location options based on category + type (adds project vs non-project distinction)
    function updateLocationOptions() {
        const category = document.getElementById('category').value;
        const typeId = document.getElementById('type_id').value;
        const locationSelect = document.getElementById('location_id');

        // If type or category empty, don't override initial location list
        if (!category || !typeId) return;

        // Preserve previous selection so we can restore it if still applicable
        const prevValue = locationSelect.value || '';
        const prevBase = prevValue.endsWith('_project') ? prevValue.slice(0, -8) : prevValue;

        // Build options per matching storage items
        const filteredItems = storageItems.filter(item =>
            item.category === category &&
            (item.type_id === typeId || item.type_id === typeId + '_PROJECT') &&
            parseInt(item.amount) > 0
        );

        if (filteredItems.length === 0) return;

        // replace location options with refined ones (compound values for project)
        locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';

        filteredItems.forEach(item => {
            const isProjectItem = item.type_id.endsWith('_PROJECT');
            const optValue = isProjectItem ? `${item.location_id}_project` : item.location_id;
            const option = document.createElement('option');
            option.value = optValue;
            option.textContent = `${item.location_id} (Stok: ${item.amount})${isProjectItem ? ' - Project' : ''}`;

            // Restore selection if this option corresponds to previous selection (either exact or base match)
            if (prevValue && (option.value === prevValue || option.value === prevBase)) {
                option.selected = true;
            }

            // Also attempt to match server-side old value if present
            if (!option.selected && option.value === '<?= set_value('location_id'); ?>') {
                option.selected = true;
            }

            locationSelect.appendChild(option);
        });
    }

    // Existing take logic for stock, projects and batches (kept largely intact)
    function updateAvailableStock() {
        const category = document.getElementById('category').value;
        const typeId = document.getElementById('type_id').value;
        const locationValue = document.getElementById('location_id').value;

        if (category && typeId && locationValue) {
            const isProject = locationValue.endsWith('_project');
            const locationId = isProject ? locationValue.slice(0, -8) : locationValue;

            const item = storageItems.find(item => {
                if (item.category !== category || item.location_id !== locationId) return false;
                const itemIsProject = item.type_id.endsWith('_PROJECT');
                return itemIsProject === isProject && item.type_id.replace('_PROJECT', '') === typeId;
            });

            if (item) {
                const stock = parseInt(item.amount);
                document.getElementById('availableStock').textContent = stock + ' barang';

                const projectIndicator = document.getElementById('projectIndicator');
                projectIndicator.style.display = isProject ? 'block' : 'none';

                if (isProject) {
                    const dbTypeId = typeId + '_PROJECT';
                    fetchProjectNotes(category, dbTypeId, locationId);
                } else {
                    hideProjectNotes();
                }

                const quantityInput = document.getElementById('quantity');
                quantityInput.max = stock;

                const stockDisplay = document.getElementById('availableStock');
                if (stock <= 2) {
                    stockDisplay.className = 'text-danger';
                } else if (stock <= 5) {
                    stockDisplay.className = 'text-warning';
                } else {
                    stockDisplay.className = 'text-primary';
                }
            }
        } else {
            document.getElementById('availableStock').textContent = '-';
            document.getElementById('projectIndicator').style.display = 'none';
            hideProjectNotes();
        }
    }

    // reuse existing project/batch functions from original take form
    function fetchProjectNotes(category, typeId, locationId) {
        fetch(`<?= site_url('storage/get_item_details'); ?>?category=${category}&type_id=${typeId}&location_id=${locationId}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.project_batches && data.project_batches.length > 0) {
                    displayProjectBatches(data.project_batches);
                    document.getElementById('projectNotes').style.display = 'block';
                } else {
                    hideProjectNotes();
                }
            })
            .catch(error => {
                console.error('Error fetching project notes:', error);
                hideProjectNotes();
            });
    }

    function displayProjectBatches(batches) {
        let html = '<div class="project-batches">';
        window.availableBatches = batches;

        const batchSelectionField = document.getElementById('batchSelectionField');
        const batchSelect = document.getElementById('batch_id');

        if (batches.length > 0) {
            batchSelectionField.style.display = 'block';
            batchSelect.required = true;
            batchSelect.innerHTML = '<option value="">Pilih batch yang akan diambil</option>';

            batches.forEach((batch, index) => {
                const option = document.createElement('option');
                option.value = batch.batch_id;
                option.textContent = `${batch.project_name || 'Unnamed Project'} (${batch.remaining_quantity} tersedia)`;
                option.dataset.remainingQuantity = batch.remaining_quantity;
                batchSelect.appendChild(option);

                html += `
                    <div class="batch-item mb-2 p-2 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${batch.project_name || 'Unnamed Project'}</strong>
                                <small class="text-muted d-block">Batch: ${batch.batch_id}</small>
                                <small class="text-muted d-block">Available: ${batch.remaining_quantity} / ${batch.batch_quantity}</small>
                                <small class="text-muted d-block">Created: ${new Date(batch.created_at).toLocaleDateString()}</small>
                                <small class="text-muted d-block">By: ${batch.created_by_name}</small>
                            </div>
                            <div class="text-end">
                                <small class="badge bg-info">Batch ${index + 1}</small>
                            </div>
                        </div>
                        ${batch.project_notes ? `
                            <div class="mt-2">
                                <small><strong>Notes:</strong> ${batch.project_notes}</small>
                            </div>
                        ` : ''}
                    </div>
                `;
            });
        } else {
            batchSelectionField.style.display = 'none';
            batchSelect.required = false;
        }

        html += '</div>';
        document.getElementById('projectNotesText').innerHTML = html;
    }

    function validateBatchQuantity() {
        const batchSelect = document.getElementById('batch_id');
        const selectedOption = batchSelect.selectedOptions[0];
        const quantityInput = document.getElementById('quantity');
        const enteredQuantity = parseInt(quantityInput.value);

        if (selectedOption && selectedOption.dataset.remainingQuantity && enteredQuantity) {
            const maxQuantity = parseInt(selectedOption.dataset.remainingQuantity);
            if (enteredQuantity > maxQuantity) {
                quantityInput.setCustomValidity(`Maksimum ${maxQuantity} barang tersedia dari batch ini`);
            } else {
                quantityInput.setCustomValidity('');
            }
        } else {
            quantityInput.setCustomValidity('');
        }
    }

    function updateBatchQuantity() {
        const batchSelect = document.getElementById('batch_id');
        const selectedOption = batchSelect.selectedOptions[0];
        const quantityInput = document.getElementById('quantity');

        if (selectedOption && selectedOption.dataset.remainingQuantity) {
            const maxQuantity = parseInt(selectedOption.dataset.remainingQuantity);
            quantityInput.max = maxQuantity;
            quantityInput.value = '';
            quantityInput.placeholder = `Max: ${maxQuantity} barang`;

            document.getElementById('availableStock').textContent = `${maxQuantity} barang (dari batch terpilih)`;
            document.getElementById('availableStock').className = maxQuantity <= 5 ? 'text-warning' : 'text-primary';
        } else {
            quantityInput.max = '';
            quantityInput.placeholder = 'Masukkan jumlah yang akan diambil';
        }
    }

    function hideProjectNotes() {
        document.getElementById('projectNotes').style.display = 'none';
        document.getElementById('projectNotesText').textContent = '';
        document.getElementById('batchSelectionField').style.display = 'none';
        document.getElementById('batch_id').required = false;
        window.availableBatches = null;
    }

    // Event listeners
    document.getElementById('location_id').addEventListener('change', function() {
        // If a compound value (with _project) is selected, keep it; otherwise treat as base location
        filterCategoriesByLocation();
        updateAvailableStock();
    });
    document.getElementById('category').addEventListener('change', function() {
        updateAvailableTypes();
        updateAvailableStock();
    });
    document.getElementById('type_id').addEventListener('change', function() {
        updateLocationOptions();
        updateAvailableStock();
    });

    // Validate quantity against available stock
    document.getElementById('quantity').addEventListener('input', function() {
        const maxStock = parseInt(this.max);
        const enteredQuantity = parseInt(this.value);

        if (maxStock && enteredQuantity > maxStock) {
            this.setCustomValidity(`Stok maksimum yang tersedia adalah ${maxStock}`);
        } else {
            this.setCustomValidity('');
        }
    });

    // Initialize on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        populateInitialLocations();

        // If previous selections exist, try to restore the flow
        const locationVal = document.getElementById('location_id').value;
        if (locationVal) filterCategoriesByLocation();

        const categoryVal = document.getElementById('category').value;
        if (categoryVal) updateAvailableTypes();

        const typeVal = document.getElementById('type_id').value;
        if (typeVal) updateLocationOptions();

        const locationId = document.getElementById('location_id').value;
        if (locationId) updateAvailableStock();
    });
</script>
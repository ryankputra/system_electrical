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
                <h3 class="text-dark m-0">Simpan Barang</h3>
                <p class="text-muted mb-0">Tambah barang ke lokasi penyimpanan</p>
            </div>
            <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Penyimpanan
            </a>
        </div>
    </div>

    <div class="card-body px-lg-5 px-4 py-4">

        <div class="row justify-content-center">
            <div class="col-md-8">
                <h5 class="mb-4">Form Simpan Barang</h5>
                <?= form_open('storage/store', ['class' => 'needs-validation', 'novalidate' => '']); ?>

                <div class="mb-3">
                    <label for="location_id" class="form-label">Lokasi <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('location_id') ? 'is-invalid' : ''; ?>" id="location_id" name="location_id" required>
                        <option value="">Pilih Lokasi</option>
                        <?php foreach (($locations ?? []) as $loc) : ?>
                            <?php if ($loc !== null && $loc !== ''): ?>
                                <?php
                                    $isSelected = (set_value('location_id') == $loc) || (!set_value('location_id') && isset($preselected_location) && $preselected_location == $loc);
                                ?>
                                <option value="<?= htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?>" <?= $isSelected ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Pilih lokasi penyimpanan dari daftar (diambil dari kolom lokasi tabel `as_electric`).</div>
                    <?php if (form_error('location_id')): ?>
                        <div class="invalid-feedback"><?= form_error('location_id'); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('category') ? 'is-invalid' : ''; ?>"
                        id="category" name="category" required>
                        <option value="">Pilih Kategori</option>
                        </select>
                    <?php if (form_error('category')): ?>
                        <div class="invalid-feedback"><?= form_error('category'); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="type_id" class="form-label">ID Tipe <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('type_id') ? 'is-invalid' : ''; ?>"
                        id="type_id" name="type_id" required>
                        <option value="">Pilih ID Tipe</option>
                        </select>
                    <div class="form-text">Pilih tipe/model spesifik dari barang</div>
                    <?php if (form_error('type_id')): ?>
                        <div class="invalid-feedback"><?= form_error('type_id'); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?= form_error('quantity') ? 'is-invalid' : ''; ?>"
                        id="quantity" name="quantity" value="<?= set_value('quantity'); ?>"
                        min="1" step="1" placeholder="Masukkan jumlah" required>
                    <div class="form-text">Masukkan jumlah barang yang akan disimpan</div>
                    <?php if (form_error('quantity')): ?>
                        <div class="invalid-feedback"><?= form_error('quantity'); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="note" class="form-label">Catatan (Opsional)</label>
                    <textarea class="form-control <?= form_error('note') ? 'is-invalid' : ''; ?>"
                        id="note" name="note" rows="3" maxlength="255"
                        placeholder="Tambahkan catatan tambahan tentang operasi penyimpanan ini"><?= set_value('note'); ?></textarea>
                    <div class="form-text">Catatan opsional tentang operasi penyimpanan</div>
                    <?php if (form_error('note')): ?>
                        <div class="invalid-feedback"><?= form_error('note'); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_project_item" name="is_project_item" value="1" <?= set_checkbox('is_project_item', '1'); ?> onchange="toggleProjectFields()">
                        <label class="form-check-label" for="is_project_item">
                            Barang untuk Project
                        </label>
                        <div class="form-text">Centang jika barang ini disimpan untuk keperluan project tertentu</div>
                    </div>
                </div>

                <div class="mb-3" id="projectNameField" style="display: none;">
                    <label for="project_name" class="form-label">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('project_name') ? 'is-invalid' : ''; ?>"
                        id="project_name" name="project_name" value="<?= set_value('project_name'); ?>"
                        maxlength="100" placeholder="Masukkan nama project">
                    <div class="form-text">Nama project untuk identifikasi batch barang ini</div>
                    <?php if (form_error('project_name')): ?>
                        <div class="invalid-feedback"><?= form_error('project_name'); ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Barang
                    </button>
                </div>

                <?= form_close(); ?>
            </div>
        </div>

    </div>
</div>

<script>
    // Data passed from PHP.
    const currentStorage = <?= json_encode($current_storage ?? []); ?>;
    const electricItems = <?= json_encode($electric_items ?? []); ?>;
    const allCategories = <?= json_encode($electric_categories ?? []); ?>;

    /**
     * Filters the Category dropdown based on the selected Location.
     */
    function filterCategoriesByLocation() {
        const loc = document.getElementById('location_id').value;
        const categorySelect = document.getElementById('category');
        categorySelect.innerHTML = '<option value="">Pilih Kategori</option>';

        if (!loc) {
            updateTypeOptions();
            return;
        }

        const categoriesInLocation = new Set();
        electricItems.forEach(item => {
            if (item.location && item.location.trim() === loc.trim() && item.nama) {
                categoriesInLocation.add(item.nama);
            }
        });

        let categoriesToShow = categoriesInLocation.size > 0 ? [...categoriesInLocation].sort() : allCategories;

        categoriesToShow.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            if (cat === '<?= set_value('category'); ?>') {
                option.selected = true;
            }
            categorySelect.appendChild(option);
        });
        
        updateTypeOptions();
    }


    /**
     * Filters the Type ID dropdown based on selected Location and Category.
     * It only shows items that DO NOT already exist in the selected location.
     */
    function updateTypeOptions() {
        const category = document.getElementById('category').value;
        const loc = document.getElementById('location_id').value;
        const typeSelect = document.getElementById('type_id');
        typeSelect.innerHTML = '<option value="">Pilih ID Tipe</option>';

        if (!loc || !category) {
            return;
        }

        // FINAL FIX: Compare location_id by trimming and converting to uppercase
        // to prevent mismatches due to whitespace or case differences.
        const existingTypeIdsInLocation = new Set(
            currentStorage
                .filter(item => item.location_id && item.location_id.trim().toUpperCase() === loc.trim().toUpperCase())
                .map(item => item.type_id.replace('_PROJECT', ''))
        );

        const itemsForCategory = electricItems.filter(item => 
            item.nama && item.nama.toLowerCase() === category.toLowerCase()
        );

        const availableItems = itemsForCategory.filter(item => 
            !existingTypeIdsInLocation.has(item.electric_id)
        );

        if (availableItems.length > 0) {
            availableItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.electric_id || '';

                let displayText = item.electric_id;
                const parts = [];
                if (item.type) parts.push(item.type);
                if (item.voltage) parts.push(item.voltage + (item.voltage_unit || ''));
                if (item.ampere) parts.push(item.ampere + ' A');

                if (parts.length > 0) {
                    displayText += ' - ' + parts.join(' - ');
                }
                
                option.textContent = displayText;
                if (item.electric_id === '<?= set_value('type_id'); ?>') {
                    option.selected = true;
                }
                typeSelect.appendChild(option);
            });
        } else {
            const option = document.createElement('option');
            option.disabled = true;
            option.textContent = 'Semua tipe untuk kategori ini sudah ada di lokasi';
            typeSelect.appendChild(option);
        }
    }
    
    /**
     * Toggles the visibility of the project name field.
     */
    function toggleProjectFields() {
        const isChecked = document.getElementById('is_project_item').checked;
        const projectNameField = document.getElementById('projectNameField');
        const projectNameInput = document.getElementById('project_name');

        if (isChecked) {
            projectNameField.style.display = 'block';
            projectNameInput.required = true;
        } else {
            projectNameField.style.display = 'none';
            projectNameInput.required = false;
            projectNameInput.value = '';
        }
    }

    // Attach event listeners after the DOM is fully loaded.
    document.addEventListener('DOMContentLoaded', function() {
        const locSel = document.getElementById('location_id');
        if (locSel) {
            locSel.addEventListener('change', filterCategoriesByLocation);
        }

        const catSel = document.getElementById('category');
        if (catSel) {
            catSel.addEventListener('change', updateTypeOptions);
        }

        toggleProjectFields();
        if (locSel && locSel.value) {
            filterCategoriesByLocation();
        }
    });

    // Standard Bootstrap form validation script.
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
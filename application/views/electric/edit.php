<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Edit Electrical</h4>

            <!-- Back Button-->
            <a href="<?= site_url('electric'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="<?= site_url('electric/edit/' . urlencode($electric['electric_id'])); ?>" method="post" enctype="multipart/form-data">
            <!-- Hidden type_id field -->
            <input type="hidden" name="type_id" value="<?= $electric['type_id'] ?? '' ?>">
            
            <!-- Electrical ID Display Section (Read-only) -->
            <div class="mb-2">
                <label for="electric_id" class="form-label">Electrical ID (Auto-generated)</label>
                <input id="electric_id" type="text" class="form-control rounded-pill" name="electric_id" value="<?= $electric['electric_id'] ?? '' ?>" readonly>
                <small class="text-muted">ID akan diperbarui secara otomatis jika nama, type, voltage, ampere, atau power diubah</small>
            </div>

            <!-- Brand Input Section -->
            <div class="mb-2">
                <label for="brand" class="form-label">Brand <small class="text-muted">(opsional)</small></label>
                <div class="position-relative">
                    <input id="brand" type="text" class="form-control rounded-pill pe-5 <?= form_error('brand') ? 'is-invalid' : '' ?>" name="brand" placeholder="Contoh: OMRON, SCHNEIDER, SIEMENS, ABB" value="<?= set_value('brand') ? set_value('brand') : ($electric['brand'] ?? ''); ?>" onkeyup="toggleClear('brand', 'clear-button-brand')" autocomplete="off" maxlength="50" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-brand" onclick="clearInput('brand', 'clear-button-brand')" aria-hidden="true">
                    <?= form_error('brand', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
                <div class="form-text text-muted">
                    <small>Brand/merek perangkat electrical. Maksimal 50 karakter.</small>
                </div>
            </div>

            <!-- Nama Input Section -->
            <div class="mb-2">
                <label for="nama" class="form-label">Nama</label>
                <div class="position-relative">
                    <input id="nama" type="text" class="form-control rounded-pill pe-5 <?= form_error('nama') ? 'is-invalid' : '' ?>" name="nama" placeholder="Nama perangkat" value="<?= set_value('nama') ? set_value('nama') : ($electric['nama'] ?? ''); ?>" onkeyup="toggleClear('nama', 'clear-button-nama')" autocomplete="off" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-nama" onclick="clearInput('nama', 'clear-button-nama')" aria-hidden="true">
                    <?= form_error('nama', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Type Input Section -->
            <div class="mb-2">
                <label for="type" class="form-label">Type</label>
                <div class="position-relative">
                    <input id="type" type="text" class="form-control rounded-pill pe-5 <?= form_error('type') ? 'is-invalid' : '' ?>" name="type" placeholder="Contoh: TK45-14SN, CT45-2D4T, RV5531-08GA4, SD-500-24..." 
                           value="<?= set_value('type') ? set_value('type') : ($electric['type'] ?? ''); ?>" 
                           onkeyup="toggleClear('type', 'clear-button-type')" autocomplete="off" maxlength="50" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-type" onclick="clearInput('type', 'clear-button-type')" aria-hidden="true">
                    <?= form_error('type', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
                <div class="form-text text-muted">
                    <small>Ketik model/type electrical sesuai dengan data yang ada. Maksimal 50 karakter.</small>
                </div>
            </div>

            <!-- Voltage Input Section -->
            <div class="mb-2">
                <label for="voltage" class="form-label">Voltage <small class="text-muted">(bisa rentang misal 4-30)</small></label>
                <div class="position-relative d-flex align-items-center">
                    <input id="voltage" type="text" class="form-control rounded-pill pe-5 <?= form_error('voltage') ? 'is-invalid' : '' ?>" name="voltage" placeholder="Contoh: 220 atau 4-30" value="<?= set_value('voltage') ? set_value('voltage') : ($electric['voltage'] ?? ''); ?>" onkeyup="toggleClear('voltage', 'clear-button-voltage')" autocomplete="off">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-voltage" onclick="clearInput('voltage', 'clear-button-voltage')" aria-hidden="true">
                    <select id="voltage_unit" name="voltage_unit" class="form-select form-select-sm ms-2" style="width:110px;">
                        <option value="V" <?= set_select('voltage_unit', 'V', (set_value('voltage_unit') ? set_value('voltage_unit') : ($electric['voltage_unit'] ?? '')) == 'V') ?>>V</option>
                        <option value="VAC" <?= set_select('voltage_unit', 'VAC', (set_value('voltage_unit') ? set_value('voltage_unit') : ($electric['voltage_unit'] ?? '')) == 'VAC') ?>>VAC</option>
                        <option value="VDC" <?= set_select('voltage_unit', 'VDC', (set_value('voltage_unit') ? set_value('voltage_unit') : ($electric['voltage_unit'] ?? '')) == 'VDC') ?>>VDC</option>
                    </select>
                </div>
                <div class="form-text">Bisa input satu nilai atau rentang, contoh: 220 atau 4-30</div>
                <?= form_error('voltage', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>

            <!-- Ampere Input Section -->
            <div class="mb-2">
                <label for="ampere" class="form-label">Ampere (A)</label>
                <div class="position-relative">
                    <input id="ampere" type="number" step="any" class="form-control rounded-pill pe-5 <?= form_error('ampere') ? 'is-invalid' : '' ?>" name="ampere" placeholder="1.5" value="<?= set_value('ampere') ? set_value('ampere') : ($electric['ampere'] ?? ''); ?>" onkeyup="toggleClear('ampere', 'clear-button-ampere')" autocomplete="off" min="0">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-ampere" onclick="clearInput('ampere', 'clear-button-ampere')" aria-hidden="true">
                    <?= form_error('ampere', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Power Input Section -->
            <div class="mb-2">
                <label for="daya" class="form-label">Daya</label>
                <div class="position-relative d-flex align-items-center">
                    <input id="daya" type="number" step="any" class="form-control rounded-pill pe-5 <?= form_error('daya') ? 'is-invalid' : '' ?>" name="daya" placeholder="330" value="<?= set_value('daya') ? set_value('daya') : ($electric['daya'] ?? ''); ?>" onkeyup="toggleClear('daya', 'clear-button-daya')" autocomplete="off" min="0">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-daya" onclick="clearInput('daya', 'clear-button-daya')" aria-hidden="true">
                    <!-- Daya unit selector -->
                    <select id="daya_unit" name="daya_unit" class="form-select form-select-sm ms-2" style="width:90px;">
                        <option value="">Unit</option>
                        <option value="W" <?= set_select('daya_unit', 'W', ($electric['daya_unit'] ?? '') === 'W'); ?>>W</option>
                        <option value="V" <?= set_select('daya_unit', 'V', ($electric['daya_unit'] ?? '') === 'V'); ?>>V</option>
                    </select>
                    <?= form_error('daya', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Location Input Section -->
            <div class="mb-2">
                <label for="location" class="form-label">Location <small class="text-muted">(opsional)</small></label>
                <div class="position-relative">
                    <input id="location" type="text" class="form-control rounded-pill pe-5 <?= form_error('location') ? 'is-invalid' : '' ?>" name="location" placeholder="Contoh: RAK E2, RAK E3, RAK E5" value="<?= set_value('location') ? set_value('location') : ($electric['location'] ?? ''); ?>" onkeyup="toggleClear('location', 'clear-button-location')" autocomplete="off" maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-location" onclick="clearInput('location', 'clear-button-location')" aria-hidden="true">
                    <?= form_error('location', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
                <div class="form-text text-muted">
                    <small>Lokasi penyimpanan electrical. Maksimal 100 karakter.</small>
                </div>
            </div>

            <!-- Image Upload Section -->
            <div class="mb-3">
                <label for="image" class="form-label">Gambar <small class="text-muted">(opsional)</small></label>
                
                <?php if (!empty($electric['image'])): ?>
                    <!-- Current Image Display -->
                    <div class="mb-2">
                        <small class="text-muted">Gambar saat ini:</small><br>
                        <div class="position-relative d-inline-block">
                            <img src="<?= base_url('assets/img/electric/' . $electric['image']); ?>" 
                                 alt="Current Image" 
                                 class="img-thumbnail rounded shadow" 
                                 style="max-height: 150px; max-width: 200px; cursor: pointer;"
                                 onclick="showImageModal('<?= base_url('assets/img/electric/' . $electric['image']); ?>', '<?= $electric['nama'] ?? 'Gambar Current'; ?>')">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" 
                                    style="width: 25px; height: 25px; padding: 0; margin: -5px;"
                                    onclick="removeCurrentImage()"
                                    title="Hapus gambar">
                                <i class="fas fa-times" style="font-size: 12px;"></i>
                            </button>
                        </div>
                        <input type="hidden" name="remove_image" id="remove_image" value="0">
                        <div class="form-text">
                            <small class="text-info">Klik gambar untuk memperbesar, atau klik (×) untuk menghapus</small>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Upload New Image -->
                <input id="image" type="file" name="image" accept="image/*" class="form-control" onchange="previewImage(this)">
                <div class="form-text">Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal 2MB</div>
                
                <!-- Image Preview -->
                <div id="imagePreview" class="mt-2" style="display: none;">
                    <small class="text-muted">Preview gambar baru:</small><br>
                    <img id="previewImg" src="" alt="Preview" class="img-thumbnail rounded shadow" style="max-height: 150px;">
                    <div class="mt-1">
                        <small class="text-success">Preview gambar yang akan diupload</small>
                    </div>
                </div>
            </div>

            <!-- Save and Delete Button -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                <a href="<?= site_url('electric/delete/') . urlencode($electric['electric_id'] ?? ''); ?>" onclick="return confirm('Apakah anda yakin ingin menghapus electric ini?')" class="btn btn-danger rounded-pill">Hapus</a>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Input-clear configuration mapping for form inputs with clear buttons.
     * 
     * @type {Array<{id: string, button: string}>}
     */
    window.inputConfigs = [{
            id: 'brand',
            button: 'clear-button-brand'
        },
        {
            id: 'nama',
            button: 'clear-button-nama'
        },
        {
            id: 'type',
            button: 'clear-button-type'
        },
        {
            id: 'location',
            button: 'clear-button-location'
        },
        {
            id: 'voltage',
            button: 'clear-button-voltage'
        },
        {
            id: 'ampere',
            button: 'clear-button-ampere'
        },
        {
            id: 'daya',
            button: 'clear-button-daya'
        }
    ];

    // Image preview function
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Check file size (2MB = 2048KB)
            if (file.size > 2048 * 1024) {
                alert('Ukuran file terlalu besar! Maksimal 2MB');
                input.value = '';
                preview.style.display = 'none';
                return;
            }
            
            // Check file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Format file tidak didukung! Gunakan JPG, JPEG, PNG, atau GIF');
                input.value = '';
                preview.style.display = 'none';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    }
    
    // Remove current image function
    function removeCurrentImage() {
        if (confirm('Apakah Anda yakin ingin menghapus gambar ini?')) {
            document.getElementById('remove_image').value = '1';
            // Hide current image display
            const currentImageDiv = document.querySelector('.position-relative.d-inline-block');
            if (currentImageDiv) {
                currentImageDiv.style.display = 'none';
            }
            // Show notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-warning';
            notification.innerHTML = '<small><i class="fas fa-exclamation-triangle"></i> Gambar akan dihapus saat form disimpan</small>';
            currentImageDiv.parentNode.insertBefore(notification, currentImageDiv.nextSibling);
        }
    }
    
    // Show image modal function
    function showImageModal(imageSrc, title) {
        // Create modal if doesn't exist
        let modal = document.getElementById('imageViewModal');
        if (!modal) {
            const modalHtml = `
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
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modal = document.getElementById('imageViewModal');
        }
        
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModalTitle').textContent = title;
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
</script>
</script>
<script src="<?= base_url('assets/img/js/forminput.js'); ?>"></script>

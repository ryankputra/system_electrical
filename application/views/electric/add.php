<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="m-0">Tambah Electrical</h4>
            <a href="<?= site_url('electric'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <div class="card-body p-4 pt-3">
        <form action="<?= site_url('electric/add'); ?>" method="post" enctype="multipart/form-data">

            <?php if (isset($typeData) && !empty($typeData)): ?>
                <div class="mb-3">
                    <div class="alert alert-success">
                        Kategori dipilih: <strong><?= htmlspecialchars($typeData['type'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <a href="<?= site_url('electric/type') ?>" class="btn btn-outline-success btn-sm ms-2">Pilih Kategori Lain</a>
                    </div>
                </div>
                <input type="hidden" name="type_id" value="<?= $typeData['id']; ?>">
            <?php else: ?>
                 <div class="mb-3">
                    <div class="alert alert-danger">
                        Kategori belum dipilih.
                        <a href="<?= site_url('electric/type') ?>" class="btn btn-outline-danger btn-sm ms-2">Kembali ke Pemilihan Kategori</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mb-2">
                <label for="brand" class="form-label">Brand <small class="text-muted">(opsional)</small></label>
                <input id="brand" type="text" class="form-control rounded-pill <?= form_error('brand') ? 'is-invalid' : '' ?>" name="brand" placeholder="Contoh: Schneider, Omron" value="<?= set_value('brand'); ?>" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <?= form_error('brand', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>

            <div class="mb-2">
                <label for="nama" class="form-label">Nama</label>
                <input id="nama" type="text" class="form-control rounded-pill <?= form_error('nama') ? 'is-invalid' : '' ?>" name="nama" placeholder="Nama umum perangkat" 
                       value="<?= set_value('nama', $typeData['type'] ?? '') ?>" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <?= form_error('nama', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>

            <div class="mb-2">
                <label for="type" class="form-label">Type / Model</label>
                <input id="type" type="text" class="form-control rounded-pill <?= form_error('type') ? 'is-invalid' : '' ?>" name="type" placeholder="Contoh: EZ9F34106, G2R-1-SND" value="<?= set_value('type') ?>" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <?= form_error('type', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <label for="voltage" class="form-label">Voltage <small class="text-muted">(opsional, bisa rentang misal 4-30)</small></label>
                        <div class="input-group">
                            <input id="voltage" type="text" class="form-control <?= form_error('voltage') ? 'is-invalid' : '' ?>" name="voltage" placeholder="Contoh: 220 atau 4-30" value="<?= set_value('voltage'); ?>">
                            <select id="voltage_unit" name="voltage_unit" class="form-select" style="max-width: 100px;">
                                <option value="V" <?= set_select('voltage_unit', 'V', TRUE); ?>>V</option>
                                <option value="VAC" <?= set_select('voltage_unit', 'VAC'); ?>>VAC</option>
                                <option value="VDC" <?= set_select('voltage_unit', 'VDC'); ?>>VDC</option>
                            </select>
                        </div>
                        <div class="form-text">Bisa input satu nilai atau rentang, contoh: 220 atau 4-30</div>
                        <?= form_error('voltage', "<div class='invalid-feedback'>", "</div>"); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <label for="ampere" class="form-label">Ampere <small class="text-muted">(opsional)</small></label>
                         <div class="input-group">
                            <input id="ampere" type="number" step="any" class="form-control <?= form_error('ampere') ? 'is-invalid' : '' ?>" name="ampere" placeholder="1.5" value="<?= set_value('ampere'); ?>">
                            <span class="input-group-text">A</span>
                        </div>
                        <?= form_error('ampere', "<div class='invalid-feedback'>", "</div>"); ?>
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <label for="daya" class="form-label">Daya <small class="text-muted">(opsional)</small></label>
                <div class="input-group">
                    <input id="daya" type="number" step="any" class="form-control <?= form_error('daya') ? 'is-invalid' : '' ?>" name="daya" placeholder="330" value="<?= set_value('daya'); ?>">
                    <select id="daya_unit" name="daya_unit" class="form-select" style="max-width: 100px;">
                        <option value="W" <?= set_select('daya_unit', 'W', TRUE); ?>>W</option>
                        <option value="VA" <?= set_select('daya_unit', 'VA'); ?>>VA</option>
                    </select>
                </div>
                <?= form_error('daya', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>

            <div class="mb-2">
                <label for="location" class="form-label">Lokasi <small class="text-muted">(opsional)</small></label>
                <input id="location" type="text" class="form-control rounded-pill <?= form_error('location') ? 'is-invalid' : '' ?>" name="location" placeholder="Contoh: RAK E2, PANEL 5" value="<?= set_value('location'); ?>" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <?= form_error('location', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Gambar <small class="text-muted">(opsional)</small></label>
                <input id="image" type="file" name="image" accept="image/*" class="form-control" onchange="previewImage(this)">
                <div class="form-text">Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal 2MB</div>
                <div id="imagePreview" class="mt-2" style="display: none;">
                    <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                    <div class="mt-1">
                        <small class="text-success">Preview gambar</small>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary rounded-pill px-5" <?= (isset($typeData)) ? '' : 'disabled' ?>>Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
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
</script>
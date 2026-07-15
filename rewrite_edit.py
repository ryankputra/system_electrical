import re

path = r'c:\xampp\htdocs\electrical-system\application\views\electric\edit.php'

content = r'''<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="m-0">Edit Electrical</h4>
            <a href="<?= site_url('electric'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <div class="card-body p-4 pt-3">
        <form action="<?= site_url('electric/edit/' . urlencode(['electric_id'])); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type_id" value="<?= ['type_id'] ?? '' ?>">
            
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <div class="form-control bg-light text-muted">
                    <?php
                    // Fetch type name
                     =& get_instance();
                     = ->db->get_where('as_electric_types', ['id' => ['type_id']])->row_array();
                    echo htmlspecialchars(['type'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
                    ?>
                </div>
                <small class="text-muted">Kategori tidak dapat diubah pada mode edit.</small>
            </div>

            <div class="mb-2">
                <label for="brand" class="form-label">Brand <small class="text-muted">(opsional)</small></label>
                <input id="brand" type="text" class="form-control rounded-pill <?= form_error('brand') ? 'is-invalid' : '' ?>" name="brand" placeholder="Contoh: Schneider, Omron" value="<?= set_value('brand', ['brand'] ?? ''); ?>" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <?= form_error('brand', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>

            <div class="mb-2">
                <label for="type" class="form-label">Type / Model</label>
                <input id="type" type="text" class="form-control rounded-pill <?= form_error('type') ? 'is-invalid' : '' ?>" name="type" placeholder="Contoh: EZ9F34106, G2R-1-SND" value="<?= set_value('type', ['type'] ?? '') ?>" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <?= form_error('type', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <label for="voltage" class="form-label">Voltage <small class="text-muted">(opsional, bisa rentang misal 4-30)</small></label>
                        <div class="input-group">
                            <input id="voltage" type="text" class="form-control <?= form_error('voltage') ? 'is-invalid' : '' ?>" name="voltage" placeholder="Contoh: 220 atau 4-30" value="<?= set_value('voltage', ['voltage'] ?? ''); ?>">
                            <select id="voltage_unit" name="voltage_unit" class="form-select" style="max-width: 100px;">
                                <option value="V" <?= set_select('voltage_unit', 'V', (['voltage_unit'] ?? '') === 'V'); ?>>V</option>
                                <option value="VAC" <?= set_select('voltage_unit', 'VAC', (['voltage_unit'] ?? '') === 'VAC'); ?>>VAC</option>
                                <option value="VDC" <?= set_select('voltage_unit', 'VDC', (['voltage_unit'] ?? '') === 'VDC'); ?>>VDC</option>
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
                            <input id="ampere" type="number" step="any" class="form-control <?= form_error('ampere') ? 'is-invalid' : '' ?>" name="ampere" placeholder="1.5" value="<?= set_value('ampere', ['ampere'] ?? ''); ?>">
                            <span class="input-group-text">A</span>
                        </div>
                        <?= form_error('ampere', "<div class='invalid-feedback'>", "</div>"); ?>
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <label for="daya" class="form-label">Daya <small class="text-muted">(opsional)</small></label>
                <div class="input-group">
                    <input id="daya" type="number" step="any" class="form-control <?= form_error('daya') ? 'is-invalid' : '' ?>" name="daya" placeholder="330" value="<?= set_value('daya', ['daya'] ?? ''); ?>">
                    <select id="daya_unit" name="daya_unit" class="form-select" style="max-width: 100px;">
                        <option value="W" <?= set_select('daya_unit', 'W', (['daya_unit'] ?? '') === 'W'); ?>>W</option>
                        <option value="VA" <?= set_select('daya_unit', 'VA', (['daya_unit'] ?? '') === 'VA'); ?>>VA</option>
                        <option value="V" <?= set_select('daya_unit', 'V', (['daya_unit'] ?? '') === 'V'); ?>>V</option>
                    </select>
                </div>
                <?= form_error('daya', "<div class='invalid-feedback'>", "</div>"); ?>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Lokasi Penyimpanan</label>
                <select name="location" id="location" class="form-select" required>
                    <option value="">-- Pilih Lokasi --</option>
                    <?php foreach(( ?? []) as ) : ?>
                        <option value="<?= (int)(['id'] ?? 0); ?>" <?= set_select('location', ['id'], (int)(['location'] ?? 0) === (int)(['id'] ?? 0)); ?>>
                            <?= htmlspecialchars(['location_name'] ?? ['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('location', '<small class="text-danger">', '</small>'); ?>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary rounded-pill px-5">Simpan Perubahan</button>
                <a href="<?= site_url('electric/delete/') . urlencode(['electric_id'] ?? ''); ?>" onclick="return confirm('Apakah anda yakin ingin menghapus electric ini?')" class="btn btn-danger rounded-pill">Hapus Barang</a>
            </div>
        </form>
    </div>
</div>
'''

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("edit.php rewritten successfully")

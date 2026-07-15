<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
            <div class="d-flex align-items-center justify-content-between">
            <h4 class="m-0">Tambah Electrical</h4>
            <a href="<?= site_url('electric'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <div class="card-body p-4 pt-3">
        <form action="<?= site_url('electric/store'); ?>" method="post" enctype="multipart/form-data">

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
                    <label for="type_id" class="form-label">Kategori</label>
                    <select id="type_id" name="type_id" class="form-select <?= form_error('type_id') ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach (($types ?? []) as $t) : ?>
                            <option value="<?= htmlspecialchars($t['id'], ENT_QUOTES, 'UTF-8') ?>" <?= set_select('type_id', $t['id']) ?: (isset($selected_type) && (int)$selected_type === (int)$t['id'] ? 'selected' : '') ?>><?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= form_error('type_id', "<div class='invalid-feedback'>", "</div>"); ?>
                    <div class="mt-2">
                        <a href="<?= site_url('electric/type') ?>" class="btn btn-outline-secondary btn-sm">Pilih Kategori Lain</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mb-2">
                <label for="brand" class="form-label">Brand <small class="text-muted">(opsional)</small></label>
                <input id="brand" type="text" class="form-control rounded-pill <?= form_error('brand') ? 'is-invalid' : '' ?>" name="brand" placeholder="Contoh: Schneider, Omron" value="<?= set_value('brand'); ?>" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <?= form_error('brand', "<div class='invalid-feedback'>", "</div>"); ?>
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

            <div class="mb-3">
                <label for="location" class="form-label">Lokasi Penyimpanan</label>
                <select name="location" id="location" class="form-select" required>
                    <option value="">-- Pilih Lokasi --</option>
                    <?php foreach(($locations ?? []) as $loc) : ?>
                        <option value="<?= (int)($loc['id'] ?? 0); ?>" <?= set_select('location', $loc['id']); ?>>
                            <?= htmlspecialchars($loc['location_name'] ?? $loc['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('location', '<small class="text-danger">', '</small>'); ?>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" name="submit_kembali" value="1" class="btn btn-outline-secondary rounded-pill px-4">Simpan & Kembali</button>
                <button type="submit" name="submit_tambah_lagi" value="1" class="btn btn-primary rounded-pill px-4"><i class="fas fa-plus me-1"></i> Simpan & Tambah Lagi</button>
            </div>
        </form>
    </div>
</div>

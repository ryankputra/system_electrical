<?php // Place the notification block above the card to match ASRS layout and set duration to 3s 
?>
<?php if ($this->session->flashdata('action')) : ?>
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0] ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <h3 class="m-0"><?= htmlspecialchars($title ?? 'Type Form', ENT_QUOTES, 'UTF-8') ?></h3>
    </div>

    <!-- Card Body with Form -->
    <div class="card-body px-lg-5 px-4 py-4">
        <?php // flash alert is rendered above the card (see top of file)
        ?>

        <?php if (function_exists('validation_errors') && validation_errors()): ?>
            <div class="alert alert-danger"><?= validation_errors(); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <?= form_open_multipart($form_action, ['id' => 'typeForm']); ?>
                <div class="mb-3">
                    <label for="type" class="form-label">Nama Jenis Electrical</label>
                    <div class="position-relative">
                        <input type="text" name="type" id="type" class="form-control" maxlength="50" required 
                               value="<?= isset($type['type']) ? htmlspecialchars($type['type'], ENT_QUOTES, 'UTF-8') : set_value('type') ?>" 
                               placeholder="Contoh: RELAY, POWER SUPPLY, TERMOSTAT, KONTAKTOR..."
                               style="text-transform: uppercase;"
                               onkeyup="toggleClear('type', 'clear-button-type'); this.value = this.value.toUpperCase();"
                               oninput="this.value = this.value.toUpperCase();">
                        <img src="<?= base_url('assets/img/delete.png'); ?>" alt="clear" class="action-button clear-button" id="clear-button-type" onclick="clearInput('type', 'clear-button-type')" aria-hidden="true">
                    </div>
                    <div class="form-text text-muted">
                        <small>Masukkan nama jenis peralatan electrical seperti: Relay, Power Supply, Termostat, Kontaktor, MCB, Fuse, Switch, Sensor, Motor, dll.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Gambar (opsional)</label>
                    <input type="file" name="image" id="image" class="form-control">
                    <div id="previewBox" class="mt-3">
                        <?php if (!empty($type['image'])): ?>
                            <img id="previewImg" src="<?= htmlspecialchars(base_url('assets/img/electric_types/' . $type['image']), ENT_QUOTES, 'UTF-8') ?>" style="max-width:240px; display:block;">
                        <?php else: ?>
                            <img id="previewImg" src<?= "('" . base_url('assets/img/electric-default.png') . "')" ?> style="max-width:240px; display:block;">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Simpan</button>
                    <a href="<?= site_url('electric_type') ?>" class="btn btn-secondary rounded-pill px-4">Batal</a>
                </div>

                <?= form_close(); ?>
            </div>

            <div class="col-md-6">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h5 class="card-title">Preview</h5>
                        <p class="text-muted">Preview gambar yang akan disimpan untuk jenis electrical ini.</p>
                        <div class="border p-3 text-center bg-white rounded">
                            <img id="previewImgCard" src="<?= !empty($type['image']) ? htmlspecialchars(base_url('assets/img/electric_types/' . $type['image']), ENT_QUOTES, 'UTF-8') : base_url('assets/img/electric-default.png') ?>" alt="Preview" style="max-width:100%; height:180px; object-fit:contain;">
                        </div>
                        <hr>
                        <h6 class="text-primary">Contoh Jenis Electrical:</h6>
                        <div class="row text-sm">
                            <div class="col-6">
                                <ul class="list-unstyled small text-muted">
                                    <li>• Relay</li>
                                    <li>• Power Supply</li>
                                    <li>• Termostat</li>
                                    <li>• Kontaktor</li>
                                    <li>• MCB</li>
                                    <li>• Fuse</li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <ul class="list-unstyled small text-muted">
                                    <li>• Switch</li>
                                    <li>• Sensor</li>
                                    <li>• Motor</li>
                                    <li>• Transformer</li>
                                    <li>• Inverter</li>
                                    <li>• PLC</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('image').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(ev) {
            var src = ev.target.result;
            var img = document.getElementById('previewImg');
            var imgCard = document.getElementById('previewImgCard');
            if (img) img.src = src;
            if (imgCard) imgCard.src = src;
        };
        reader.readAsDataURL(file);
    });
</script>

<?php if ($this->session->flashdata('action')) : ?>
    <script>
        // ensure duration is 3 seconds (string) to match ASRS exactly
        window.notificationDuration = "3000";
    </script>
<?php endif; ?>

<script>
    // Register input clear config for this form
    window.inputConfigs = window.inputConfigs || [];
    // Add or replace entry for 'type'
    (function() {
        const existing = window.inputConfigs.find(c => c.id === 'type');
        if (existing) {
            existing.button = 'clear-button-type';
        } else {
            window.inputConfigs.push({
                id: 'type',
                button: 'clear-button-type'
            });
        }
    })();
</script>

<script src="<?= base_url('assets/img/js/forminput.js'); ?>"></script>

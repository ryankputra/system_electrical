<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Tambah Pengguna Air System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('user'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- NIK Input Section -->
            <div class="mb-2">
                <label for="nik" class="form-label">NIK</label>
                <div class="position-relative">
                    <input id="nik" type="text" class="form-control rounded-pill pe-5 <?= form_error('nik') ? 'is-invalid' : '' ?>" name="nik" placeholder="123456789" value="<?= set_value('nik'); ?>" onkeyup="toggleClear('nik', 'clear-button-nik')" autocomplete="off">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-nik" onclick="clearInput('nik', 'clear-button-nik')" aria-hidden="true">
                    <?= form_error('nik', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Name Input Section -->
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <div class="position-relative">
                    <input id="name" type="text" class="form-control rounded-pill pe-5 <?= form_error('name') ? 'is-invalid' : '' ?>" name="name" placeholder="John Doe" value="<?= set_value('name'); ?>" onkeyup="toggleClear('name', 'clear-button-name')" autocomplete="off">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-name" onclick="clearInput('name', 'clear-button-name')" aria-hidden="true">
                    <?= form_error('name', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary rounded-pill">Tambah</button>
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
            id: 'nik',
            button: 'clear-button-nik'
        },
        {
            id: 'name',
            button: 'clear-button-name'
        }
    ];
</script>
<script src="<?= base_url('assets/img/js/forminput.js'); ?>"></script>
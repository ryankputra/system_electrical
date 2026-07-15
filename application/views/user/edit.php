<?php $user = $user ?? []; $roles = $roles ?? ['Staf Gudang', 'Manajer OE', 'Teknisi']; ?>
<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Edit Pengguna Electrical System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('user'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- NIK Input Section (Disabled) -->
            <div class="mb-2">
                <label for="nik" class="form-label">NIK</label>
                <input id="nik" type="text" class="form-control rounded-pill" name="nik" placeholder="<?= $user['nik'] ?>" disabled>
            </div>

            <!-- Name Input Section -->
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <div class="position-relative">
                    <input id="name" type="text" class="form-control rounded-pill pe-5 <?= form_error('name') ? 'is-invalid' : '' ?>" name="name" placeholder="John Doe" value="<?= set_value('name') ? set_value('name') : $user['name']; ?>" onkeyup="toggleClear('name', 'clear-button-name')" autocomplete="off">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-name" onclick="clearInput('name', 'clear-button-name')" aria-hidden="true">
                    <?= form_error('name', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Password Input Section (optional) -->
            <div class="mb-3">
                <label for="password" class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                <div class="position-relative">
                    <input id="password" type="password" class="form-control rounded-pill pe-5 <?= form_error('password') ? 'is-invalid' : '' ?>" name="password" placeholder="Biarkan kosong jika tidak diganti" value="<?= set_value('password'); ?>" onkeyup="toggleClear('password', 'clear-button-password')" autocomplete="new-password">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-password" onclick="clearInput('password', 'clear-button-password')" aria-hidden="true">
                    <?= form_error('password', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Role Input Section -->
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <?php $selectedRole = set_value('role') ? set_value('role') : $user['role']; ?>
                <select id="role" name="role" class="form-select rounded-pill <?= form_error('role') ? 'is-invalid' : '' ?>">
                    <option value="">-- Pilih Role --</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= htmlspecialchars($r) ?>" <?= $selectedRole === $r ? 'selected' : '' ?>><?= htmlspecialchars($r) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('role', "<div class='invalid-feedback d-block'>", "</div>"); ?>
            </div>

            <?php if ($this->session->userdata('user_data')['nik'] !== $user['nik']) : ?>
                <!-- Save and Delete Button (for other users) -->
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                    <a href="<?= site_url('user/delete/') . urlencode(base64_encode($user['nik'])); ?>" onclick="return confirm('Apakah anda yakin?')" class="btn btn-danger rounded-pill">Hapus</a>
                </div>
            <?php else : ?>
                <!-- Save Button Only (for current user) -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                </div>
            <?php endif ?>
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
        id: 'name',
        button: 'clear-button-name'
    }];
    // add password clear mapping
    window.inputConfigs.push({
        id: 'password',
        button: 'clear-button-password'
    });
</script>
<script src="<?= base_url('assets/img/js/forminput.js'); ?>"></script>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper" style="background-color:#f4f7f6;padding:1.25rem 1rem;">
    <div class="container-fluid mb-5">
        
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <h3 class="mb-4 text-dark fw-bold">Profil & Password</h3>

                <!-- Informasi Profil -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-user-circle me-2 text-primary"></i>Informasi Akun</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase tracking-wide">Nama Lengkap</label>
                            <p class="fs-5 mb-0 fw-medium"><?= htmlspecialchars($user['name'] ?? '-'); ?></p>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label class="text-muted small fw-semibold text-uppercase tracking-wide">NIK</label>
                                <p class="fs-6 mb-0"><?= htmlspecialchars($user['nik'] ?? '-'); ?></p>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-semibold text-uppercase tracking-wide">Role Akses</label>
                                <p class="fs-6 mb-0">
                                    <span class="badge bg-info text-dark px-3 py-2 rounded-pill">
                                        <?= htmlspecialchars($this->session->userdata('role') ?? '-'); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Ganti Password -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-lock me-2 text-warning"></i>Ganti Password</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($this->session->flashdata('action')) : ?>
                            <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" role="alert">
                                <?= $this->session->flashdata('action')[1]; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?= site_url('profile/update_password'); ?>" method="post">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Password Lama <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                                    <input type="password" name="old_password" class="form-control border-start-0 bg-light" required>
                                </div>
                            </div>
                            
                            <hr class="my-4 text-muted">

                            <div class="mb-3">
                                <label class="form-label fw-medium">Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="new_password" class="form-control border-start-0 bg-light" required minlength="5">
                                </div>
                                <div class="form-text">Minimal 5 karakter.</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-circle text-muted"></i></span>
                                    <input type="password" name="confirm_password" class="form-control border-start-0 bg-light" required minlength="5">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
                                Simpan Password Baru
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const newPassInput = document.querySelector('input[name="new_password"]');
    const confirmPassInput = document.querySelector('input[name="confirm_password"]');
    const submitBtn = document.querySelector('button[type="submit"]');

    // Create warning elements
    const passWarning = document.createElement('div');
    passWarning.className = 'text-danger small mt-1 d-none fw-bold';
    passWarning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Konfirmasi password tidak sama!';
    confirmPassInput.parentElement.after(passWarning);

    const strengthDiv = document.createElement('div');
    strengthDiv.className = 'mt-2 d-none';
    strengthDiv.innerHTML = `
        <div class="d-flex justify-content-between mb-1">
            <span class="small fw-bold text-muted">Kekuatan: <span id="strength-text"></span></span>
        </div>
        <div class="progress" style="height: 6px;">
            <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    `;
    newPassInput.parentElement.after(strengthDiv);

    const strengthText = document.getElementById('strength-text');
    const strengthBar = document.getElementById('strength-bar');

    function checkPasswordMatch() {
        if (confirmPassInput.value && newPassInput.value !== confirmPassInput.value) {
            passWarning.classList.remove('d-none');
            submitBtn.disabled = true;
            confirmPassInput.classList.add('is-invalid');
        } else {
            passWarning.classList.add('d-none');
            submitBtn.disabled = false;
            confirmPassInput.classList.remove('is-invalid');
        }
    }

    function checkPasswordStrength(password) {
        if (!password) {
            strengthDiv.classList.add('d-none');
            return;
        }
        strengthDiv.classList.remove('d-none');
        
        let strength = 0;
        if (password.length >= 5) strength += 1;
        if (password.length >= 8) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;

        if (strength <= 1) {
            strengthText.textContent = 'Lemah (Weak)';
            strengthText.className = 'text-danger';
            strengthBar.style.width = '33%';
            strengthBar.className = 'progress-bar bg-danger';
        } else if (strength <= 3) {
            strengthText.textContent = 'Sedang (Mid)';
            strengthText.className = 'text-warning';
            strengthBar.style.width = '66%';
            strengthBar.className = 'progress-bar bg-warning';
        } else {
            strengthText.textContent = 'Kuat (Strong)';
            strengthText.className = 'text-success';
            strengthBar.style.width = '100%';
            strengthBar.className = 'progress-bar bg-success';
        }
    }

    newPassInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        checkPasswordMatch();
    });

    confirmPassInput.addEventListener('input', checkPasswordMatch);
});
</script>
</div>

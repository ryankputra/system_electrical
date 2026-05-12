<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electrical System - Login</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css'); ?>">
</head>
<body>
    <?php if ($this->session->flashdata('error')) : ?>
        <div class="cust-notification m-3">
            <div class="alert alert-danger alert-dismissible fade show" id="notification" role="alert">
                <?= $this->session->flashdata('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <main style="background-image: url('<?= base_url('assets/img/pabrik.jpg'); ?>'); background-size: cover; background-color: black; padding-top: 9rem;" class="min-vh-100">
        <div class="card rounded-5 shadow border-0 mx-auto w-75" style="max-width: 500px;">
            <div class="card-body d-flex flex-column align-items-center gap-4 p-4">
                <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                <h5 class="text-center">Electrical System</h5>

                <form action="<?= site_url('auth/index'); ?>" method="post" class="w-75">
    <div class="mb-3">
        <label>NIK</label>
        <input type="text" name="nik" class="form-control rounded-pill" placeholder="Masukkan NIK" value="<?= set_value('nik'); ?>">
    </div>

    <div class="mb-4">
        <label>Password</label>
        <input type="password" name="password" class="form-control rounded-pill" placeholder="Masukkan Password">
    </div>

    <?php if ($this->session->flashdata('error')) : ?>
        <div class="alert alert-danger p-2 small text-center" role="alert">
            <?= $this->session->flashdata('error'); ?>
        </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary w-100 rounded-pill">Login</button>
</form>
            </div>
        </div>
    </main>

    <footer class="fixed-bottom d-flex text-white px-2 py-1 justify-content-between" style="background-color: #004274; font-size: 10px;">
        <span>ELECTRICAL-SYSTEM V 1.0 | Operation Excellence 2026</span>
    </footer>

    <script src="<?= base_url('assets/img/js/bootstrap.bundle.min.js'); ?>"></script>
    <script>
        setTimeout(() => {
            const note = document.getElementById('notification');
            if (note) { note.style.display = 'none'; }
        }, 3000);
    </script>
</body>
</html>
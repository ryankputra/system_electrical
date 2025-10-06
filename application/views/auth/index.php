<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air System</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css'); ?>">
    <script src="<?= base_url('assets/img/js/bootstrap.bundle.min.js'); ?>"></script>
</head>

<body>
    <!-- Flash Notification -->
    <?php if ($this->session->flashdata('action')) : ?>
        <div class="cust-notification m-3">
            <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
                <?= $this->session->flashdata('action')[1]; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <main style="background-image: url('<?= base_url('assets/img/pabrik.jpg'); ?>'); background-size: cover; background-color: black; padding-top: 9rem;" class="min-vh-100">
        <div class="card rounded-5 shadow border-0 mx-auto w-75" style="max-width: 500px;">
            <div class="card-body d-flex flex-column align-items-center gap-4 p-4">
                <!-- Apparel One Indonesia Logo -->
                <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">

                <!-- Needle Dispenser Machine Heading -->
                <h5 class="text-center">Electrical System</h5>

                <!-- NIK Input Form -->
                <form action="" method="post" class="w-75">
                    <div class="mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <div class="position-relative">
                            <input id="nik" type="text" class="form-control rounded-pill pe-5 <?= form_error('nik') ? 'is-invalid' : '' ?>" name="nik" placeholder="123456789" value="<?= set_value('nik'); ?>" onkeyup="toggleClear('nik', 'clear-button-nik')" autocomplete="off">
                            <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-nik" onclick="clearInput('nik', 'clear-button-nik')" aria-hidden="true">
                            <?= form_error('nik', "<div class='invalid-feedback'>", "</div>"); ?>
                        </div>
                    </div>

                    <!-- Login button -->
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Login</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="fixed-bottom d-flex text-white px-2 py-1 justify-content-between" style="background-color: #004274; font-size: 10px;">
        <span>AIR-SYSTEM V 1.0</span>
        <span>Operation Excellence 2025</span>
    </footer>
</body>

<script>
    window.notificationDuration = "3000";
    window.inputConfigs = [{
        id: 'nik',
        button: 'clear-button-nik'
    }];
</script>

<script src="<?= base_url('assets/img/js/forminput.js'); ?>"></script>

</html>
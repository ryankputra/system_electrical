    </main>

    <!-- Footer -->
    <footer class="fixed-bottom d-flex text-white px-2 py-1 justify-content-between" style="background-color: #004274; font-size: 10px;">
        <span>Electrical System V 1.0</span>
        <span>Operation Excellence 2025</span>
    </footer>

    <!-- Scripts: Bootstrap then app logic (ASRS canonical) -->
    <script>
        // Global vars used by ASRS scripts; set reasonable defaults for Electrical-system
        window.notificationDuration = window.notificationDuration || 3000;
        window.popoverConfigs = window.popoverConfigs || [];
        window.inputConfigs = window.inputConfigs || [];
    </script>
    <!-- Load local Bootstrap bundle for offline use (actual files are in assets/img/js) -->
    <script src="<?= base_url('assets/img/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/img/js/tooltip.js') ?>"></script>
    <script src="<?= base_url('assets/img/js/popoverlogic.js') . '?v=' . @filemtime(FCPATH.'assets/img/js/popoverlogic.js') ?>"></script>
    <script src="<?= base_url('assets/img/js/notificationlogic.js') . '?v=' . @filemtime(FCPATH.'assets/img/js/notificationlogic.js') ?>"></script>
    <script src="<?= base_url('assets/img/js/forminput.js') ?>"></script>
    <script src="<?= base_url('assets/img/js/resetfilter.js') ?>"></script>
    <script src="<?= base_url('assets/img/js/searchbar.js') ?>"></script>
    <script src="<?= base_url('assets/img/js/sortbutton.js') ?>"></script>
    <script src="<?= base_url('assets/img/js/uploadlogic.js') ?>"></script>

    </body>

    </html>
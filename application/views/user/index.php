<?php if ($this->session->flashdata('action')) : ?>
    <!-- Flash Notification Alert -->
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 table-responsive mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Search -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="row g-3 align-items-center">
            <!-- Page Title -->
            <div class="col-12 col-lg-6">
                <h3 class="m-0">Data Pengguna</h3>
            </div>

            <!-- Search Form + Add User -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search Form -->
                    <form action="" method="post" class="flex-grow-1 position-relative" id="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan NIK atau nama..." name="keyword" value="<?= $this->session->userdata('keyword') ?>" id="search-bar" onkeyup="displayClear()" autocomplete="off">
                            <input type="hidden" name="find" value="1">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>
                        <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button top-50 translate-middle-y" id="clear-button" onclick="clearKeyword()" style="right: 5.5rem;">
                    </form>

                    <!-- Add User Button -->
                    <a href="<?= site_url('user/add'); ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($users)) : ?>
        <!-- No Data Alert -->
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <p>Pengguna tidak ditemukan. Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
        </div>
    <?php else : ?>
        <!-- Data Table Container -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <!-- NIK Column -->
                        <th scope="col" class="text-center ps-lg-5 ps-4">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>NIK</span>
                                <?php if ($sortKeyword[0] === 'nik') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('nik-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan NIK (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan NIK">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('nik-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan NIK (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Name Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Nama</span>
                                <?php if ($sortKeyword[0] === 'name') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('name-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Nama (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Nama">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('name-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Nama (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Edit Column -->
                        <th scope="col" class="text-center">Edit</th>

                        <!-- Delete Column -->
                        <th scope="col" class="text-center pe-lg-5 pe-4">Delete</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <th scope="row" class="text-center ps-lg-5 ps-4"><?= $user['nik']; ?></th>
                            <td class="text-center"><?= $user['name']; ?></td>
                            <td class="text-center">
                                <a href="<?= site_url('user/edit/' . $user['nik']); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit pengguna">
                                    <img src="<?= base_url('assets/img/edit.png'); ?>" alt="edit" class="action-button">
                                </a>
                            </td>
                            <td class="text-center pe-lg-5 pe-4">
                                <a href="<?= site_url('user/delete/' . $user['nik']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus pengguna">
                                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Card Footer with Pagination and Controls -->
        <div class="card-footer bg-white border-0 px-lg-5 px-4 py-3">
            <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
                <!-- Record Count Display -->
                <div class="text-muted">
                    Menampilkan <strong><?= $display; ?></strong>
                </div>

                <!-- Pagination Links -->
                <?= $this->pagination->create_links(); ?>

                <!-- Action Buttons -->
                <div class="btn-group">
                    <!-- Reset All Filters -->
                    <?php if ($hasFilters) : ?>
                        <form action="" method="post" class="d-inline">
                            <input type="hidden" name="reset" value="1">
                            <button type="submit" class="btn btn-outline-secondary rounded-start-pill">Reset Filter</button>
                        </form>
                    <?php endif; ?>

                    <!-- Download Button -->
                    <a href="<?= site_url('user/download'); ?>" class="btn btn-primary <?= $hasFilters ? '' : 'rounded-start-pill' ?>">Download</a>

                    <!-- Upload Modal Trigger -->
                    <button type="button" class="btn btn-primary rounded-end-pill" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        Upload
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Ketentuan Upload:</strong>
                    <ul class="mb-0 mt-2">
                        <li>WAJIB menggunakan template yang sudah disediakan.</li>
                        <li>Download template <a href="<?= site_url('user/template'); ?>">disini</a>.</li>
                        <li>Ketentuan pengisian tabel:
                            <ol>
                                <li>NIK wajib menggunakan angka dan berjumlah 9 digit.</li>
                                <li>Nama akan otomatis diformat menjadi Title Case.</li>
                            </ol>
                        </li>
                    </ul>
                </div>
                <form id="uploadForm" action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Pilih File Excel</label>
                        <input class="form-control" type="file" id="formFile" name="file" accept=".xlsx,.xls" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="uploadForm" class="btn btn-success rounded-pill" id="uploadBtn">Upload Data</button>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * Global Configuration Variables for existing JS files
     */
    window.notificationDuration = "10000";

    /**
     * Handle upload form submission
     */
    (function() {
        const uploadBtn = document.getElementById('uploadBtn');
        const form = document.getElementById('uploadForm');
        const fileInput = document.getElementById('formFile');
        if (!uploadBtn || !form || !fileInput) return;
        uploadBtn.addEventListener('click', function(e) {
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Pilih file Excel terlebih dahulu!');
            }
        });
    })();
</script>
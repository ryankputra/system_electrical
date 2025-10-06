<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Add Button -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="m-0"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
            <a class="btn btn-primary rounded-pill px-4" href="<?= site_url('electric_type/add') ?>">Tambah Jenis Electrical</a>
        </div>
    </div>

    <!-- Card Body with Content -->
    <div class="card-body px-lg-5 px-4 py-4">
        <?php if (!empty($types)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($types as $t): ?>
                    
                    <?php // check if the image file exists on the server, fallback to electric-default.png if not
                        $imgPath = FCPATH . 'assets/img/electric_types/';
                        $img = base_url('assets/img/electric-default.png');
                        if (!empty($t['image'])) {
                            $candidate = $imgPath . $t['image'];
                            if (is_file($candidate)) {
                                $img = base_url('assets/img/electric_types/' . $t['image']);
                            }
                        }
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="electric-thumb">
                                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title mb-2"><?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?></h5>

                                <?php if ($t['is_in_use']): ?>
                                    <!-- Show usage count if type is in use -->
                                    <small class="text-muted mb-2 d-block">Digunakan oleh <?= $t['usage_count'] ?> item electrical</small>
                                <?php else: ?>
                                    <small class="text-muted mb-2 d-block">Belum digunakan</small>
                                <?php endif; ?>

                                <div class="d-flex justify-content-center gap-2">
                                    <a href="<?= site_url('electric_type/edit/' . $t['id']) ?>" class="btn btn-sm btn-outline-warning">Edit</a>

                                    <?php if ($t['is_in_use']): ?>
                                        <!-- Wrapper for disabled button to ensure tooltip works -->
                                        <span class="d-inline-block"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Tidak dapat dihapus karena sedang digunakan oleh <?= $t['usage_count'] ?> item electrical">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                disabled
                                                style="pointer-events: none;">
                                                Hapus
                                            </button>
                                        </span>
                                    <?php else: ?>
                                        <!-- Normal delete button for unused types -->
                                        <a href="<?= site_url('electric_type/delete/' . $t['id']) ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Hapus jenis electrical <?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?> ini?')">
                                            Hapus
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h5 class="alert-heading">Belum Ada Jenis Electrical</h5>
                <p class="mb-2">Belum ada jenis electrical yang didaftarkan dalam sistem.</p>
                <hr>
                <p class="mb-0">
                    <a class="btn btn-primary btn-sm" href="<?= site_url('electric_type/add') ?>">Tambah Jenis Electrical Pertama</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

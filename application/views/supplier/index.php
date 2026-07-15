<div class="card mx-auto rounded-5 shadow border-0 table-responsive mb-5" style="margin-top: 5rem; max-width: 95%;">
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-6">
                <h3 class="m-0"><?= htmlspecialchars($title) ?></h3>
            </div>
            <div class="col-12 col-lg-6 text-end">
                <a href="<?= site_url('dashboard') ?>" class="btn btn-outline-secondary rounded-pill px-4 me-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <?php if (is_admin()): ?>
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                        <i class="fas fa-plus"></i> Tambah Supplier
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert alert-success m-3"><?= $this->session->flashdata('message') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger m-3"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <div class="card-body p-0 table-responsive">
        <table class="table table-borderless table-hover table-striped mb-0 align-middle">
            <thead>
                <tr class="text-center">
                    <th>No</th>
                    <th>Nama Supplier</th>
                    <th>Kontak Person</th>
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <?php if (is_admin()): ?>
                        <th class="pe-lg-4">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($suppliers as $s): ?>
                <tr class="text-center">
                    <td><?= $no++ ?></td>
                    <td class="text-start"><strong><?= htmlspecialchars($s['supplier_name']) ?></strong></td>
                    <td><?= htmlspecialchars($s['contact_person'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['address'] ?? '-') ?></td>
                    <?php if (is_admin()): ?>
                        <td class="pe-lg-4">
                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editSupplierModal<?= $s['id'] ?>"><i class="fas fa-edit"></i> Edit</button>
                            <a href="<?= site_url('supplier/delete/'.$s['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus data ini?');"><i class="fas fa-trash"></i> Hapus</a>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($suppliers)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada data supplier.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Supplier -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= site_url('supplier/add') ?>" method="post">
      <div class="modal-header">
        <h5 class="modal-title" id="addSupplierModalLabel">Tambah Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
            <input type="text" name="supplier_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Person</label>
            <input type="text" name="contact_person" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">No. Telepon</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Alamat Lengkap</label>
            <textarea name="address" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit Supplier -->
<?php foreach ($suppliers as $s): ?>
<div class="modal fade" id="editSupplierModal<?= $s['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= site_url('supplier/edit/'.$s['id']) ?>" method="post">
      <div class="modal-header">
        <h5 class="modal-title">Edit Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
            <input type="text" name="supplier_name" class="form-control" value="<?= htmlspecialchars($s['supplier_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Person</label>
            <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($s['contact_person'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">No. Telepon</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($s['phone'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Alamat Lengkap</label>
            <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($s['address'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
    // Mencegah double-submission pada form modal (Tambah & Edit Supplier)
    document.querySelectorAll('.modal form').forEach(function(form) {
        form.addEventListener('submit', function() {
            var btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Menyimpan...';
            }
        });
    });
</script>

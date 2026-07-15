<div class="card mx-auto rounded-5 shadow border-0 table-responsive mb-5" style="margin-top: 5rem; max-width: 95%;">
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-6">
                <h3 class="m-0"><?= htmlspecialchars($title) ?></h3>
            </div>
            <div class="col-12 col-lg-6 text-end">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addWoModal">
                    <i class="fas fa-plus"></i> Tambah Work Order
                </button>
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
                    <th>Nomor WO</th>
                    <th>Nama Proyek / Divisi</th>
                    <th>Tanggal Permintaan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($work_orders as $wo): ?>
                <tr class="text-center">
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($wo['wo_number']) ?></strong></td>
                    <td><?= htmlspecialchars($wo['project_name']) ?></td>
                    <td><?= date('d M Y', strtotime($wo['request_date'])) ?></td>
                    <td>
                        <a href="<?= site_url('wo/detail/' . $wo['id']) ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i> Detail</a>
                        <a href="<?= site_url('wo/delete/' . $wo['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus Work Order ini?');"><i class="fas fa-trash"></i> Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($work_orders)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data Work Order.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah WO -->
<div class="modal fade" id="addWoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= site_url('wo/add') ?>" method="post">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Work Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
            <label class="form-label">Nama Proyek / Divisi <span class="text-danger">*</span></label>
            <input type="text" name="project_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal Permintaan</label>
            <input type="date" name="request_date" class="form-control" value="<?= date('Y-m-d') ?>">
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

<script>
    // Mencegah double-submission pada form
    document.querySelector('#addWoModal form').addEventListener('submit', function() {
        var btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
    });
</script>

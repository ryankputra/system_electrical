<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="m-0">Master Lokasi</h4>
            <a href="<?= site_url('electric'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>
    <div class="card-body p-4 pt-3">
        <form action="<?= site_url('location/add'); ?>" method="post" class="mb-3">
            <div class="input-group">
                <input type="text" name="location_name" class="form-control rounded-pill" placeholder="Nama Lokasi" required>
                <button class="btn btn-primary rounded-pill ms-2" type="submit">Tambah</button>
            </div>
            <?= form_error('location_name', '<small class="text-danger">', '</small>'); ?>
        </form>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:60px">No</th>
                        <th>Nama Lokasi</th>
                        <th style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($locations as $loc) : ?>
                        <?php $locId = isset($loc['id']) ? $loc['id'] : (isset($loc['location_id']) ? $loc['location_id'] : $loc['location_name']); ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($loc['location_name'] ?? ($loc['location'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <a href="<?= site_url('location/delete/' . urlencode($locId)); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus lokasi ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

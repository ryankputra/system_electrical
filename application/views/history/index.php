<?php if (!empty($this->session->flashdata('action'))) : ?>
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 d-flex justify-content-between align-items-center">
        <h3 class="m-0">Riwayat Transaksi Stok</h3>
        <div>
            <a href="<?= site_url('history/in'); ?>" class="btn btn-success rounded-pill me-2">Tambah Masuk</a>
            <a href="<?= site_url('history/out'); ?>" class="btn btn-danger rounded-pill">Tambah Keluar</a>
        </div>
    </div>

    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Petugas</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)) : ?>
                    <tr><td colspan="6" class="text-center">Belum ada riwayat transaksi</td></tr>
                <?php else : ?>
                    <?php foreach ($history as $row) : ?>
                        <tr>
                            <td><?= date('d M Y H:i:s', strtotime($row['date'])); ?></td>
                            <td><?= htmlspecialchars($row['nama_barang'] ?? $row['electric_id']); ?></td>
                            <td><?= htmlspecialchars($row['type']); ?></td>
                            <td class="text-end"><?= (int)$row['qty']; ?></td>
                            <td><?= htmlspecialchars($row['user_name'] ?? $row['user_nik'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['keterangan'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

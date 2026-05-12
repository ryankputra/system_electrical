<?php
// Simple index view for Transaksi Stok
?>

<?php $this->load->view('templates/header'); ?>
<div class="container" style="margin-top:6rem">
    <h3><?= isset($title) ? $title : 'Transaksi Stok'; ?></h3>
    <p><a href="<?= site_url('transaksi_stok/create'); ?>" class="btn btn-primary">Buat Transaksi</a></p>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Barang</th>
                    <th>Aksi</th>
                    <th>Jumlah</th>
                    <th>Pengguna</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['nama_barang'] ?? $r['electric_id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['type'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['qty'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['user_name'] ?? $r['user_nik'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['date'] ?? $r['datetime'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Belum ada transaksi</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->load->view('templates/footer'); ?>

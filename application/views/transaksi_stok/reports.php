<?php $this->load->view('templates/header'); ?>
<div class="container" style="margin-top:6rem">
    <h3><?= isset($title) ? $title : 'Laporan Transaksi'; ?></h3>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Storing ID</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                    <th>Barang</th>
                    <th>Pengguna</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['storing_id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['location_id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['action'] ?? $r['action'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['type_id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['user_name'] ?? $r['nik'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($r['datetime'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->load->view('templates/footer'); ?>

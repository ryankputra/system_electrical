<?php
// View for displaying detailed low stock items
?>

<?php $this->load->view('templates/header'); ?>

<div class="container-fluid mt-5 pt-5">
    <div class="card mx-auto rounded-5 shadow border-0 mb-5">
        <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 m-0 text-primary">Detail Minimum Stok</h1>
                    <small class="text-muted">Ringkasan stok yang berada di bawah ambang batas.</small>
                </div>
                <div>
                    <a href="<?= site_url('lowstock/export_csv'); ?>" class="btn btn-success me-2">Download CSV</a>
                    <button class="btn btn-outline-secondary" onclick="window.history.back();">Kembali</button>
                </div>
            </div>
        </div>
        <div class="card-body px-lg-5 px-4 py-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Tipe Electrical</th>
                            <th class="text-center">Kategori</th>
                            <th class="text-center">Jumlah Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($low_stock)): ?>
                            <?php foreach ($low_stock as $item): ?>
                                    <?php // Ensure numeric and cap display at 5 ?>
                                    <?php $amount = isset($item['total_amount']) ? (int)$item['total_amount'] : 0; ?>
                                    <?php $display_amount = min($amount, 5); ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= htmlspecialchars($item['type_id']); ?></td>
                                        <td class="text-center text-muted"><?= htmlspecialchars($item['category']); ?></td>
                                        <td class="text-center text-danger fw-bold"><?= htmlspecialchars($display_amount); ?></td>
                                    </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Tidak ada item Minimum Stok.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top px-lg-5 px-4 py-4 rounded-bottom-5 d-flex justify-content-between align-items-center">
            <small class="text-muted">Data diperbarui secara berkala.</small>
        </div>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>

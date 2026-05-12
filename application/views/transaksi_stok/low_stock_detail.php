<?php $this->load->view('templates/header'); ?>
<div class="container" style="margin-top:6rem">
    <h3><?= isset($title) ? $title : 'Detail Stok Rendah'; ?></h3>
    <div class="list-group">
        <?php if (!empty($low_stock)): ?>
            <?php foreach ($low_stock as $it): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($it['type_id'] ?? ''); ?></div>
                        <small class="text-muted">Kategori: <?= htmlspecialchars($it['category'] ?? ''); ?></small>
                    </div>
                    <span class="badge bg-danger rounded-pill"><?= htmlspecialchars($it['total_amount'] ?? ''); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="list-group-item text-center text-muted">Tidak ada stok rendah</div>
        <?php endif; ?>
    </div>
</div>
<?php $this->load->view('templates/footer'); ?>

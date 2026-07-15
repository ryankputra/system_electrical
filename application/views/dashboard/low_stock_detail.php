<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="container-fluid" style="margin-top:5rem;">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-0 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Detail Stok Rendah</h4>
                <p class="text-muted mb-0 small">
                    Menampilkan barang dengan stok &le; <strong><?= (int)($threshold ?? 5); ?> unit</strong>
                    (berdasarkan data FIFO aktual)
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('dashboard/low_stock_detail?threshold=3'); ?>" class="btn btn-sm btn-outline-danger">Kritis (&le;3)</a>
                <a href="<?= site_url('dashboard/low_stock_detail?threshold=5'); ?>" class="btn btn-sm btn-outline-warning">Rendah (&le;5)</a>
                <a href="<?= site_url('dashboard/low_stock_detail?threshold=10'); ?>" class="btn btn-sm btn-outline-secondary">Perlu Perhatian (&le;10)</a>
                <a href="<?= site_url('dashboard'); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <?php
            $habis   = array_filter($low_stock ?? [], fn($i) => (int)($i['total_amount'] ?? 0) === 0);
            $kritis  = array_filter($low_stock ?? [], fn($i) => (int)($i['total_amount'] ?? 0) > 0 && (int)($i['total_amount'] ?? 0) <= 2);
            $rendah  = array_filter($low_stock ?? [], fn($i) => (int)($i['total_amount'] ?? 0) > 2);
        ?>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body d-flex align-items-center p-4">
                    <div style="width:52px;height:52px;background:#dc3545;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;margin-right:16px;">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Stok Habis (0)</div>
                        <h2 class="mb-0 fw-bold text-danger"><?= count($habis); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4" style="border-left: 4px solid #fd7e14 !important;">
                <div class="card-body d-flex align-items-center p-4">
                    <div style="width:52px;height:52px;background:#fd7e14;border-radius:12px;display:flex;align-items:center;justify-content:color:#fff;font-size:20px;margin-right:16px;color:#fff;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Sangat Kritis (1–2)</div>
                        <h2 class="mb-0 fw-bold" style="color:#fd7e14;"><?= count($kritis); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body d-flex align-items-center p-4">
                    <div style="width:52px;height:52px;background:#ffc107;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-right:16px;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Stok Rendah (3–<?= (int)($threshold ?? 5); ?>)</div>
                        <h2 class="mb-0 fw-bold text-warning"><?= count($rendah); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Detail -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-table me-2 text-danger"></i>
                Daftar Barang Stok Rendah
                <span class="badge bg-danger ms-2"><?= count($low_stock ?? []); ?> barang</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($low_stock)): ?>
            <div class="table-responsive">
                <table id="tblLowStock" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">#</th>
                            <th>ID Barang</th>
                            <th>Nama Barang</th>
                            <th>Brand</th>
                            <th>Spesifikasi</th>
                            <th>Lokasi</th>
                            <th class="text-center">Stok Aktual</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock as $i => $item):
                            $stok = (int)($item['total_amount'] ?? $item['stock'] ?? 0);
                            // Build spec string
                            $sp = [];
                            if (!empty($item['spec_type'])) $sp[] = 'Tipe: ' . $item['spec_type'];
                            $vv = trim(($item['voltage'] ?? '') . ($item['voltage_unit'] ?? ''));
                            if ($vv !== '') $sp[] = $vv;
                            if (!empty($item['ampere'])) $sp[] = $item['ampere'] . 'A';
                            $dd = trim(($item['daya'] ?? '') . ($item['daya_unit'] ?? ''));
                            if ($dd !== '') $sp[] = $dd;

                            if ($stok === 0) {
                                $rowClass = 'table-danger';
                                $badgeClass = 'bg-danger';
                                $statusLabel = '<i class="fas fa-ban me-1"></i>Habis';
                            } elseif ($stok <= 2) {
                                $rowClass = 'table-warning';
                                $badgeClass = 'bg-warning text-dark';
                                $statusLabel = '<i class="fas fa-exclamation-triangle me-1"></i>Sangat Kritis';
                            } else {
                                $rowClass = '';
                                $badgeClass = 'bg-danger bg-opacity-75';
                                $statusLabel = '<i class="fas fa-exclamation-circle me-1"></i>Stok Rendah';
                            }
                        ?>
                        <tr class="<?= $rowClass; ?>">
                            <td class="px-4 text-muted"><?= $i + 1; ?></td>
                            <td>
                                <small class="font-monospace fw-bold text-primary">
                                    <?= htmlspecialchars($item['type_id'] ?? ''); ?>
                                </small>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($item['category'] ?? ''); ?></div>
                            </td>
                            <td>
                                <?php if (!empty($item['brand'])): ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($item['brand']); ?></span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($sp)): ?>
                                    <?php foreach ($sp as $s): ?>
                                    <span class="badge bg-light text-dark border me-1" style="font-size:0.72rem;"><?= htmlspecialchars($s); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['location_name']) && $item['location_name'] !== '-'): ?>
                                <span class="badge bg-info text-dark">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($item['location_name']); ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $badgeClass; ?> rounded-pill" style="font-size:1rem; padding: 6px 14px;">
                                    <?= $stok; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $badgeClass; ?>"><?= $statusLabel; ?></span>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('po/create?electric_id=' . urlencode($item['type_id'] ?? '')); ?>"
                                   class="btn btn-sm btn-outline-success" title="Buat PO untuk barang ini">
                                    <i class="fas fa-shopping-cart me-1"></i>Buat PO
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5 class="text-success">Semua Stok Aman!</h5>
                <p class="text-muted">Tidak ada barang dengan stok di bawah <?= (int)($threshold ?? 5); ?> unit.</p>
                <a href="<?= site_url('dashboard'); ?>" class="btn btn-primary mt-2">Kembali ke Dashboard</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbl = document.getElementById('tblLowStock');
    if (tbl && tbl.querySelectorAll('tbody tr').length > 0) {
        $('#tblLowStock').DataTable({
            pageLength: 25,
            lengthChange: false,
            searching: true,
            ordering: true,
            order: [[6, 'asc']], // Sort by stok asc (paling kritis duluan)
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
            columnDefs: [{ targets: [7, 8], orderable: false }]
        });
    }
});
</script>

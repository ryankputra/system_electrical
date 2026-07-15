<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container-fluid" style="margin-top:5rem;">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">🗂️ Kartu Stok Digital (Stock Card)</h4>
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>
    </div>

    <!-- Select2 Assets -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px !important;
            height: calc(2.8rem + 2px) !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 1rem !important;
        }
        .input-group .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px 0 0 8px !important;
        }
    </style>

    <!-- Dropdown Selector Component -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="get" action="<?= site_url('history/stock_card') ?>" id="form-stock-card">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="filter_kategori_sc" class="form-label fw-bold text-secondary small mb-1"><i class="fas fa-tag me-2"></i>Filter Kategori:</label>
                                <select class="form-select select2-kategori" id="filter_kategori_sc">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $kat): ?>
                                        <?php
                                            $cat_selected = (!empty($selected_electric) && $selected_electric['type_id'] == $kat['id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= htmlspecialchars($kat['id']) ?>" <?= $cat_selected ?>><?= htmlspecialchars($kat['type']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="electric_id" class="form-label fw-bold text-secondary small mb-1"><i class="fas fa-search me-2"></i>Pilih Komponen Kelistrikan:</label>
                                <div class="input-group">
                                    <select class="form-select form-select-lg select2" name="electric_id" id="electric_id" style="border-radius: 8px 0 0 8px;">
                                        <option value="">-- Cari & Pilih Suku Cadang --</option>
                                        <?php foreach ($electrics as $el): ?>
                                            <?php 
                                                $spec = [];
                                                if (!empty($el['brand']) && $el['brand'] !== '-') $spec[] = $el['brand'];
                                                if (!empty($el['type']) && $el['type'] !== '-') $spec[] = $el['type'];
                                                
                                                // Voltase, Ampere, Daya
                                                $voltage = trim(($el['voltage'] ?? '') . ($el['voltage_unit'] ?? ''));
                                                if ($voltage !== '') $spec[] = $voltage;
                                                if (!empty($el['ampere'])) $spec[] = $el['ampere'] . 'A';
                                                $daya = trim(($el['daya'] ?? '') . ($el['daya_unit'] ?? ''));
                                                if ($daya !== '') $spec[] = $daya;
                                                
                                                $spec_str = !empty($spec) ? ' (' . implode(' | ', $spec) . ')' : '';
                                                $stock_val = $el['total_stock'] ?? $el['stock'] ?? 0;
                                                $stock_str = ' [Stok: ' . number_format($stock_val) . ']';
                                                $selected = ($selected_id === $el['electric_id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?= htmlspecialchars($el['electric_id']) ?>" data-category-id="<?= htmlspecialchars($el['type_id']) ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($el['nama'] . $spec_str . $stock_str) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary px-4" style="background:#004274;border:0;border-radius: 0 8px 8px 0;">
                                        <i class="fas fa-filter me-2"></i>Tampilkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($selected_electric): ?>
        <!-- Component Details Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 text-white" style="background: linear-gradient(135deg, #004274, #0072b5);">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <span class="badge bg-white text-primary mb-2 fw-bold"><?= htmlspecialchars($selected_electric['electric_id']) ?></span>
                                <h3 class="fw-bold mb-1"><?= htmlspecialchars(!empty($selected_electric['type']) && $selected_electric['type'] !== '-' ? $selected_electric['type'] : $selected_electric['nama']) ?></h3>
                                <p class="mb-0 text-white-50">
                                    Kategori: <strong><?= htmlspecialchars($selected_electric['nama'] ?: '-') ?></strong> | 
                                    Brand: <strong><?= htmlspecialchars($selected_electric['brand'] ?: '-') ?></strong>

                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="p-3 bg-white bg-opacity-10 rounded-3 d-inline-block text-start" style="min-width: 240px; backdrop-filter: blur(4px);">
                                    <small class="text-white-50 d-block">Total Stok Tersedia</small>
                                    <h2 class="fw-bold mb-1 d-inline-block"><?= number_format($selected_electric['stock'] ?? 0) ?></h2>
                                    <span class="text-white-50 small ms-1">unit</span>
                                    
                                    <?php if (!empty($active_batches)): ?>
                                        <div class="mt-2 pt-2 border-top border-white border-opacity-10">
                                            <small class="text-white-50 d-block mb-1" style="font-size: 0.75rem;"><i class="fas fa-layer-group me-1"></i>Rincian Batch Aktif (FIFO):</small>
                                            <?php foreach ($active_batches as $ab): ?>
                                                <div class="d-flex justify-content-between text-white small" style="font-size: 0.8rem; opacity: 0.95;">
                                                    <span>Batch #<?= htmlspecialchars($ab['batch_seq_display'] ?? $ab['id']) ?> (<?= date('d/m/Y', strtotime($ab['created_at'])) ?>)</span>
                                                    <span class="fw-bold ms-3"><?= number_format($ab['qty_sisa']) ?> unit</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Card Table -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-light border-bottom px-4 py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Buku Besar Riwayat Mutasi & Saldo</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-sm" id="table-stock-card-history">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">No</th>
                                        <th style="width: 15%">Tanggal Transaksi</th>
                                        <th style="width: 12%">Tipe</th>
                                        <th style="width: 15%">No. Referensi (PO / WO)</th>
                                        <th class="text-center" style="width: 10%">Masuk (+)</th>
                                        <th class="text-center" style="width: 10%">Keluar (-)</th>
                                        <th class="text-center" style="width: 12%; background: #e8f4f8;">Saldo Akhir</th>
                                        <th style="width: 13%">Operator</th>
                                        <th style="width: 18%">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($card_history)): ?>
                                        <?php $i = 1; ?>
                                        <?php foreach ($card_history as $row): ?>
                                            <?php 
                                                $type = strtolower($row['type'] ?? '');
                                                $isMasuk = strpos($type, 'masuk') !== false || $type === 'in';
                                                $isKeluar = strpos($type, 'keluar') !== false || $type === 'out';
                                                
                                                // Determine Badge
                                                if ($isMasuk) {
                                                    $badge = '<span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i>MASUK</span>';
                                                    $inQty = number_format($row['display_amount']);
                                                    $outQty = '-';
                                                } elseif ($isKeluar) {
                                                    $badge = '<span class="badge bg-danger"><i class="fas fa-arrow-up me-1"></i>KELUAR</span>';
                                                    $inQty = '-';
                                                    $outQty = number_format($row['display_amount']);
                                                } else {
                                                    $badge = '<span class="badge bg-warning text-dark"><i class="fas fa-sync me-1"></i>AUDIT</span>';
                                                    $inQty = '-';
                                                    $outQty = '-';
                                                }
                                                
                                                // Date Formatting
                                                $dateRaw = $row['date'] ?? $row['created_at'] ?? null;
                                                $dateFmt = $dateRaw ? date('d M Y H:i', strtotime($dateRaw)) : '-';
                                                
                                                // Reference
                                                $ref = $row['batch_number'] ?? $row['po_number'] ?? $row['po_id'] ?? $row['wo_id'] ?? '-';
                                                if ($isKeluar && !empty($row['wo_id'])) {
                                                    $ref = 'WO #' . $row['wo_id'];
                                                } elseif ($isMasuk && !empty($row['po_number'])) {
                                                    $ref = htmlspecialchars($row['po_number']);
                                                }
                                            ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= $dateFmt ?></td>
                                                <td><?= $badge ?></td>
                                                <td><strong><?= htmlspecialchars($ref) ?></strong></td>
                                                <td class="text-center text-success font-monospace fw-bold"><?= $isMasuk ? '+' . $inQty : '-' ?></td>
                                                <td class="text-center text-danger font-monospace fw-bold"><?= $isKeluar ? '-' . $outQty : '-' ?></td>
                                                <td class="text-center font-monospace fw-bold" style="background: #f4fafd; font-size: 1.05rem; color: #004274; border-left: 1px solid #dee2e6; border-right: 1px solid #dee2e6;">
                                                    <?= number_format($row['running_balance']) ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><i class="fas fa-user me-1"></i><?= htmlspecialchars($row['user_name'] ?? $row['user_nik'] ?? '-') ?></small>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?= htmlspecialchars($row['keterangan_dinamis'] ?? $row['keterangan'] ?? '-') ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                <i class="fas fa-folder-open d-block mb-2 fs-2"></i> Belum ada riwayat transaksi mutasi untuk suku cadang ini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-5">
            <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm border-0">
                <i class="fas fa-address-card mb-3 text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                <h5 class="text-muted fw-bold">Pilih Komponen Terlebih Dahulu</h5>
                <p class="text-muted mb-0">Silakan pilih komponen kelistrikan di atas untuk memuat data Kartu Stok Digital.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Load jQuery and Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Save all options for filtering
        var allOptions = $('#electric_id').html();

        // Initialize Select2 for Kategori
        $('#filter_kategori_sc').select2({
            theme: 'bootstrap-5',
            placeholder: 'Semua Kategori',
            width: '100%'
        });

        // Initialize Select2 for Electric
        $('#electric_id').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Cari & Pilih Suku Cadang --',
            width: '100%'
        });

        function filterComponents(catId, keepSelected) {
            var currentVal = $('#electric_id').val();
            
            // Restore all options first
            $('#electric_id').html(allOptions);
            
            if (catId !== "") {
                $('#electric_id option').each(function() {
                    var optCat = $(this).attr('data-category-id');
                    // Keep the empty placeholder option
                    if ($(this).val() !== "" && optCat !== catId) {
                        $(this).remove();
                    }
                });
            }
            
            // Re-init select2 value
            if (keepSelected && currentVal) {
                $('#electric_id').val(currentVal);
            } else {
                $('#electric_id').val("");
            }
            $('#electric_id').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Cari & Pilih Suku Cadang --',
                width: '100%'
            });
        }

        // Run on page load if category is already selected
        var initialCat = $('#filter_kategori_sc').val();
        if (initialCat !== "") {
            filterComponents(initialCat, true);
        }

        // Listen for category change
        $('#filter_kategori_sc').on('change', function() {
            var catId = $(this).val();
            filterComponents(catId, false);
        });

        // Auto submit form on change to make it feel like an instant filter
        $('#electric_id').on('change', function() {
            if ($(this).val() !== "") {
                $('#form-stock-card').submit();
            }
        });
    });
</script>

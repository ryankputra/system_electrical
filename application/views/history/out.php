<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div style="margin-top: 3rem; margin-bottom: 3rem;">
    <div class="container-fluid">

        <!-- ==================== TAMPILAN AWAL: GRID LOKASI ==================== -->
        <?php if (empty($lokasi_id)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-4"><i class="fas fa-warehouse me-2"></i>Pilih Lokasi Gudang</h3>
                            <p class="text-muted mb-0">Tentukan lokasi gudang terlebih dahulu sebelum memilih barang yang akan diambil.</p>
                        </div>
                        <a href="<?= base_url('index.php/dashboard'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <?php if (!empty($locations) && is_array($locations)): ?>
                    <?php foreach ($locations as $loc): ?>
                        <?php
                            $locId = htmlspecialchars($loc['id'] ?? $loc['lokasi_id'] ?? $loc['location_id'] ?? '');
                            $locName = htmlspecialchars($loc['location_name'] ?? $loc['nama'] ?? $loc['name'] ?? 'Lokasi');
                            $locDesc = htmlspecialchars($loc['description'] ?? $loc['keterangan'] ?? '');
                        ?>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <a href="<?= base_url('index.php/history/out?lokasi_id=' . urlencode($locId)); ?>" style="text-decoration: none; color: inherit;">
                                <div class="card border-0 shadow-sm rounded-4 h-100 transition-all" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center text-center">
                                        <div class="mb-3" style="font-size: 2.5rem; color: #0d6efd;">
                                            <i class="fas fa-cubes"></i>
                                        </div>
                                        <h5 class="card-title mb-2 fw-bold"><?= $locName; ?></h5>
                                        <?php if ($locDesc): ?>
                                            <p class="card-text text-muted small"><?= $locDesc; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>Belum ada lokasi gudang yang terdaftar.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <!-- ==================== TAMPILAN FORM: PILIH BARANG ==================== -->
        <?php else: ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-hand-holding-box me-2"></i>Ambil Barang</h3>
                            <p class="text-muted mb-0">Pilih barang yang akan diambil dari lokasi <strong><?= isset($barang_per_lokasi[0]['location_name']) ? htmlspecialchars($barang_per_lokasi[0]['location_name']) : htmlspecialchars($lokasi_id); ?></strong></p>
                        </div>
                        <a href="<?= base_url('index.php/history/out'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Ganti Lokasi
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <?php if (!empty($barang_per_lokasi) && is_array($barang_per_lokasi)): ?>
                                <form action="" method="post">
                                    <!-- Pilih Work Order -->
                                    <div class="mb-4">
                                        <label for="wo_id" class="form-label fw-bold">Nomor Work Order (WO) <span class="text-danger">*</span></label>
                                        <select id="wo_id" name="wo_id" class="form-select rounded-3" required>
                                            <option value="">-- Pilih Work Order --</option>
                                            <?php foreach ($work_orders as $wo): ?>
                                                <option value="<?= htmlspecialchars($wo['id']) ?>">
                                                    <?= htmlspecialchars($wo['wo_number'] . ' - ' . $wo['project_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Search Bar untuk cari barang -->
                                    <div class="mb-4">
                                        <label for="barang_search" class="form-label fw-bold">
                                            <i class="fas fa-search me-2"></i>Cari Barang
                                        </label>
                                        <input type="text" id="barang_search" class="form-control rounded-3" placeholder="Ketik nama barang, brand, atau spesifikasi...">
                                        <div class="form-text">Ketikkan untuk memfilter barang secara real-time</div>
                                    </div>

                                    <!-- Pilih Barang dengan Select2 -->
                                    <div class="mb-4">
                                        <label for="electric_id" class="form-label fw-bold">Pilih Barang <span class="text-danger">*</span></label>
                                        <select id="electric_id" name="electric_id" class="form-select rounded-3" required>
                                            <option value="">-- Ketik atau Pilih Barang --</option>
                                            <?php foreach ($barang_per_lokasi as $e): 
                                                $name = trim($e['nama'] ?? '');
                                                $type = trim($e['type'] ?? '');
                                                $brand = trim($e['brand'] ?? '');
                                                $voltage = trim((string)($e['voltage'] ?? '')) . trim($e['voltage_unit'] ?? '');
                                                $ampere = trim((string)($e['ampere'] ?? ''));
                                                $daya = trim((string)($e['daya'] ?? ''));
                                                $total = (int)($e['total_stock'] ?? 0);

                                                $specParts = [];
                                                if ($brand !== '') $specParts[] = 'Brand: ' . $brand;
                                                if ($voltage !== '') $specParts[] = $voltage;
                                                if ($ampere !== '') $specParts[] = $ampere . 'A';
                                                if ($daya !== '') $specParts[] = $daya . 'W';

                                                $displayName = ($type !== '') ? $type . ' (' . $name . ')' : $name;
                                                $label = htmlspecialchars($displayName . ' | ' . implode(' | ', $specParts) . ' (Sisa: ' . $total . ')');
                                                $searchText = strtolower($name . ' ' . $type . ' ' . $brand . ' ' . implode(' ', $specParts));
                                            ?>
                                                <option value="<?= htmlspecialchars($e['electric_id']); ?>" data-stock="<?= $total; ?>" data-search="<?= htmlspecialchars($searchText); ?>"><?= $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Sistem akan otomatis memotong stok dari batch tertua (FIFO).</div>
                                    </div>

                                    <!-- Jumlah yang Diambil -->
                                    <div class="mb-4">
                                        <label for="qty" class="form-label fw-bold">Jumlah yang Diambil <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input id="qty" name="qty" type="number" min="1" class="form-control rounded-start-3" placeholder="Masukkan jumlah..." required>
                                            <span class="input-group-text rounded-end-3 bg-light border-0" id="qty-display" style="min-width: 120px;">Maks: -</span>
                                        </div>
                                        <div id="qty-help" class="form-text text-muted mt-2">Pilih barang terlebih dahulu untuk melihat stok tersedia.</div>
                                    </div>

                                    <!-- Keterangan -->
                                    <div class="mb-4">
                                        <label for="keterangan" class="form-label fw-bold">Keterangan / Alasan Pengambilan</label>
                                        <textarea id="keterangan" name="keterangan" class="form-control rounded-3" rows="3" placeholder="Contoh: Perbaikan mesin, Testing, dll..."></textarea>
                                        <div class="form-text">Opsional - Tulis alasan atau keterangan tambahan.</div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-danger btn-lg rounded-3" style="padding: 12px; font-weight: 600;">
                                            <i class="fas fa-check me-2"></i>Simpan Barang Keluar
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info text-center" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>Tidak ada barang tersedia di lokasi ini.
                                </div>
                                <div class="text-center mt-3">
                                    <a href="<?= base_url('index.php/history/out'); ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Pilih Lokasi Lain
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Select2 CDN untuk autocomplete dropdown -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script>
                // Wait for jQuery to be ready
                jQuery(function($) {
                    var $select = $('#electric_id');
                    var $searchInput = $('#barang_search');
                    var originalOptions = []; // Store all original options

                    if ($select.length && $searchInput.length) {
                        console.log('Initializing search filter...');
                        
                        // Collect all options data BEFORE Select2 initialization
                        $select.find('option:not([value=""])').each(function() {
                            var $opt = $(this);
                            var optData = {
                                value: $opt.val(),
                                text: $opt.text(),
                                searchText: ($opt.data('search') || '').toLowerCase(),
                                stock: parseInt($opt.data('stock')) || 0
                            };
                            originalOptions.push(optData);
                            console.log('Stored option:', optData);
                        });

                        // Initialize Select2
                        $select.select2({
                            width: '100%',
                            placeholder: '-- Ketik atau Pilih Barang --',
                            allowClear: true,
                            searchInputPlaceholder: 'Cari barang...'
                        });

                        // Live search filter function
                        function applyFilter() {
                            var searchTerm = $searchInput.val().toLowerCase().trim();
                            console.log('Filtering with term:', searchTerm);

                            // Destroy and rebuild Select2 with filtered options
                            var currentVal = $select.val();
                            
                            // Clear existing options
                            $select.empty();
                            $select.append('<option value="">-- Ketik atau Pilih Barang --</option>');

                            var foundCount = 0;

                            if (searchTerm === '') {
                                // Show all options
                                originalOptions.forEach(function(opt) {
                                    var optHTML = '<option value="' + escapeHtml(opt.value) + '" data-stock="' + opt.stock + '" data-search="' + escapeHtml(opt.searchText) + '">' + 
                                                  escapeHtml(opt.text) + 
                                                  '</option>';
                                    $select.append(optHTML);
                                    foundCount++;
                                });
                            } else {
                                // Filter options
                                originalOptions.forEach(function(opt) {
                                    if (opt.searchText.includes(searchTerm) || opt.text.toLowerCase().includes(searchTerm)) {
                                        var optHTML = '<option value="' + escapeHtml(opt.value) + '" data-stock="' + opt.stock + '" data-search="' + escapeHtml(opt.searchText) + '">' + 
                                                      escapeHtml(opt.text) + 
                                                      '</option>';
                                        $select.append(optHTML);
                                        foundCount++;
                                    }
                                });

                                // Add no-match message if nothing found
                                if (foundCount === 0) {
                                    $select.append('<option disabled>Tidak ada barang yang cocok</option>');
                                }
                            }

                            console.log('Found ' + foundCount + ' items');

                            // Reinitialize Select2 with new options
                            $select.trigger('change.select2');
                            
                            // Restore previous selection if it still exists
                            if (currentVal && originalOptions.some(function(opt) { return opt.value === currentVal; })) {
                                $select.val(currentVal).trigger('change');
                            }
                        }

                        // HTML escape helper
                        function escapeHtml(text) {
                            var map = {
                                '&': '&amp;',
                                '<': '&lt;',
                                '>': '&gt;',
                                '"': '&quot;',
                                "'": '&#039;'
                            };
                            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
                        }

                        // Listen to search input
                        $searchInput.on('input keyup change', function() {
                            applyFilter();
                        });

                        // Update Qty Display when dropdown changes
                        $select.on('change', function() {
                            var $selectedOpt = $select.find('option:selected');
                            var stock = parseInt($selectedOpt.data('stock')) || 0;
                            var $qtyDisplay = $('#qty-display');
                            var $qtyInput = $('#qty');
                            var $qtyHelp = $('#qty-help');

                            if (stock > 0) {
                                $qtyDisplay.html('Maks: <strong>' + stock + '</strong>');
                                $qtyInput.attr('max', stock).removeAttr('disabled');
                                $qtyHelp.html('<i class="fas fa-check-circle text-success me-1"></i>Stok tersedia: ' + stock + ' pcs');
                            } else {
                                $qtyDisplay.html('Maks: 0');
                                $qtyInput.attr('disabled', 'disabled').val('');
                                $qtyHelp.html('<i class="fas fa-exclamation-triangle text-warning me-1"></i>Barang tidak tersedia.');
                            }

                            // Clear search if dropdown cleared
                            if (!$select.val()) {
                                $searchInput.val('');
                            }
                        });

                        // Initialize qty display
                        $select.trigger('change');
                    }
                });
            </script>

        <?php endif; ?>
    </div>
</div>


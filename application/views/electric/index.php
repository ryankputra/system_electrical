<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem;">
    <div class="card-header bg-white border-bottom px-4 py-3">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="m-0">Katalog Electrical</h4>
            <?php if (is_admin()): ?>
            <div>
                <a href="<?= site_url('electric/download'); ?>" class="btn btn-sm btn-outline-primary rounded-pill">Download</a>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    $ci =& get_instance();
    $kategori_list = $ci->db->get('as_electric_types')->result_array();
    $lokasi_list = $ci->db->get('as_location')->result_array();
    
    // Kita jalankan query manual untuk ambil semua electric lengkap dengan specs
    $sql = "SELECT e.*, 
            COALESCE(l.location_name, '-') as display_location, 
            COALESCE(t.type, '-') as type_name, 
            COALESCE(hs.history_stock, 0) as stock 
            FROM as_electric e 
            LEFT JOIN (SELECT electric_id, SUM(qty_sisa) as history_stock FROM as_history GROUP BY electric_id) hs ON hs.electric_id = e.electric_id 
            LEFT JOIN as_location l ON l.id = e.location 
            LEFT JOIN as_electric_types t ON t.id = e.type_id 
            ORDER BY e.nama ASC";
            
    $all_electrics = $ci->db->query($sql)->result_array();
    
    // Grouping berdasarkan type_id
    $grouped = [];
    foreach ($all_electrics as $el) {
        $tid = $el['type_id'];
        if (!isset($grouped[$tid])) $grouped[$tid] = [];
        $grouped[$tid][] = $el;
    }
    ?>

    <div class="card-body p-4">
        
        <!-- Multi-Filter Section -->
        <div class="card bg-light border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small mb-1">Kategori</label>
                        <select class="form-select rounded-pill border-0 shadow-sm" id="filter_kategori">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategori_list as $kat) : ?>
                                <option value="<?= htmlspecialchars($kat['type']) ?>"><?= htmlspecialchars($kat['type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small mb-1">Lokasi Penyimpanan</label>
                        <select class="form-select rounded-pill border-0 shadow-sm" id="filter_lokasi">
                            <option value="">Semua Lokasi</option>
                            <?php foreach ($lokasi_list as $lok) : ?>
                                <option value="<?= htmlspecialchars($lok['location_name']) ?>"><?= htmlspecialchars($lok['location_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small mb-1">Pencarian Spesifik</label>
                        <div class="input-group shadow-sm rounded-pill">
                            <span class="input-group-text bg-white border-0 rounded-start-pill text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control border-0 rounded-end-pill" id="search_global" placeholder="Cari ID, Type, Brand...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Looping Utama Kategori -->
        <div id="kategori-container">
            <?php foreach ($kategori_list as $kat) : ?>
                <?php 
                $items = $grouped[$kat['id']] ?? []; 
                $imgPath = !empty($kat['image']) ? base_url('assets/img/electric_types/' . $kat['image']) : base_url('assets/img/electric-default.png');
                ?>
                <div class="kategori-block mb-5" id="kat-<?= $kat['id'] ?>">
                    
                    <!-- Header Kategori -->
                    <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-4 shadow-sm mb-3 border-start border-4 border-primary">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= $imgPath ?>" alt="icon" class="rounded-circle shadow-sm" style="width: 50px; height: 50px; object-fit: cover; background: #fff;">
                            <h5 class="m-0 fw-bold text-primary"><?= htmlspecialchars($kat['type']) ?></h5>
                        </div>
                        <?php if (is_admin()): ?>
                            <a href="<?= site_url('electric/add?type_id='.$kat['id']) ?>" class="btn btn-primary rounded-pill shadow-sm">
                                <i class="fas fa-plus me-1"></i> Tambah Barang
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Tabel Barang -->
                    <div class="table-responsive rounded-4 shadow-sm border border-light">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">ID Barang</th>
                                    <th width="35%">Spesifikasi (Nama - Type)</th>
                                    <th width="15%">Lokasi</th>
                                    <th width="20%">Stok & Batch</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr class="barang-row empty-row" data-kategori="<?= htmlspecialchars($kat['type']) ?>" data-lokasi="" data-text="">
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada barang di kategori ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($items as $el): ?>
                                        <tr class="text-center barang-row" 
                                            data-kategori="<?= htmlspecialchars($kat['type']) ?>" 
                                            data-lokasi="<?= htmlspecialchars($el['display_location'] ?? '-') ?>" 
                                            data-text="<?= htmlspecialchars(strtolower($el['electric_id'] . ' ' . $el['nama'] . ' ' . $el['type'] . ' ' . ($el['brand'] ?? ''))) ?>">
                                            <td><?= $no++ ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($el['electric_id']) ?></span></td>
                                            
                                            <td class="text-start align-middle">
                                                <div class="fw-bold text-dark" style="font-size: 1.05rem;">
                                                    <?= htmlspecialchars(!empty($el['type']) && $el['type'] !== '-' ? $el['type'] : $el['nama']) ?> 
                                                </div>
                                                
                                                <?php
                                                $brandLabel = trim($el['brand'] ?? '');
                                                $specs = [];
                                                $voltagePart = trim(($el['voltage'] ?? '') . ($el['voltage_unit'] ?? ''));
                                                if ($voltagePart !== '') $specs[] = $voltagePart;
                                                if (!empty($el['ampere'])) $specs[] = ($el['ampere']) . 'A';
                                                $dayaPart = trim(($el['daya'] ?? '') . ($el['daya_unit'] ?? ''));
                                                if ($dayaPart !== '') $specs[] = $dayaPart;
                                                ?>
                                                <div class="text-muted" style="font-size: 0.85rem; margin-top: 6px;">
                                                    <strong>Brand:</strong> <?= htmlspecialchars($brandLabel !== '' ? $brandLabel : '-') ?>
                                                    <?php if (!empty($specs)): ?>
                                                        <span class="ms-2 border-start ps-2 border-secondary"><?= htmlspecialchars(implode(' | ', $specs)) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($el['display_location'] ?? '-') ?></span>
                                            </td>

                                            <td>
                                                <?php $stockVal = (int)($el['stock']); ?>
                                                <?php if ($stockVal <= 5): ?>
                                                    <span class="badge bg-danger fs-6 fw-bold mb-1 d-inline-block shadow-sm">Total Stok: <?= $stockVal ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success fs-6 fw-bold mb-1 d-inline-block shadow-sm">Total Stok: <?= $stockVal ?></span>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                $db_batches = $ci->db->select('qty_sisa, created_at')
                                                                     ->where('electric_id', $el['electric_id'])
                                                                     ->where('qty_sisa >', 0)
                                                                     ->where('type', 'Masuk')
                                                                     ->order_by('created_at', 'ASC')
                                                                     ->get('as_history')
                                                                     ->result_array();

                                                if (!empty($db_batches)) {
                                                    echo '<div style="font-size: 0.85rem; text-align: left; background: #f8f9fa; padding: 6px 10px; border-radius: 6px; border: 1px dashed #dee2e6; margin-top:5px; white-space: nowrap;">';
                                                    foreach ($db_batches as $idx => $b) {
                                                        $mbClass = ($idx < count($db_batches) - 1) ? 'mb-1' : '';
                                                        echo '<div class="' . $mbClass . ' text-secondary"><i class="fas fa-box-open text-primary me-1"></i> Batch ' . ($idx + 1) . ': <strong class="text-dark">' . $b['qty_sisa'] . '</strong></div>';
                                                    }
                                                    echo '</div>';
                                                } else {
                                                    echo '<div style="font-size: 0.85rem; text-align: center; color: #aaa; margin-top: 4px;">Kosong</div>';
                                                }
                                                ?>
                                            </td>

                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="<?= site_url('history/stock_card?electric_id=' . urlencode($el['electric_id'])) ?>" class="btn btn-sm btn-outline-info rounded-circle" data-bs-toggle="tooltip" title="Kartu Stok Digital">
                                                        <i class="fas fa-address-card"></i>
                                                    </a>
                                                    <?php if (is_admin()): ?>
                                                        <a href="<?= site_url('electric/edit/' . urlencode($el['electric_id'])) ?>" class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="tooltip" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('electric/upload'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="file" name="file" accept=".xlsx,.xls" class="form-control" required>
                    <small class="text-muted mt-2 d-block">Silakan upload file excel sesuai format.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 for Kategori Filter
        $('#filter_kategori').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Initialize Select2 for Lokasi Filter (Bonus)
        $('#filter_lokasi').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Multi-Filter Logic
        $('#filter_kategori, #filter_lokasi, #search_global').on('change keyup', function() {
            let filterKategori = $('#filter_kategori').val().toLowerCase();
            let filterLokasi = $('#filter_lokasi').val().toLowerCase();
            let searchQuery = $('#search_global').val().toLowerCase();

            $('.kategori-block').each(function() {
                let $block = $(this);
                let hasVisibleRow = false;

                $block.find('.barang-row').each(function() {
                    let $row = $(this);
                    
                    // Kalau baris kosong (belum ada barang), jangan difilter teks/lokasinya
                    if ($row.hasClass('empty-row')) {
                        if (filterKategori === "" || $row.data('kategori').toLowerCase() === filterKategori) {
                            // Biarkan tetap terlihat kalau filter lokasi/teks kosong, jika tidak sembunyikan
                            if (filterLokasi === "" && searchQuery === "") {
                                $row.show();
                                hasVisibleRow = true;
                            } else {
                                $row.hide();
                            }
                        } else {
                            $row.hide();
                        }
                        return; // continue to next row
                    }

                    let rowKategori = $row.data('kategori').toLowerCase();
                    let rowLokasi = $row.data('lokasi').toLowerCase();
                    let rowText = $row.data('text');

                    let matchKategori = (filterKategori === "" || rowKategori === filterKategori);
                    let matchLokasi = (filterLokasi === "" || rowLokasi === filterLokasi);
                    let matchSearch = (searchQuery === "" || rowText.includes(searchQuery));

                    if (matchKategori && matchLokasi && matchSearch) {
                        $row.show();
                        hasVisibleRow = true;
                    } else {
                        $row.hide();
                    }
                });

                // Sembunyikan Header Kategori jika tidak ada isinya yg match
                if (hasVisibleRow) {
                    $block.show();
                } else {
                    $block.hide();
                }
            });
        });



        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

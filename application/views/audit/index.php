<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div style="margin-top: 3rem; margin-bottom: 3rem;">
    <div class="container-fluid">

        <?php
            $CI = &get_instance();
            $daftar_lokasi = isset($daftar_lokasi) ? $daftar_lokasi : [];
            if (empty($daftar_lokasi) && $CI->db->table_exists('as_location')) {
                $CI->load->model('Location_model');
                $daftar_lokasi = $CI->Location_model->get_all();
            }
            $lokasiId = $CI->input->get('lokasi_id') ?? null;
        ?>

        <!-- ==================== TAMPILAN AWAL: GRID LOKASI ==================== -->
        <?php if (empty($lokasiId)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-4"><i class="fas fa-clipboard-check me-2"></i>Audit / Stock Opname</h3>
                            <p class="text-muted mb-0">Pilih lokasi gudang untuk melakukan opname dan verifikasi stok fisik.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if (is_admin() || is_manajer_oe()): ?>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#downloadHasilModal">
                                    <i class="fas fa-file-invoice me-2"></i>Download Laporan Hasil Audit
                                </button>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#downloadModal">
                                    <i class="fas fa-file-excel me-2"></i>Download Blanko Stock Opname
                                </button>
                            <?php endif; ?>
                            <a href="<?= base_url('index.php/dashboard'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Download Hasil Audit -->
            <div class="modal fade" id="downloadHasilModal" tabindex="-1" aria-labelledby="downloadHasilModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="<?= site_url('audit/export_hasil_audit') ?>" method="GET">
                      <div class="modal-header">
                        <h5 class="modal-title" id="downloadHasilModalLabel"><i class="fas fa-file-invoice me-2"></i>Download Laporan Hasil Audit (Hari Ini)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="text-muted">Pilih lokasi laporan hasil audit hari ini yang ingin diunduh:</p>
                        <div class="form-check mb-3 bg-light p-2 rounded">
                            <input class="form-check-input ms-1" type="checkbox" id="checkAllHasil" onclick="toggleAllHasil(this)">
                            <label class="form-check-label fw-bold ms-2" style="cursor:pointer;" for="checkAllHasil">Pilih Semua Lokasi</label>
                        </div>
                        <hr>
                        <div style="max-height: 250px; overflow-y: auto;" class="px-2">
                            <?php foreach ($daftar_lokasi as $l): 
                                $lid = $l['lokasi_id'] ?? $l['id'] ?? $l['lokasiId'] ?? ''; 
                                $locName = htmlspecialchars($l['nama_lokasi'] ?? $l['location_name'] ?? $l['nama'] ?? 'Lokasi');
                            ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input hasil-checkbox" type="checkbox" name="lokasi_id[]" value="<?= $lid ?>" id="hasil_<?= $lid ?>">
                                <label class="form-check-label" style="cursor:pointer;" for="hasil_<?= $lid ?>"><?= $locName ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                      </div>
                      <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-file-invoice me-2"></i>Download Excel/CSV</button>
                      </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Modal Download Multiple -->
            <div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="<?= site_url('audit/export_audit') ?>" method="GET">
                      <div class="modal-header">
                        <h5 class="modal-title" id="downloadModalLabel"><i class="fas fa-download me-2"></i>Download Format Stock Opname</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="text-muted">Pilih lokasi yang ingin dicetak format pengecekan fisiknya:</p>
                        <div class="form-check mb-3 bg-light p-2 rounded">
                            <input class="form-check-input ms-1" type="checkbox" id="checkAllLoc" onclick="toggleAllLoc(this)">
                            <label class="form-check-label fw-bold ms-2" style="cursor:pointer;" for="checkAllLoc">Pilih Semua Lokasi</label>
                        </div>
                        <hr>
                        <div style="max-height: 250px; overflow-y: auto;" class="px-2">
                            <?php foreach ($daftar_lokasi as $l): 
                                $lid = $l['lokasi_id'] ?? $l['id'] ?? $l['lokasiId'] ?? ''; 
                                $locName = htmlspecialchars($l['nama_lokasi'] ?? $l['location_name'] ?? $l['nama'] ?? 'Lokasi');
                            ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input loc-checkbox" type="checkbox" name="lokasi_id[]" value="<?= $lid ?>" id="loc_<?= $lid ?>">
                                <label class="form-check-label" style="cursor:pointer;" for="loc_<?= $lid ?>"><?= $locName ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                      </div>
                      <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-file-excel me-2"></i>Download Excel/CSV</button>
                      </div>
                  </form>
                </div>
              </div>
            </div>
            <script>
            function toggleAllLoc(source) {
                let checkboxes = document.querySelectorAll('.loc-checkbox');
                for(let i=0; i<checkboxes.length; i++) {
                    checkboxes[i].checked = source.checked;
                }
            }
            function toggleAllHasil(source) {
                let checkboxes = document.querySelectorAll('.hasil-checkbox');
                for(let i=0; i<checkboxes.length; i++) {
                    checkboxes[i].checked = source.checked;
                }
            }
            </script>

            <?php if (empty($daftar_lokasi)): ?>
                <div class="alert alert-warning text-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Belum ada lokasi gudang yang terdaftar.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($daftar_lokasi as $l): 
                        $lid = $l['lokasi_id'] ?? $l['id'] ?? $l['lokasiId'] ?? ''; 
                        $locName = htmlspecialchars($l['nama_lokasi'] ?? $l['location_name'] ?? $l['nama'] ?? 'Lokasi');
                        $locDesc = htmlspecialchars($l['description'] ?? $l['keterangan'] ?? '');
                    ?>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <a href="<?= base_url('index.php/audit?lokasi_id=' . $lid); ?>" style="text-decoration: none; color: inherit;">
                                <div class="card border-0 shadow-sm rounded-4 h-100 transition-all" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center text-center">
                                        <div class="mb-3" style="font-size: 2.5rem; color: #17a2b8;">
                                            <i class="fas fa-boxes"></i>
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
                </div>
            <?php endif; ?>

        <!-- ==================== TAMPILAN FORM: AUDIT DATA ==================== -->
        <?php else: ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-clipboard-check me-2"></i>Audit / Stock Opname</h3>
                            <p class="text-muted mb-0">Lakukan verifikasi stok fisik dengan sistem untuk lokasi ini</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('index.php/audit'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Ganti Lokasi
                            </a>
                            <?php if (is_admin() || is_manajer_oe()): ?>
                                <a href="<?= site_url('audit/export_audit?lokasi_id=' . $lokasiId); ?>" class="btn btn-success" title="Download Hasil Audit (CSV)">
                                    <i class="fas fa-download me-2"></i>Download Laporan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                        <input id="search-audit-item" type="search" class="form-control border-0 bg-light rounded-end-3" placeholder="Cari barang berdasarkan nama, ID, atau brand..." onkeyup="filterAuditRows()" />
                    </div>
                </div>
            </div>

            <!-- Audit Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>ID Barang</th>
                                        <th>Nama Barang</th>
                                        <th class="text-center">Stok Sistem</th>
                                        <th class="text-center">Stok Fisik</th>
                                        <th class="text-center">Selisih</th>
                                        <th>Keterangan / Alasan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $no = 1;
                                        if (!empty($electrics) && is_array($electrics)) {
                                            foreach ($electrics as $item):
                                                $stok = (int)($item['total_amount'] ?? $item['system_stock'] ?? 0);
                                                $electricId = htmlspecialchars($item['electric_id'] ?? '');
                                                $tipe = htmlspecialchars(!empty($item['type']) && $item['type'] !== '-' ? $item['type'] : '');
                                                $kategori = htmlspecialchars(!empty($item['nama']) ? $item['nama'] : '');
                                                $nama = $tipe ? $tipe : ($kategori ?: '-');
                                                $detailHtml = $tipe && $kategori && $tipe !== $kategori ? "<div class='fw-bold'>{$tipe}</div><small class='text-muted'><i class='fas fa-tag me-1'></i>{$kategori}</small>" : "<div class='fw-bold'>{$nama}</div>";
                                                $savedNote = htmlspecialchars($item['note'] ?? '');
                                                $isManager = $this->session->userdata('role') == 'Manajer OE';
                                                $rowId = 'row-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $electricId);

                                    ?>
                                    <tr class="audit-row" id="<?= $rowId; ?>" data-search-text="<?= strtolower($electricId . ' ' . $nama); ?>">
                                        <td class="ps-4 fw-bold"><?= $no++; ?></td>
                                        <td><small class="font-monospace"><?= $electricId ?: '-'; ?></small></td>
                                        <td><?= $detailHtml; ?></td>
                                        <td class="text-center stok-sistem" id="stok-sistem-<?= $rowId; ?>">
                                            <span class="badge bg-info"><?= $stok; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" 
                                                   id="input-fisik-<?= $rowId; ?>" 
                                                   class="form-control input-fisik text-center" 
                                                   value="<?= $stok ?>" 
                                                   style="width:100px;margin:auto;" 
                                                   min="0" 
                                                   <?= $isManager ? 'disabled' : ''; ?> 
                                                   onkeyup="hitungSelisih('<?= $rowId; ?>')" 
                                                   oninput="hitungSelisih('<?= $rowId; ?>')" />
                                        </td>
                                        <td class="text-center text-selisih fw-bold" id="selisih-<?= $rowId; ?>" style="color: black;">+0</td>
                                        <td>
                                            <?php if ($isManager): ?>
                                                <div class="text-muted small"><?= $savedNote !== '' ? $savedNote : '-'; ?></div>
                                            <?php else: ?>
                                                <select class="form-select form-select-sm select-alasan" 
                                                        id="alasan-<?= $rowId; ?>" 
                                                        disabled>
                                                    <option value="">-- Pilih Alasan --</option>
                                                    <option value="Barang Rusak / Broken">Barang Rusak</option>
                                                    <option value="Selisih Hilang (Lupa Catat)">Hilang / Lupa Catat</option>
                                                    <option value="Kelebihan Fisik (Surplus)">Kelebihan Fisik</option>
                                                </select>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($this->session->userdata('role') == 'Staf Gudang'): ?>
                                                <button type="button" 
                                                        class="btn btn-success btn-sm btn-simpan" 
                                                        onclick="simpanAdjustment('<?= $rowId; ?>')">
                                                    <i class="fas fa-save me-1"></i>Simpan
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; } else { ?>
                                        <tr><td colspan="8" class="text-center text-muted p-4"><i class="fas fa-inbox me-2"></i>Tidak ada barang untuk lokasi ini.</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .audit-card {
        transition: all 0.2s ease;
    }
    .audit-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
    }
    .form-select-sm {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
</style>

<script>
// A. Live Search Filter
function filterAuditRows() {
    const searchInput = document.getElementById('search-audit-item');
    const filterValue = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll('.audit-row');

    rows.forEach(row => {
        const searchText = row.getAttribute('data-search-text');
        if (searchText.indexOf(filterValue) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// B. Real-Time Kalkulasi Selisih
function hitungSelisih(rowId) {
    const sistemElem = document.getElementById('stok-sistem-' + rowId);
    const inputFisikElem = document.getElementById('input-fisik-' + rowId);
    const selisihElem = document.getElementById('selisih-' + rowId);
    const alasanElem = document.getElementById('alasan-' + rowId);

    if (!sistemElem || !inputFisikElem || !selisihElem) {
        return;
    }

    const sistem = parseInt(sistemElem.textContent.trim()) || 0;
    const fisik = parseInt(inputFisikElem.value) || 0;
    const selisih = fisik - sistem;

    // Update Teks dan Warna Selisih
    if (selisih > 0) {
        selisihElem.textContent = '+' + selisih;
        selisihElem.style.color = 'green';
    } else if (selisih < 0) {
        selisihElem.textContent = selisih;
        selisihElem.style.color = 'red';
    } else {
        selisihElem.textContent = '+0';
        selisihElem.style.color = 'black';
    }

    // Aktifkan/Matikan Dropdown Alasan
    if (alasanElem) {
        if (selisih !== 0) {
            alasanElem.disabled = false;
            alasanElem.innerHTML = '<option value="">-- Pilih Alasan --</option>';
            if (selisih > 0) {
                alasanElem.innerHTML += '<option value="Kelebihan Fisik (Surplus)">Kelebihan Fisik / Kelebihan Hitung</option>';
                alasanElem.innerHTML += '<option value="Lupa Catat Barang Masuk">Lupa Catat Barang Masuk</option>';
                alasanElem.innerHTML += '<option value="Salah Penempatan Rak">Salah Penempatan Rak (Nyasar)</option>';
            } else {
                alasanElem.innerHTML += '<option value="Barang Rusak / Broken">Barang Rusak / Afkir</option>';
                alasanElem.innerHTML += '<option value="Selisih Hilang (Lupa Catat)">Hilang / Lupa Catat Barang Keluar</option>';
                alasanElem.innerHTML += '<option value="Terpakai Darurat Tanpa Laporan">Terpakai Darurat Tanpa Laporan</option>';
            }
        } else {
            alasanElem.disabled = true;
            alasanElem.innerHTML = '<option value="">-- Pilih Alasan --</option>';
        }
    }
}

// C. Tombol Simpan
function simpanAdjustment(rowId) {
    const selisihElem = document.getElementById('selisih-' + rowId);
    const inputFisikElem = document.getElementById('input-fisik-' + rowId);
    const alasanElem = document.getElementById('alasan-' + rowId);
    const trRow = document.getElementById(rowId);

    if (!selisihElem || !inputFisikElem || !trRow) {
        alert('Data tidak lengkap!');
        return;
    }

    // Extract electric_id dari td (second td dalam row)
    const electricIdElem = trRow.querySelector('td:nth-child(2) small');
    const electricId = electricIdElem ? electricIdElem.textContent.trim() : null;

    if (!electricId) {
        alert('ID Barang tidak ditemukan!');
        return;
    }

    const selisihText = selisihElem.textContent.trim();
    const fisik = parseInt(inputFisikElem.value) || 0;
    const alasan = alasanElem ? alasanElem.value : '';
    
    // Get lokasi_id from URL
    const urlParams = new URLSearchParams(window.location.search);
    const lokasiId = urlParams.get('lokasi_id') || null;

    // Validasi
    if (selisihText !== '+0' && !alasan) {
        alert('Alasan selisih wajib dipilih!');
        if (alasanElem) alasanElem.focus();
        return;
    }

    // Send AJAX POST to adjust method
    const formData = new FormData();
    formData.append('electric_id', electricId);
    formData.append('counted', fisik);
    formData.append('note', alasan);
    formData.append('location_id', lokasiId);

    fetch('<?= site_url('audit/adjust'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menyimpan data!');
    });
}
</script>

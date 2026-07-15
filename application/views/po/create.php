<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 1000px;">
    <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
        <h4 class="m-0"><?= htmlspecialchars($title) ?></h4>
        <a href="<?= site_url('po') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">Kembali</a>
    </div>
    <div class="card-body p-4">
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= site_url('po/store') ?>" method="post" id="poForm">
            <div class="row g-3 mb-4">

                <div class="col-md-6">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select select2" required>
                        <option value="">-- Pilih Supplier --</option>
                        <?php foreach($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Order <span class="text-danger">*</span></label>
                    <input type="date" name="order_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <h5 class="mb-3 border-bottom pb-2">Daftar Barang Dipesan</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="45%">Barang</th>
                            <th width="20%">Jumlah</th>
                            <th width="25%">Harga Satuan (Rp)</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="electric_id[]" class="form-select select2-item" required>
                                    <option value="">-- Pilih Barang --</option>
                                      <?php foreach($electrics as $e): ?>
                                          <?php
                                              $name = trim($e['nama'] ?? '');
                                              $type = trim($e['type'] ?? '');
                                              $brand = trim($e['brand'] ?? '');
                                              
                                              $specs = [];
                                              if (!empty($e['voltage'])) $specs[] = $e['voltage'] . ($e['voltage_unit'] ?? '');
                                              if (!empty($e['ampere'])) $specs[] = $e['ampere'] . 'A';
                                              if (!empty($e['daya'])) $specs[] = $e['daya'] . ($e['daya_unit'] ?? 'W');
                                              
                                              $brandStr = !empty($brand) ? ' | BRAND: ' . strtoupper($brand) : '';
                                              $specStr = !empty($specs) ? ' | SPEC: ' . implode(', ', $specs) : '';
                                              $typeStr = !empty($type) ? ' - ' . strtoupper($type) : '';
                                              
                                              $label = strtoupper($name) . $typeStr . $brandStr . $specStr;
                                          ?>
                                          <option value="<?= $e['electric_id'] ?>">
                                              <?= htmlspecialchars($label) ?>
                                          </option>
                                      <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="qty_ordered[]" class="form-control" min="1" required>
                            </td>
                            <td>
                                <input type="number" name="price[]" class="form-control" min="0" step="0.01" value="0">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mb-4">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="addRow">
                    <i class="fas fa-plus"></i> Tambah Barang Lain
                </button>
            </div>

            <div class="text-end border-top pt-3">
                <button type="submit" class="btn btn-success rounded-pill px-5">Simpan PO</button>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });
    $('.select2-item').select2({ width: '100%' });

    $('#addRow').click(function() {
        var row = $('#itemsTable tbody tr:first').clone();
        
        // Reset values
        row.find('input[type="number"]').val('');
        row.find('input[name="price[]"]').val('0');
        
        // Fix select2 cloning issue
        row.find('.select2-container').remove();
        row.find('select').removeClass('select2-hidden-accessible');
        row.find('select').val('');
        
        $('#itemsTable tbody').append(row);
        
        // Re-init select2 for the new row
        row.find('.select2-item').select2({ width: '100%' });
    });

    $(document).on('click', '.remove-row', function() {
        if ($('#itemsTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('Minimal harus ada 1 barang!');
        }
    });

    // Mencegah double-submission pada form PO
    $('form').on('submit', function() {
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);
        btn.html('Menyimpan...');
    });
});
</script>

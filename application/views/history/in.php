<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 2rem; max-width: 900px;">
    <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
        <h4 class="m-0">Procurement - Catat Barang Masuk (Ad-hoc)</h4>
        <a href="<?= site_url('dashboard') ?>" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> Untuk pengadaan barang berencana, silakan gunakan menu <strong>Purchase Order (PO)</strong>. Form ini hanya untuk penerimaan ad-hoc (tanpa PO resmi).
        </div>
        <?php $electrics = $electrics ?? []; $locations = $locations ?? []; ?>
        <form action="" method="post" class="row g-3">


            <div class="col-12 col-md-6">
                <label for="proc_location" class="form-label">Lokasi / Rak</label>
                <?php
                    $CI = &get_instance();
                    if (empty($locations)) {
                        if ($CI->db->table_exists('as_location')) {
                            $locations = $CI->db->select('id, location_name')->order_by('location_name', 'ASC')->get('as_location')->result_array();
                        } else {
                            $locations = [];
                        }
                    }
                ?>
                <select id="proc_location" name="location_id" class="form-select">
                    <option value="">-- Pilih Lokasi / Rak --</option>
                    <?php foreach ($locations as $loc) : ?>
                        <option value="<?= htmlspecialchars($loc['id']) ?>"><?= htmlspecialchars($loc['location_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <label for="proc_barang" class="form-label">Nama Barang</label>
                <select id="proc_barang" name="electric_id" class="form-select select2" required disabled>
                    <option value="">-- Pilih lokasi terlebih dahulu --</option>
                </select>
            </div>

            <div class="col-12 col-md-6">
                <label for="qty" class="form-label">Jumlah</label>
                <input id="qty" name="qty" type="number" min="1" class="form-control" required>
            </div>

            <!-- Lokasi removed from procurement form — use location from as_electric when recording -->

            <div class="col-12">
                <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="3" placeholder="Catatan tambahan untuk batch, nota, dsb"></textarea>
            </div>

            <div class="col-12 text-end">
                <button class="btn btn-success rounded-pill px-4" type="submit">Simpan Stok Masuk</button>
            </div>
        </form>
    </div>
</div>

<!-- Select2 assets (requires jQuery) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        try {
            $('.select2').select2({ width: '100%', placeholder: '-- Pilih Barang --' });
        } catch (e) {
            console.warn('Select2 init failed', e);
        }
    });
</script>
<script>
    (function($){
        $(function(){
            var $loc = $('#proc_location');
            var $barang = $('#proc_barang');

            $loc.on('change', function(){
                var lok = $(this).val();
                $barang.prop('disabled', true);
                // show loading option
                if ($barang.data('select2')) {
                    $barang.html('<option value="">Memuat...</option>').trigger('change.select2');
                } else {
                    $barang.html('<option value="">Memuat...</option>');
                }

                if (!lok) {
                    if ($barang.data('select2')) $barang.html('<option value="">-- Pilih lokasi terlebih dahulu --</option>').trigger('change.select2');
                    else $barang.html('<option value="">-- Pilih lokasi terlebih dahulu --</option>');
                    $barang.prop('disabled', true);
                    return;
                }

                $.ajax({
                    url: '<?= site_url('procurement/get_barang_by_lokasi/') ?>' + encodeURIComponent(lok),
                    method: 'GET',
                    
                    dataType: 'json',
                    success: function(resp){
                        $barang.empty();
                        if (Array.isArray(resp) && resp.length) {
                            $barang.append('<option value="">-- Pilih Barang --</option>');
                            resp.forEach(function(it){
                                var opt = new Option(it.text, it.id, false, false);
                                $barang.append(opt);
                            });
                            $barang.prop('disabled', false);
                        } else {
                            $barang.append('<option value="">Tidak ada barang di lokasi ini</option>');
                            $barang.prop('disabled', true);
                        }
                        // refresh select2 if used
                        try { $barang.trigger('change.select2'); } catch(e){}
                    },
                    error: function(){
                        $barang.empty().append('<option value="">Gagal memuat data</option>').prop('disabled', true);
                    }
                });
            });
        });
    })(jQuery);
</script>

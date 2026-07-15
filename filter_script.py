import re

path = r'c:\xampp\htdocs\electrical-system\application\views\electric\index.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# First, we need to fetch locations. We can do this in the top PHP block.
# Let's find the PHP block:
#  = ->db->get('as_electric_types')->result_array();
php_insert = ''' = ->db->get('as_electric_types')->result_array();
     = ->db->get('as_location')->result_array();'''
content = content.replace("$" + "kategori_list = ->db->get('as_electric_types')->result_array();", php_insert)

# Now, let's replace the old single filter dropdown with the new multi-filter block.
old_filter_pattern = re.compile(r'<!-- Filter Dropdown \(jQuery\) -->.*?</div>', re.DOTALL)
new_filter_html = '''<!-- Multi-Filter Section -->
        <div class="card bg-light border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small mb-1">Kategori</label>
                        <select class="form-select rounded-pill border-0 shadow-sm" id="filter_kategori">
                            <option value="">Semua Kategori</option>
                            <?php foreach ( as ) : ?>
                                <option value="<?= htmlspecialchars(['type']) ?>"><?= htmlspecialchars(['type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small mb-1">Lokasi Penyimpanan</label>
                        <select class="form-select rounded-pill border-0 shadow-sm" id="filter_lokasi">
                            <option value="">Semua Lokasi</option>
                            <?php foreach ( as ) : ?>
                                <option value="<?= htmlspecialchars(['location_name']) ?>"><?= htmlspecialchars(['location_name']) ?></option>
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
        </div>'''
content = re.sub(old_filter_pattern, new_filter_html, content)

# Now, we need to add data attributes to the <tr>.
# Find: <tr class="text-center">
# We should replace it with:
# <tr class="text-center barang-row" data-kategori="<?= htmlspecialchars(['type']) ?>" data-lokasi="<?= htmlspecialchars(['display_location'] ?? '-') ?>" data-text="<?= htmlspecialchars(strtolower(['electric_id'] . ' ' . ['nama'] . ' ' . ['type'] . ' ' . (['brand'] ?? ''))) ?>">

tr_pattern = re.compile(r'<tr class="text-center">(\s*<td><\?= \\+\+ \?></td>)')
new_tr = '''<tr class="text-center barang-row" 
                                            data-kategori="<?= htmlspecialchars(['type']) ?>" 
                                            data-lokasi="<?= htmlspecialchars(['display_location'] ?? '-') ?>" 
                                            data-text="<?= htmlspecialchars(strtolower(['electric_id'] . ' ' . ['nama'] . ' ' . ['type'] . ' ' . (['brand'] ?? ''))) ?>">\\1'''
content = re.sub(tr_pattern, new_tr, content)

# Replace the script at the bottom
script_pattern = re.compile(r'<script>\s*\$\(document\)\.ready\(function\(\) \{.*?\}\);\s*</script>', re.DOTALL)
new_script = '''<script>
    .ready(function() {
        // Multi-Filter Logic
        #filter_kategori, #filter_lokasi, #search_global.on('change keyup', function() {
            let filterKategori = #filter_kategori.val().toLowerCase();
            let filterLokasi = #filter_lokasi.val().toLowerCase();
            let searchQuery = #search_global.val().toLowerCase();

            .kategori-block.each(function() {
                let  = ;
                let hasVisibleRow = false;

                .find('.barang-row').each(function() {
                    let  = ;
                    let rowKategori = .data('kategori').toLowerCase();
                    let rowLokasi = .data('lokasi').toLowerCase();
                    let rowText = .data('text');

                    // Check conditions
                    let matchKategori = (filterKategori === "" || rowKategori === filterKategori);
                    let matchLokasi = (filterLokasi === "" || rowLokasi === filterLokasi);
                    let matchSearch = (searchQuery === "" || rowText.includes(searchQuery));

                    if (matchKategori && matchLokasi && matchSearch) {
                        .show();
                        hasVisibleRow = true;
                    } else {
                        .hide();
                    }
                });

                // Tampilkan atau sembunyikan seluruh blok kategori
                if (hasVisibleRow) {
                    .show();
                } else {
                    .hide();
                }
            });
        });

        // Trigger filter on load to handle back navigation or initial states
        #filter_kategori.trigger('change');

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>'''
content = re.sub(script_pattern, new_script, content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Multi-filter implemented successfully")

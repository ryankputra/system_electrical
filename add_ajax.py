import re

path = r'c:\xampp\htdocs\electrical-system\application\views\electric\index.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add Kategori dropdown
pattern = r'(<div class="mt-2 d-flex gap-2">)'
replacement = r'''\1
                            <?php
                             =& get_instance();
                             = ->db->get('as_electric_types')->result_array();
                             = ->input->get('type_id') ?? '';
                            ?>
                            <select class="form-select form-select-sm" id="kategori-ajax-filter" aria-label="Filter Kategori" style="max-width: 200px;">
                                <option value="">Semua Kategori</option>
                                <?php foreach ( as ) : ?>
                                    <option value="<?= ['id'] ?>" <?=  == ['id'] ? 'selected' : '' ?>><?= htmlspecialchars(['type']) ?></option>
                                <?php endforeach; ?>
                            </select>'''

content = re.sub(pattern, replacement, content)

# Add Ajax script at the end
script = r'''
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kategoriFilter = document.getElementById('kategori-ajax-filter');
        if (kategoriFilter) {
            kategoriFilter.addEventListener('change', function() {
                const typeId = this.value;
                const url = typeId ? '<?= site_url("electric") ?>?type_id=' + typeId : '<?= site_url("electric") ?>';
                
                // Fetch the new page using AJAX
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Replace tbody
                        const newTbody = doc.querySelector('table tbody');
                        if (newTbody) {
                            document.querySelector('table tbody').innerHTML = newTbody.innerHTML;
                        }
                        
                        // Replace pagination links
                        const newPagination = doc.querySelector('.card-footer .d-flex');
                        if (newPagination) {
                            document.querySelector('.card-footer .d-flex').innerHTML = newPagination.innerHTML;
                        }
                        
                        // Update URL silently
                        window.history.pushState({path: url}, '', url);
                    })
                    .catch(error => console.error('Error fetching data:', error));
            });
        }
    });
</script>
'''
content = content + script

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

import re

path = r'c:\xampp\htdocs\electrical-system\application\views\history\index.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add Aksi header
content = content.replace('<th>Stok Sistem</th>\n\t\t\t\t\t\t\t\t</tr>', '<th>Stok Sistem</th>\n\t\t\t\t\t\t\t\t\t<th>Aksi</th>\n\t\t\t\t\t\t\t\t</tr>')

# Add Cetak Stiker button
row_replacement = r'''<td><span class="badge bg-light text-dark"><?= number_format(); ?></span></td>
\t\t\t\t\t\t\t\t\t\t\t<td>
\t\t\t\t\t\t\t\t\t\t\t\t<?php if (strtolower(['type'] ?? '') === 'masuk'): ?>
\t\t\t\t\t\t\t\t\t\t\t\t\t<a href="<?= site_url('history/print_sticker/' . ['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-print"></i> Cetak Stiker</a>
\t\t\t\t\t\t\t\t\t\t\t\t<?php endif; ?>
\t\t\t\t\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t\t\t\t\t</tr>'''

content = content.replace('<td><span class="badge bg-light text-dark"><?= number_format(); ?></span></td>\n\t\t\t\t\t\t\t\t\t\t</tr>', row_replacement)

# Update colspan
content = content.replace('colspan="15"', 'colspan="16"')

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated history/index.php")

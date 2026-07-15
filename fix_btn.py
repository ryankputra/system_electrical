import re

path = r'c:\xampp\htdocs\electrical-system\application\views\history\index.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Aksi column if not exists
if '<th>Aksi</th>' not in content:
    content = re.sub(r'<th>Stok Sistem</th>\s*</tr>', r'<th>Stok Sistem</th>\n\t\t\t\t\t\t\t\t\t<th>Aksi</th>\n\t\t\t\t\t\t\t\t</tr>', content)

# Replace colspan
content = re.sub(r'colspan="15"', r'colspan="16"', content)

# Append Button
row_replacement = r'''<td><span class="badge bg-light text-dark"><?= number_format(); ?></span></td>
\t\t\t\t\t\t\t\t\t\t\t<td>
\t\t\t\t\t\t\t\t\t\t\t\t<?php if (strpos(strtolower(['type'] ?? ''), 'masuk') !== false || strtolower(['type'] ?? '') === 'in'): ?>
\t\t\t\t\t\t\t\t\t\t\t\t\t<a href="<?= site_url('history/print_sticker/' . ['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-print"></i> Cetak Stiker</a>
\t\t\t\t\t\t\t\t\t\t\t\t<?php endif; ?>
\t\t\t\t\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t\t\t\t\t</tr>'''

content = re.sub(r'<td><span class="badge bg-light text-dark"><\?= number_format\(\\); \?></span></td>\s*</tr>', row_replacement, content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed Aksi column in history/index.php")

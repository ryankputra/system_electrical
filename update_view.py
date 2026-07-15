import re

path = r'c:\xampp\htdocs\electrical-system\application\views\history\print_sticker.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace static content with dynamic variables
# Name and Spec
content = re.sub(r'<div class="item-name">.*?</div>', r'<div class="item-name"><?= htmlspecialchars([\'nama\'] . (\' - \' . [\'type\'])) ?></div>', content)
content = re.sub(r'<div class="item-spec">.*?</div>', r'<div class="item-spec">Brand: <?= htmlspecialchars([\'brand\'] ?? \'-\') ?> | Spec: <?= htmlspecialchars(([\'voltage\'] ?? \'\') . ([\'voltage_unit\'] ?? \'\')) ?>, <?= htmlspecialchars(([\'ampere\'] ?? \'\') ? [\'ampere\'] . \'A\' : \'\') ?></div>', content)

# Batch Box
content = re.sub(r'<div class="batch-box">.*?</div>', r'<div class="batch-box">BATCH <?= htmlspecialchars([\'id\']) ?></div>', content)

# Date Box
content = re.sub(r'28/06/2026', r'<?= date(\'d/m/Y\', strtotime([\'created_at\'])) ?>', content)

# Color Band
content = re.sub(r'<div class="color-band" style="background-color: #E74C3C;">\s*BULAN MASUK: JANUARI\s*</div>', r'''<div class="color-band" style="background-color: <?=  ?>;">
            BULAN MASUK: <?=  ?>
        </div>''', content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated print_sticker.php")

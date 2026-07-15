import re

path = r'c:\xampp\htdocs\electrical-system\application\views\electric\edit.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Delete Nama block
pattern_nama = re.compile(r'<!-- Nama Input Section -->.*?</div>\s*</div>', re.DOTALL)
content = re.sub(pattern_nama, '', content)

# Delete Image block
pattern_image = re.compile(r'<!-- Gambar Upload Section -->.*?</script>', re.DOTALL)
content = re.sub(pattern_image, '</form>\n    </div>\n</div>\n\n<script>', content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

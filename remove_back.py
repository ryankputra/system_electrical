import re

path = r'c:\xampp\htdocs\electrical-system\application\views\electric\index.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Remove the 'Kembali ke Kategori' button
content = re.sub(r'<a href="<\?= site_url\(\'electric/type\'\); \?>" class="btn btn-outline-secondary\s+rounded-pill">Kembali ke Kategori</a>', '', content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

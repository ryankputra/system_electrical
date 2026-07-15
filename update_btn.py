import re

path = r'c:\xampp\htdocs\electrical-system\application\views\history\index.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix the condition to match any string containing 'masuk'
content = content.replace("=== 'masuk'", "!== false")
content = content.replace("strtolower(['type'] ?? '')", "strpos(strtolower(['type'] ?? ''), 'masuk')")

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated condition in history/index.php")

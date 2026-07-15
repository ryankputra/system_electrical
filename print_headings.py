import re

with open('bab4_actual_body.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# Print lines that look like headings
lines = text.split('\n')
for i, line in enumerate(lines):
    if re.match(r'^(4\.\d+|[A-Z\s]{5,})', line.strip()):
        print(f"Line {i}: {line.strip()}")

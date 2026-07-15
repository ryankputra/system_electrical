with open('docx_blocks.txt', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Find blocks containing BAB IV and BAB V
for idx, line in enumerate(lines):
    if 'BAB IV' in line or 'BAB V' in line or 'Hasil Penelitian' in line:
        print(f"Line {idx}: {line.strip()}")

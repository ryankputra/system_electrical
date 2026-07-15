with open('docx_blocks.txt', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for idx in range(370, 395):
    if idx < len(lines):
        print(lines[idx].strip())

with open('docx_blocks.txt', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for idx, line in enumerate(lines):
    if 'Tabel 4.1' in line:
        # print blocks around it
        for j in range(max(0, idx-5), min(len(lines), idx+15)):
            print(lines[j].strip())

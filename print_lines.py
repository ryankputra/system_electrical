with open('bab4_actual_body.txt', 'r', encoding='utf-8') as f:
    text = f.read()

lines = text.split('\n')
for idx in range(80, 180):
    if idx < len(lines):
        # Print non-empty lines
        l = lines[idx].strip()
        if l:
            print(f"Line {idx}: {l[:120]}")

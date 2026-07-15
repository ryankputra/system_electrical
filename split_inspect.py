with open('bab4_actual_body.txt', 'r', encoding='utf-8') as f:
    text = f.read()

import re

# Let's see the end of the file or split it by major parts
parts = re.split(r'(Pengujian Parameter Optimasi Stok \(Pendekatan Queue-Based Processing / FIFO\)|Pembahasan)', text, flags=re.IGNORECASE)

print(f"Split into {len(parts)} parts.")
for i, p in enumerate(parts):
    print(f"Part {i} length: {len(p)}")
    print(p[:200])
    print("...")

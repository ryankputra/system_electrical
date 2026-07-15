with open('bab4_actual_body.txt', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = list(re.finditer(r'Pengujian Parameter', text, re.IGNORECASE))
for m in matches:
    print(text[m.start():m.start()+1000])

with open('bab4_actual_body.txt', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m = re.search(r'Pengujian Parameter.*?(?=4\.5|BAB V)', text, re.IGNORECASE | re.DOTALL)
if m:
    print(m.group(0))

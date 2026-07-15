with open(r'C:\Users\Ryan\Documents\TA\diagrams\Revisi_Lengkap_BAB_IV.html', 'r', encoding='utf-8') as f:
    html = f.read()

# print HTML around 'Physical Data Model'
import re
match = re.search(r'Physical Data Model.*?(?=Hasil Penelitian)', html, re.DOTALL)
if match:
    print(match.group(0))
else:
    print("Not found")

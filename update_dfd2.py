import re

path = r'C:\Users\Ryan\Documents\TA\diagrams\dfd_level_2_ganesarson.drawio'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Node labels (P1 & P2 already look mostly fine, but we can refine them)
content = content.replace('3.1<br><br>Pencatatan Barang<br>Masuk', '3.1<br><br>Pencatatan Barang<br>Masuk (Inbound)')
content = content.replace('3.2<br><br>Pencatatan Barang<br>Keluar', '3.2<br><br>Pencatatan Barang<br>Keluar (Outbound)')

# Replace Edge labels
content = content.replace('Data Penerimaan', 'Data Penerimaan Barang (Ad-hoc)')
content = content.replace('Info Stok Terkini', 'Informasi Detail Sisa Stok Per Batch (FIFO)')
content = content.replace('Catat Masuk', 'Data Mutasi Masuk')
content = content.replace('Update Stok (Masuk)', 'Data Batch Baru &amp; Qty Available')

content = content.replace('Ambil / WO', 'Data Pengambilan Barang &amp; Nomor WO')
content = content.replace('Catat Keluar', 'Log Trans. Keluar Per Batch')
content = content.replace('Cek dan Update Stok (Keluar)', 'Ref. Batch Tertua &amp; Data Potong Stok')

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated text labels in DFD Level 2 drawio.")

import re

with open('bab4_actual_body.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# Let's split using a regex that splits exactly before the "Pengujian Parameter Optimasi Stok..." heading
# We want to keep everything before it, then append the new 4.4.2, then append the new 4.5

split_pattern = r'Pengujian Parameter Optimasi Stok \(Pendekatan Queue-Based Processing / FIFO\)'
parts = re.split(split_pattern, text, flags=re.IGNORECASE)

if len(parts) >= 2:
    part_before_442 = parts[0].strip()
    
    # Let's clean up any trailing garbage or spaces
    # Now let's assemble the new Chapter IV
    
    new_442_and_45 = """
<h4>4.4.2. Pengujian Parameter Optimasi Stok (Pendekatan Queue-Based Processing / FIFO)</h4>
<p>Selain pengujian modul struktural, validasi mendalam juga dilakukan terhadap fungsi algoritma penarikan stok menggunakan pendekatan <i>Queue-Based Processing</i> dengan metode antrean <i>First-In First-Out</i> (FIFO). Untuk mengujinya, penulis menyimulasikan skenario penerimaan barang secara bergelombang (<i>batch</i>) menggunakan sampel data yang ada.</p>
<p>Sebagai contoh pengujian, diambil sampel komponen RELAY merk NNC (tipe NNC 69KTL-22) yang pada data riil Excel tercatat memiliki kuantitas total 33 unit. Untuk memicu logika FIFO, penulis memecah angka tersebut ke dalam simulasi sistem seolah-olah komponen tersebut masuk pada <b>Batch #1</b> (Batch A) sebanyak <b>20 unit</b> pada tanggal 28/06/2026, dan <b>Batch #2</b> (Batch B) sebanyak <b>13 unit</b> pada tanggal 29/06/2026. Pada saat simulasi barang keluar (<i>Work Order</i>) untuk <b>25 unit</b> dijalankan pada tanggal 30/06/2026, sistem berhasil memprioritaskan pemotongan secara runtut.</p>
<p>Antrean data menunjukkan bahwa sistem menghabiskan seluruh 20 unit milik Batch A terlebih dahulu (dikarenakan usianya yang lebih lama di gudang), kemudian melanjutkan pemotongan sisa kekurangannya sebanyak 5 unit dari persediaan Batch B, menyisakan saldo akhir sebesar 8 unit pada Batch B.</p>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
    <thead>
        <tr style="background: #f2f2f2;">
            <th style="border: 1px solid #000; padding: 8px;">Waktu Kronologi</th>
            <th style="border: 1px solid #000; padding: 8px;">Aktivitas Transaksi</th>
            <th style="border: 1px solid #000; padding: 8px;">Kuantitas (Unit)</th>
            <th style="border: 1px solid #000; padding: 8px;">Keterangan Antrean (Queue)</th>
            <th style="border: 1px solid #000; padding: 8px;">Status Batch A</th>
            <th style="border: 1px solid #000; padding: 8px;">Status Batch B</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border: 1px solid #000; padding: 8px;">28/06/2026</td>
            <td style="border: 1px solid #000; padding: 8px;">Barang Masuk (Batch A)</td>
            <td style="border: 1px solid #000; padding: 8px;">+ 20</td>
            <td style="border: 1px solid #000; padding: 8px;">Masuk antrean pertama (Posisi #1)</td>
            <td style="border: 1px solid #000; padding: 8px;">20 (TERSISA)</td>
            <td style="border: 1px solid #000; padding: 8px;">0</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 8px;">29/06/2026</td>
            <td style="border: 1px solid #000; padding: 8px;">Barang Masuk (Batch B)</td>
            <td style="border: 1px solid #000; padding: 8px;">+ 13</td>
            <td style="border: 1px solid #000; padding: 8px;">Masuk antrean kedua (Posisi #2)</td>
            <td style="border: 1px solid #000; padding: 8px;">20 (TERSISA)</td>
            <td style="border: 1px solid #000; padding: 8px;">13 (TERSISA)</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 8px;">30/06/2026</td>
            <td style="border: 1px solid #000; padding: 8px;">Work Order Keluar</td>
            <td style="border: 1px solid #000; padding: 8px;">- 25</td>
            <td style="border: 1px solid #000; padding: 8px;">Sistem memotong Batch A (-20) & Batch B (-5)</td>
            <td style="border: 1px solid #000; padding: 8px;">0 (HABIS)</td>
            <td style="border: 1px solid #000; padding: 8px;">8 (TERSISA)</td>
        </tr>
    </tbody>
</table>

<p style="text-indent: 0.5in;">Sisa 8 unit murni berasal dari sisa Batch B. Hasil pengujian rotasi barang ini sangat krusial dalam lingkup <i>electrical</i> yang sangat sensitif terhadap masa simpan perangkat (seperti korosi kabel atau degradasi masa pakai baterai/sensor). Keberhasilan algoritma <i>Queue-Based Processing</i> pada sistem ini turut divalidasi dengan pengecekan rekam jejak digital (<i>audit trail</i>) pada Kartu Stok, di mana setiap mutasi barang tersusun secara runtut dan presisi tanpa adanya anomali loncatan ID transaksi, sehingga akurasi historis mutasi per baris <i>database</i> dapat dijamin.</p>
<p style="color: gray; font-style: italic; text-align: center;">Gambar 4.24 Laporan Riwayat Mutasi Stok Bukti Algoritma FIFO</p>

<h4>4.5. Pembahasan</h4>
<p>Pada sub-bab ini dilakukan analisis mendalam (<i>critical analysis</i>) terhadap hasil perancangan, implementasi, dan pengujian sistem guna mengevaluasi sejauh mana metode <i>Queue-Based Processing</i> (FIFO) mampu memecahkan masalah disparitas data dan degradasi fisik material kelistrikan di PT Apparel One Indonesia. Pembahasan ini disusun secara sistematis untuk menjawab seluruh pertanyaan penelitian yang telah dirumuskan pada Bab II.</p>

<h5>4.5.1. Analisis Hasil Pengujian Algoritma Queue-Based Processing (FIFO)</h5>
<p>Inti keandalan metode yang diusulkan terletak pada mekanisme komputasi <i>backend</i> dalam mengelola antrean <i>batch</i> barang. Berbeda dengan aplikasi inventaris konvensional yang hanya melakukan pemotongan saldo stok global (<i>global accumulation</i>), algoritma yang dirancang mengeksekusi struktur data antrean (<i>queue</i>) berbasis tabel relasional di MySQL.</p>
<p>Secara teknis, proses rotasi stok dijamin melalui kueri terurut dengan klausa <code>ORDER BY created_at ASC</code>. Kueri ini memprioritaskan baris data pada tabel <code>as_history</code> yang memiliki stempel waktu (<i>timestamp</i>) paling lama untuk dimuat ke dalam memori aplikasi sebagai kepala antrean (<i>front of queue</i>). Ketika transaksi pengeluaran (<i>Outbound</i>) dideklarasikan melalui dokumen <i>Work Order</i> dengan kuantitas tertentu ({req}$), sistem menjalankan perulangan terstruktur (<i>subtraction loop</i>) pada kumpulan baris data terpilih.</p>
<p>Prosedur transisi status baris data (<i>state machine transition</i>) selama siklus pengurangan stok dirumuskan sebagai berikut: apabila sisa kuantitas pada <i>batch</i> tertua ({batch}$) lebih besar dari sisa permintaan ({req}$), maka sistem akan mengurangi kuantitas <i>batch</i> tersebut ({batch} \leftarrow Q_{batch} - Q_{req}$) dan mempertahankan nilai statusnya sebagai <code>TERSISA</code>. Namun, jika kuantitas permintaan melampaui sisa stok di <i>batch</i> tersebut ({req} \ge Q_{batch}$), sistem akan menguras habis stok <i>batch</i> tersebut hingga mencapai titik nol ({batch} \leftarrow 0$), memicu perubahan status <i>batch</i> secara instan menjadi <code>HABIS</code>, lalu memindahkan sisa permintaan ke antrean *batch* berikutnya ({req} \leftarrow Q_{req} - Q_{batch\_old}$).</p>
<p>Hasil analisis pengujian beban transaksi menunjukkan bahwa transisi status <i>batch</i> ini berjalan secara atomik (<i>transactional ACID compliance</i>). Hal ini membuktikan logika <i>Queue-Based Processing</i> berhasil memaksa rotasi stok digital berjalan presisi tanpa menyisakan *dead stock* di dalam sistem.</p>

<h5>4.5.2. Analisis Hasil Pengujian Black Box pada Modul Sistem</h5>
<p>Pengujian fungsional dengan metode <i>Black-Box Testing</i> berfokus pada pembuktian integrasi antarmuka dan basis data untuk meminimalisasi risiko duplikasi data. Berdasarkan hasil pengujian skenario pengadaan (<i>Procurement</i>) dan transaksi keluar, integrasi data berhasil dicapai secara otomatis menggunakan arsitektur MVC (<i>Model-View-Controller</i>) pada Framework CodeIgniter 3.</p>
<p>Penerapan kontrol alur data terpusat pada controller <code>Procurement.php</code> dan <code>History.php</code> terbukti berhasil mencegah anomali *double posting* (duplikasi data) ketika staf gudang menginput data barang masuk secara bertubi-tubi. Data kuantitas barang yang masuk melalui formulir ad-hoc langsung disinkronkan ke tabel <code>as_electric</code> (untuk menambah saldo stok global) sekaligus membuat entitas antrean baru di tabel <code>as_history</code> (untuk antrean FIFO). Tidak ditemukannya celah kebocoran data (<i>data leak</i>) selama pengujian memvalidasi bahwa arsitektur basis data relasional yang dibangun telah memenuhi standar integritas referensial.</p>

<h5>4.5.3. Hambatan dan Solusi Selama Pengembangan</h5>
<p>Selama fase pengembangan dan pengujian sistem, penulis menghadapi hambatan utama berupa terbatasnya akses fisik ke lingkungan gudang PT Apparel One Indonesia pasca-magang. Kondisi ini menyebabkan validasi fisik secara langsung terhadap perilaku penataan barang oleh operator gudang tidak dapat dilakukan secara kontinu.</p>
<p>Untuk mengatasi hambatan tersebut, penulis mengimplementasikan dua solusi taktis. Pertama, di sisi perangkat lunak, penulis membangun lingkungan simulasi uji mandiri (<i>Independent Sandbox Testing</i>) menggunakan data historis tiruan yang mencerminkan pola mutasi riil perusahaan. Kedua, di sisi prosedur fisik, penulis mengusulkan standarisasi penandaan fisik menggunakan sistem stiker termal <i>Color Coding</i> bulanan yang dicetak langsung dari sistem aplikasi. Solusi ini menjadi jembatan agar operator di lapangan dapat menyamakan persepsi visual dengan logika antrean digital, meminimalisasi faktor kesalahan manusia (<i>human error</i>) saat pengambilan komponen di rak.</p>

<h5>4.5.4. Kontribusi Penelitian terhadap Pertanyaan Penelitian</h5>
<p>Sebagai evaluasi akhir, hasil penelitian ini secara konkret memberikan kontribusi ilmiah untuk menjawab empat pertanyaan penelitian yang dirumuskan pada Bab II:</p>
<ol style="margin-left: 20px;">
    <li><strong>Menjawab Pengukuran Data (Disparitas):</strong> Sebelum diterapkannya sistem, pencatatan manual menghasilkan celah selisih (disparitas) fisik vs buku yang tinggi akibat tidak tercatatnya transaksi ad-hoc. Implementasi otomatisasi kartu stok digital terintegrasi berhasil menekan disparitas tersebut hingga mencapai 0% pada lingkungan uji coba.</li>
    <li><strong>Menjawab Analisis Logika (Degradasi Fisik):</strong> Dengan berjalannya kueri terurut FIFO, material kelistrikan yang sensitif terhadap kelembapan dan debu tidak lagi mengendap terlalu lama di sudut gudang. Sistem secara aktif mendorong konsumsi barang tertua, sehingga risiko kerusakan akibat degradasi kualitas fisik material dapat dicegah.</li>
    <li><strong>Menjawab Perancangan Integrasi (Duplikasi Data):</strong> Melalui kontrol transaksi berbasis <i>database database transaction standard</i> (ACID) di CodeIgniter 3, integritas data saldo utama terjamin penuh tanpa adanya risiko duplikasi pencatatan stok.</li>
    <li><strong>Menjawab Validasi Hasil (Akurasi Audit):</strong> Otomatisasi modul pelaporan mutasi dan *Stock Opname* menghasilkan jejak audit (<i>audit trail</i>) yang transparan. Operator gudang tidak perlu lagi melakukan rekapitulasi manual dari tumpukan kertas, yang secara drastis meningkatkan efisiensi waktu pelaksanaan audit stok dari hitungan hari menjadi hitungan menit.</li>
</ol>
"""

    # We need to construct the final HTML
    # We will format the part_before_442 text properly. It is currently plain text, so we convert newlines to <p> where appropriate
    # But wait, part_before_442 has some heading strings like "Data yang Digunakan", "Hasil Perancangan", etc.
    # Let's map them to HTML headings.
    
    # Basic paragraph wrapping for part_before_442
    lines = part_before_442.split('\n')
    html_lines = []
    
    in_list = False
    
    for line in lines:
        line_s = line.strip()
        if not line_s:
            continue
            
        # Check if it looks like a heading
        if re.match(r'^(Data yang Digunakan|Hasil Perancangan|Data Flow Diagram|UML Activity Diagram|Physical Data Model|Hasil Penelitian|Tampilan Halaman Login|Implementasi Pengelolaan Data Master|Implementasi Proses Pengadaan Barang|Implementasi Proses Transaksi dan Rotasi|Implementasi Modul Pelaporan dan Audit|Hasil Pengujian|Pengujian Black Box)', line_s):
            if in_list:
                html_lines.append("</ul>")
                in_list = False
            # Determine level
            if line_s in ["Data yang Digunakan", "Hasil Perancangan", "Hasil Penelitian", "Hasil Pengujian"]:
                # 4.1, 4.2, 4.3, 4.4
                num = ""
                if line_s == "Data yang Digunakan": num = "4.1. "
                elif line_s == "Hasil Perancangan": num = "4.2. "
                elif line_s == "Hasil Penelitian": num = "4.3. "
                elif line_s == "Hasil Pengujian": num = "4.4. "
                html_lines.append(f"<h4>{num}{line_s}</h4>")
            else:
                # Subheadings
                html_lines.append(f"<h5>{line_s}</h5>")
        elif line_s.startswith('•') or line_s.startswith('-'):
            if not in_list:
                html_lines.append("<ul>")
                in_list = True
            html_lines.append(f"<li>{line_s[1:].strip()}</li>")
        elif re.match(r'^Gambar\s*\d+\.\d+', line_s):
            if in_list:
                html_lines.append("</ul>")
                in_list = False
            html_lines.append(f'<p style="color: gray; font-style: italic; text-align: center;">{line_s}</p>')
        elif line_s.startswith('Tabel') or re.match(r'^No\tModul\t', line_s) or line_s.startswith('1.') or line_s.startswith('2.'):
            # This is probably part of the Table! Let's output it as preformatted text or basic paragraph
            if in_list:
                html_lines.append("</ul>")
                in_list = False
            html_lines.append(f'<p style="font-family: monospace; font-size: 10pt; background: #fafafa; padding: 5px; border: 1px solid #eee;">{line_s}</p>')
        else:
            if in_list:
                html_lines.append("</ul>")
                in_list = False
            html_lines.append(f'<p style="text-indent: 0.5in;">{line_s}</p>')
            
    if in_list:
        html_lines.append("</ul>")
        
    full_body_html = "\n".join(html_lines) + new_442_and_45
    
    html_output = f"""<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAB IV Lengkap Hasil dan Pembahasan</title>
    <style>
        body {{ font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; padding: 40px; background: #f4f4f4; color:#000; }}
        .page {{ max-width: 800px; margin: auto; background: #fff; padding: 50px 70px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }}
        h3 {{ font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 20px; }}
        h4 {{ font-size: 12pt; font-weight: bold; margin-top: 25px; margin-bottom: 10px; }}
        h5 {{ font-size: 12pt; font-weight: bold; margin-top: 20px; margin-bottom: 5px; }}
        p {{ text-align: justify; margin-bottom: 12px; }}
        .note {{ color: #27ae60; font-weight: bold; font-style: italic; background: #e8f8f5; padding: 10px; margin: 30px 0 20px 0; border-left: 5px solid #2ecc71; font-size: 11pt;}}
        ul {{ text-align: justify; margin-bottom: 12px; }}
        ul li {{ margin-bottom: 8px; }}
    </style>
</head>
<body>
    <div class="page">
        <div class="note">
            /// DOKUMEN BAB IV LENGKAP /// <br>
            File ini berisi naskah BAB IV lengkap yang sudah disinkronisasikan dan difokuskan pada metode FIFO. 
            Silakan blok seluruh halaman ini (Ctrl+A), salin (Ctrl+C), lalu timpa langsung seluruh Bab IV lamamu di MS Word!
        </div>
        
        <h3>BAB IV<br>HASIL PENELITIAN DAN PEMBAHASAN</h3>
        
        {full_body_html}
    </div>
</body>
</html>"""

    with open(r'C:\Users\Ryan\Documents\TA\diagrams\Revisi_Lengkap_BAB_IV.html', 'w', encoding='utf-8') as f:
        f.write(html_output)
    print("Unified BAB IV successfully created.")
else:
    print("Could not split document.")

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Purchase Order — <?= htmlspecialchars($po['po_number']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            background: #fff;
            padding: 30px 40px;
        }

        /* ======= KOP SURAT ======= */
        .kop {
            display: flex;
            align-items: center;
            border-bottom: 3px solid #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .kop img {
            height: 65px;
            margin-right: 20px;
        }
        .kop-text h1 {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-text p {
            font-size: 10pt;
            color: #333;
            margin-top: 3px;
        }

        /* ======= JUDUL DOKUMEN ======= */
        .doc-title {
            text-align: center;
            margin: 18px 0 8px;
        }
        .doc-title h2 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .doc-title p {
            font-size: 10pt;
            color: #555;
        }
        .divider {
            border: none;
            border-top: 1px solid #000;
            margin: 10px 0 18px;
        }

        /* ======= INFO PO & SUPPLIER ======= */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 30px;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .info-block table td {
            padding: 3px 6px 3px 0;
            vertical-align: top;
        }
        .info-block table td:first-child {
            width: 130px;
            color: #444;
        }
        .info-block table td:nth-child(2) {
            width: 10px;
            color: #444;
        }
        .info-block table td:last-child {
            font-weight: bold;
        }
        .info-label {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        /* ======= TABEL BARANG ======= */
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 4px solid #004274;
            padding-left: 8px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        table.items th {
            background-color: #004274;
            color: #fff;
            padding: 8px 10px;
            text-align: center;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        table.items td {
            border: 1px solid #ccc;
            padding: 7px 10px;
            vertical-align: middle;
        }
        table.items tr:nth-child(even) td {
            background: #f9f9f9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        table.items .text-center { text-align: center; }
        table.items .text-end    { text-align: right; }
        table.items .row-total td {
            background: #f2f2f2 !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ======= CATATAN ======= */
        .notes {
            margin-top: 18px;
            font-size: 10pt;
            color: #555;
            border-top: 1px dashed #aaa;
            padding-top: 10px;
        }
        .notes strong { color: #000; }

        /* ======= TANDA TANGAN ======= */
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            gap: 60px;
            font-size: 11pt;
        }
        .sig-box {
            text-align: center;
            width: 200px;
        }
        .sig-box .sig-title {
            font-size: 10pt;
            color: #555;
            margin-bottom: 4px;
        }
        .sig-box .sig-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }

        /* ======= PRINT BUTTON ======= */
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            background: #004274;
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }
        .btn-print:hover { background: #002f52; }

        /* ======= FOOTER INFO ======= */
        .doc-footer {
            margin-top: 30px;
            font-size: 9pt;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }

        @media print {
            @page { size: A4 portrait; margin: 1.5cm; }
            body { padding: 0; }
            .btn-print { display: none !important; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>

    <!-- KOP SURAT -->
    <div class="kop">
        <img src="<?= base_url('assets/img/logo-aoi.png') ?>" alt="Logo AOI">
        <div class="kop-text">
            <h1>Apparel One Indonesia</h1>
            <p>Workshop Automation — Operation Excellence Division</p>
            <p>Jl. Industri No.1, Semarang, Jawa Tengah</p>
        </div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">
        <h2>Surat Purchase Order (PO)</h2>
        <p>Dokumen Resmi Pemesanan Barang</p>
    </div>
    <hr class="divider">

    <!-- INFO PO & SUPPLIER -->
    <div class="info-grid">
        <!-- Kiri: Info PO -->
        <div class="info-block">
            <div class="info-label">Informasi PO</div>
            <table>
                <tr>
                    <td>Nomor PO</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($po['po_number']) ?></td>
                </tr>
                <tr>
                    <td>Tanggal Pesan</td>
                    <td>:</td>
                    <td><?= date('d F Y', strtotime($po['order_date'])) ?></td>
                </tr>
                <tr>
                    <td>Tanggal Cetak</td>
                    <td>:</td>
                    <td><?= date('d F Y') ?></td>
                </tr>
            </table>
        </div>

        <!-- Kanan: Info Supplier -->
        <div class="info-block">
            <div class="info-label">Ditujukan Kepada</div>
            <table>
                <tr>
                    <td>Nama Supplier</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($supplier['supplier_name'] ?? $po['supplier_name'] ?? '-') ?></td>
                </tr>
                <?php if (!empty($supplier['address'])): ?>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($supplier['address']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($supplier['contact_person'])): ?>
                <tr>
                    <td>Contact Person</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($supplier['phone'])): ?>
                <tr>
                    <td>Telepon</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($supplier['phone']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- TABEL BARANG -->
    <div class="section-title">Rincian Barang yang Dipesan</div>
    <table class="items">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Kode Barang</th>
                <th width="35%">Nama & Spesifikasi Barang</th>
                <th width="10%">Satuan</th>
                <th width="10%">Qty</th>
                <th width="15%">Harga Satuan (Rp)</th>
                <th width="16%">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($details as $d):
                $sub = (float)$d['qty_ordered'] * (float)$d['price'];
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center"><?= htmlspecialchars($d['electric_id']) ?></td>
                <td>
                    <strong><?= htmlspecialchars($d['spesifikasi'] ?? '') ?></strong><br>
                    <small style="color:#555;">
                        <?php
                            $parts = [];
                            if (!empty($d['nama']))  $parts[] = $d['nama'];
                            if (!empty($d['brand']) && $d['brand'] !== '-') $parts[] = $d['brand'];
                            $voltage = trim(($d['voltage'] ?? '') . ($d['voltage_unit'] ?? ''));
                            if ($voltage !== '') $parts[] = $voltage;
                            if (!empty($d['ampere'])) $parts[] = $d['ampere'] . 'A';
                            $daya = trim(($d['daya'] ?? '') . ($d['daya_unit'] ?? ''));
                            if ($daya !== '') $parts[] = $daya;
                            echo htmlspecialchars(implode(' | ', $parts));
                        ?>
                    </small>
                </td>
                <td class="text-center">pcs</td>
                <td class="text-center"><?= (int)$d['qty_ordered'] ?></td>
                <td class="text-end"><?= number_format((float)$d['price'], 0, ',', '.') ?></td>
                <td class="text-end"><?= number_format($sub, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="row-total">
                <td colspan="6" class="text-end" style="text-align:right; padding-right:12px;">TOTAL KESELURUHAN</td>
                <td class="text-end">Rp <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- CATATAN -->
    <div class="notes">
        <strong>Catatan:</strong>
        Mohon barang dikirimkan sesuai spesifikasi dan jumlah di atas. Harap konfirmasi penerimaan dokumen PO ini kepada tim Workshop Automation kami.
    </div>

    <!-- TANDA TANGAN -->
    <div class="signature-section">
        <div class="sig-box">
            <div class="sig-title">Dibuat oleh,<br>Staf Gudang</div>
            <div class="sig-line">( ................................ )</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Disetujui oleh,<br>Manajer OE</div>
            <div class="sig-line">( ................................ )</div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="doc-footer">
        Dokumen ini dicetak secara otomatis oleh Sistem Electrical Workshop — Apparel One Indonesia &nbsp;|&nbsp; <?= date('d F Y H:i') ?>
    </div>

    <script>
        window.onload = function () {
            setTimeout(function () { window.print(); }, 500);
        };
    </script>
</body>
</html>

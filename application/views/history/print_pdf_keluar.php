<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            color: #000;
            background: #fff;
            padding: 20px;
            font-size: 12px;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat h2 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-surat p {
            margin: 2px 0;
            font-size: 12px;
        }
        .periode-info {
            margin-bottom: 15px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table, th, td {
            border: 1px solid #000 !important;
        }
        th {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            text-align: center;
            padding: 6px 8px;
            font-weight: bold;
        }
        td {
            padding: 5px 7px;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        .signature-section {
            margin-top: 50px;
            width: 100%;
            overflow: hidden;
        }
        .signature-box {
            float: right;
            width: 280px;
            text-align: center;
        }
        .signature-name {
            margin-top: 70px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        @media print {
            @page { margin: 1.5cm; size: A4 portrait; }
            body { padding: 0; }
            .btn-print { display: none !important; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>

    <div class="kop-surat">
        <img src="<?= base_url('assets/img/logo-aoi.png') ?>" alt="Logo AOI" style="height:55px; margin-bottom:8px;"><br>
        <h2>Laporan Barang Keluar</h2>
        <h2>Workshop Automation – Apparel One Indonesia</h2>
        <p>Tanggal Cetak: <?= date('d F Y H:i') ?></p>
    </div>

    <div class="periode-info">
        <?php if (!empty($start_date) || !empty($end_date)): ?>
            <strong>Periode:</strong>
            <?= !empty($start_date) ? date('d F Y', strtotime($start_date)) : '&mdash;' ?>
            s/d
            <?= !empty($end_date) ? date('d F Y', strtotime($end_date)) : '&mdash;' ?>
        <?php else: ?>
            <strong>Periode:</strong> Semua Data
        <?php endif; ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <?php if (!empty($electric_id)): ?>
            <strong>Barang:</strong> <?= htmlspecialchars($electric_id) ?><?= !empty($electric_label) ? ' — ' . htmlspecialchars($electric_label) : '' ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;
        <?php endif; ?>
        <strong>Total Record:</strong> <?= count($history) ?> transaksi
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="13%">Tanggal & Waktu</th>
                <th width="9%">ID Barang</th>
                <th width="18%">Nama Barang</th>
                <th width="14%">Kategori & Brand</th>
                <th width="11%">No. WO</th>
                <th width="18%">Keterangan / Tujuan</th>
                <th width="6%">Qty</th>
                <th width="7%">Teknisi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($history)): ?>
                <?php $i = 1; $total_qty = 0; foreach ($history as $r): ?>
                    <?php
                        $qty = $r['display_amount'] ?? $r['qty'] ?? 0;
                        $total_qty += (int)$qty;
                    ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>
                        <td class="text-center"><?= date('d M Y H:i', strtotime($r['created_at'] ?? $r['date'] ?? 'now')) ?></td>
                        <td class="text-center"><?= htmlspecialchars($r['electric_id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['nama_barang'] ?? '') ?></td>
                        <td><?= htmlspecialchars(($r['spec_type'] ?? '') . ' – ' . ($r['brand'] ?? '')) ?></td>
                        <td class="text-center"><?= htmlspecialchars($r['po_number'] ?? $r['wo_number'] ?? '-') ?></td>
                        <td><small><?= htmlspecialchars($r['keterangan'] ?? '-') ?></small></td>
                        <td class="text-center text-bold">-<?= $qty ?></td>
                        <td class="text-center"><?= htmlspecialchars($r['user_name'] ?? $r['user_nik'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background-color:#f2f2f2; font-weight:bold; -webkit-print-color-adjust:exact; print-color-adjust:exact;">
                    <td colspan="7" class="text-end" style="text-align:right;">TOTAL QTY KELUAR:</td>
                    <td class="text-center"><?= $total_qty ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding:20px;">Tidak ada data barang keluar untuk periode ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p>Semarang, <?= date('d F Y') ?><br>Mengetahui,</p>
            <div class="signature-name">( ................................................ )</div>
        </div>
    </div>

    <script>
        window.onload = function () {
            setTimeout(function () { window.print(); }, 500);
        };
    </script>
</body>
</html>

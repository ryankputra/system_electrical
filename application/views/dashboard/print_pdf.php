<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-surat p {
            margin: 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        table, th, td {
            border: 1px solid #000 !important;
        }
        th {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            text-align: center;
            padding: 8px;
        }
        td {
            padding: 6px 8px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        
        .signature-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 300px;
            text-align: center;
        }
        .signature-name {
            margin-top: 80px;
            font-weight: bold;
            text-decoration: underline;
        }

        @media print {
            @page { margin: 1.5cm; size: A4 portrait; }
            body { padding: 0; }
            .btn-print { display: none !important; }
        }
    </style>
</head>
<body>

    <button class="btn btn-primary btn-print" style="position:fixed; top:20px; right:20px; z-index:100;" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak / Simpan sebagai PDF
    </button>

    <div class="kop-surat">
        <img src="<?= base_url('assets/img/logo-aoi.png') ?>" alt="Logo AOI" style="height: 60px; margin-bottom: 10px;">
        <h2>Laporan Mutasi Komponen Electrical</h2>
        <h2>Workshop Automation - Apparel One Indonesia</h2>
        <p>Tanggal Cetak: <?= date('d F Y H:i') ?></p>
        <p>Periode Laporan: <strong>Bulan <?= date('F Y') ?></strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Kode Barang</th>
                <th width="30%">Tipe Barang & Spesifikasi</th>
                <th width="10%">Qty</th>
                <th width="10%">Tipe Transaksi</th>
                <th width="15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($rows)): ?>
                <?php $no=1; foreach($rows as $r): ?>
                    <?php
                        $t = $r[$dateField] ?? ($r['created_at'] ?? '');
                        $code = $r['electric_id'] ?? ($r['electric_code'] ?? '');
                        
                        // Fix for item name: Prefer Type over Category (Nama)
                        $tipe = $r['type'] ?? $r['tipe'] ?? '';
                        $brand = $r['brand'] ?? '';
                        $name = $tipe ? ($tipe . ($brand ? ' - ' . $brand : '')) : ($r['nama_barang'] ?? $r['nama'] ?? '');
                        
                        if ($qtyField) {
                            $qtyVal = $r[$qtyField] ?? 0;
                        } else {
                            $qtyVal = $r['amount'] ?? ($r['qty'] ?? ($r['jumlah'] ?? ($r['qty_sisa'] ?? 0)));
                        }
                        // Avoid name collision for 'type' (transaction vs electric.type)
                        $status = isset($r['type']) && in_array($r['type'], ['Masuk', 'Keluar', 'Audit']) ? $r['type'] : ($r['transaction_type'] ?? '');
                        $ket = $r['keterangan'] ?? '';
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= $t ? date('d-m-Y H:i', strtotime($t)) : '-' ?></td>
                        <td class="text-center"><?= htmlspecialchars($code) ?></td>
                        <td><?= htmlspecialchars($name) ?></td>
                        <td class="text-center font-monospace fw-bold"><?= $qtyVal ?></td>
                        <td class="text-center"><?= htmlspecialchars($status) ?></td>
                        <td><small><?= htmlspecialchars($ket) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada transaksi pada periode bulan ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p>Semarang, <?= date('d F Y') ?><br>Mengetahui,</p>
            <div class="signature-name">(........................................)</div>
        </div>
        <div style="clear:both;"></div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

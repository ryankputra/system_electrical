<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Stiker Batch</title>
    <style>
        /* Setup Halaman Khusus Printer Thermal (Misal: 80mm x 50mm) */
        @page {
            size: 80mm 50mm;
            margin: 0;
        }
        body {
            margin: 0; padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f0f0f0; 
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }
        .sticker-container {
            width: 76mm; /* Margin aman dari pinggir kertas */
            height: 46mm;
            background: #fff;
            border: 2px solid #000;
            border-radius: 4px;
            box-sizing: border-box;
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        
        /* Area Kode Warna Bulanan */
        .color-band {
            height: 8mm;
            color: white;
            font-weight: bold; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important; /* Wajib agar warna ter-print */
        }

        /* Area Informasi Utama */
        .info-area {
            padding: 4px 6px;
            flex-grow: 1;
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .item-name {
            font-size: 14px; font-weight: 900; text-align: center;
            line-height: 1.1; margin-bottom: 2px; text-transform: uppercase;
        }
        .item-spec {
            font-size: 10px; text-align: center; color: #333;
            margin-bottom: 4px; padding-bottom: 2px;
            border-bottom: 1px dashed #ccc;
        }

        /* Area Batch & Tanggal */
        .batch-area {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 2px;
        }
        .batch-box {
            border: 2px solid #000; padding: 2px 6px;
            font-size: 16px; font-weight: 900; border-radius: 3px;
        }
        .date-box {
            font-size: 12px; font-weight: bold; text-align: right;
        }
        .date-box span {
            display: block; font-size: 9px; font-weight: normal; color: #555;
        }

        @media print {
            body { background-color: #fff; min-height: auto; display: block; }
            .sticker-container { border: 1px solid #000; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="sticker-container">
        <div class="color-band" style="background-color: <?= $month_color ?>;">
            BULAN MASUK: <?= $month_name ?>
        </div>
        
        <div class="info-area">
            <div>
                <div class="item-name"><?= htmlspecialchars(($electric['name'] ?? '') . ' - ' . ($electric['type'] ?? '')) ?></div>
                <div class="item-spec">Brand: <?= htmlspecialchars($electric['brand'] ?? '-') ?> | Spec: <?= htmlspecialchars(($electric['voltage'] ?? '') . ($electric['voltage_unit'] ?? '')) ?>, <?= htmlspecialchars(($electric['ampere'] ?? '') ? $electric['ampere'] . 'A' : '') ?></div>
            </div>
            
            <div class="batch-area">
                <div class="batch-box">BATCH <?= htmlspecialchars($history['id'] ?? '') ?></div>
                <div class="date-box">
                    <span>Tgl Masuk:</span>
                    <?= date('d/m/Y', strtotime($history['created_at'] ?? 'now')) ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

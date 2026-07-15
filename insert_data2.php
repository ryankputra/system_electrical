<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';
$db_config = $db['default'];
$dsn = 'mysql:host=' . $db_config['hostname'] . ';dbname=' . $db_config['database'] . ';charset=' . $db_config['char_set'];
$pdo = new PDO($dsn, $db_config['username'], $db_config['password']);

$stmtTypes = $pdo->query('SELECT id, type FROM as_electric_types');
$typesMap = [];
while ($row = $stmtTypes->fetch(PDO::FETCH_ASSOC)) {
    $typesMap[$row['type']] = $row['id'];
}

$data = [
    // POWER SUPLAY
    ['POWER SUPLAY', 'OMRON', 'S8FS-C05024', '24', 'VDC', '2.2', '50'],
    ['POWER SUPLAY', 'OMRON', 'S8FS-C10024J', '24', 'VDC', '4.5', '100'],
    ['POWER SUPLAY', 'OMRON', 'S8FS-C15024', '24', 'VDC', '6.5', '150'],
    ['POWER SUPLAY', 'OMRON', 'S8FS-C15024J', '24', 'VDC', '6.5', '150'],
    ['POWER SUPLAY', 'OMRON', 'S8VS-12024', '24', 'VDC', '5', '120'],
    ['POWER SUPLAY', 'OMRON', 'S8VK-C06024', '24', 'VDC', '2.5', '60'],
    ['POWER SUPLAY', 'OMRON', 'S8VK-C12024', '24', 'VDC', '5', '120'],
    ['POWER SUPLAY', 'OMRON', 'S8VK-G48024', '24', 'VDC', '20', '480'],
    ['POWER SUPLAY', 'MEAN WELL', 'LRS-50-24', '24', 'VDC', '2.2', '52'],
    ['POWER SUPLAY', 'MEAN WELL', 'LRS-100-24', '24', 'VDC', '4.5', '108'],
    ['POWER SUPLAY', 'MEAN WELL', 'LRS-150-24', '24', 'VDC', '6.5', '156'],
    ['POWER SUPLAY', 'MEAN WELL', 'HDR-15-24', '24', 'VDC', '0.6', '15'],
    
    // PLC
    ['PLC', 'OMRON', 'CP1E-E20SDR-A', '240', 'VAC', null, null],
    ['PLC', 'OMRON', 'CP1E-E30SDR-A', '240', 'VAC', null, null],
    ['PLC', 'OMRON', 'CP1E-E40SDR-A', '240', 'VAC', null, null],
    ['PLC', 'OMRON', 'CP1L-L20DR-A', '240', 'VAC', null, null],
    
    // MCB 1 PHASE
    ['MCB 1 PHASE', 'SCHNEIDER', 'EZ9F34106', '230', 'VAC', '6', null],
    
    // THERMOCOUPLPE R (RMS)
    ['THERMOCOUPLPE R', 'RKC', 'REX-C100', '240', 'VAC', '3', null],
    
    // MCB SHILINDER 2
    ['MCB SHILINDER 2', 'CHNT', 'NBH8-40', '230', 'VAC', '25', null],
    
    // DRIVER MOTOR
    ['DRIVER MOTOR', 'LEADSHINE', 'DM542', '50', 'VDC', '4.2', null],
    ['DRIVER MOTOR', 'LEADSHINE', 'M542C', '50', 'VDC', '4.2', null],
    ['DRIVER MOTOR', 'LEADSHINE', 'DM556', '50', 'VDC', '5.6', null],
    
    // PHOTO SENSOR
    ['PHOTO SENSOR', 'OMRON', 'E3Z-D62', '24', 'VDC', null, null],
    ['PHOTO SENSOR', 'SICK', 'SICK-PHOTO-SENSOR', null, null, null, null],
    
    // PHOTO ELECTRIK
    ['PHOTO ELECTRIK', 'OMRON', 'E3JK-DS30M1', '240', 'VDC', null, null],
    
    // PROXIMITY SENSOR
    ['PROXIMITY SENSOR', 'OMRON', 'E2E-X5ME1', '24', 'VDC', null, null],
    ['PROXIMITY SENSOR', 'OMRON', 'PROXIMITY-OMRON', null, null, null, null],
    
    // PSU ANDON
    ['PSU ANDON', '', 'QZ-120024B', '24', 'VDC', '10', null],
    
    // PSU KECIL
    ['PSU KECIL', 'JING', 'JING-PSU', '24', 'VDC', null, null],
    
    // MOTOR STEPER
    ['MOTOR STEPER', '', 'MOTOR-STEPER', null, null, null, null],
    
    // 5 PHASE STEPPING MOTOR
    ['5 PHASE STEPPING MOTOR', 'ORIENTAL', 'A15112A', '1.4', 'V', '1.4', null],
    
    // SSR
    ['SSR', 'OMRON', 'G3NA-220B', '240', 'VAC', '20', null],
    ['SSR', 'FOTEK', 'SSR-40DA', '380', 'VAC', '40', null],
    ['SSR', 'SCHNEIDER', 'SSP1A125BD', '300', 'VAC', '25', null],
    
    // PLC XPAN
    ['PLC XPAN', 'OMRON', 'CP1W-40EDR', null, null, null, null],
    ['PLC XPAN', 'OMRON', 'CPM1A-40EDR', '24', 'VDC', null, null],
    ['PLC XPAN', 'OMRON', 'CP1W-20EDR1', '24', 'VDC', null, null],
    
    // TRAVO
    ['TRAVO', 'OMRON', 'NNN', '24', 'V', null, null],
    
    // REMOTE CONTROL SWITCH
    ['REMOTE CONTROL SWITCH', '', 'REMOTE-SWITCH', null, null, null, null],
    
    // LOGIC PANEL
    ['LOGIC PANEL', '', 'LOGIC-PANEL', null, null, null, null],
    
    // HMI
    ['HMI', 'OMRON', 'NB7W-TW00B', '24', 'VDC', null, null],
    
    // MAGNETIC SENSOR MERK
    ['MAGNETIC SENSOR MERK', 'SICK', 'MZT8-03VPS-KP0', '24', 'VDC', null, null],
    
    // CONECTOR JUNOTION
    ['CONECTOR JUNOTION', '', 'CONECTOR', null, null, null, null],
    
    // SMART SWITCH
    ['SMART SWITCH', 'FORT', 'FORT-SWITCH', '240', 'VAC', '10', null]
];

$stmt = $pdo->prepare("INSERT IGNORE INTO as_electric (electric_id, type_id, nama, brand, type, voltage, voltage_unit, ampere, daya, daya_unit, location, created_at, updated_at, editor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 'SYSTEM')");

foreach ($data as $idx => $row) {
    $catName = $row[0];
    $brand = $row[1];
    $type = $row[2];
    $volt = $row[3];
    $volt_u = $row[4];
    $amp = $row[5];
    $daya = $row[6];
    
    $catId = $typesMap[$catName] ?? null;
    if (!$catId) {
        echo "Missing category ID for $catName\n";
        continue;
    }
    
    $clean_type = preg_replace('/[^a-z0-9]/', '-', strtolower($type));
    $clean_type = preg_replace('/-+/', '-', $clean_type);
    $clean_type = trim($clean_type, '-');
    
    $clean_nama = preg_replace('/[^a-z0-9]/', '-', strtolower($catName));
    $clean_nama = preg_replace('/-+/', '-', $clean_nama);
    
    $id = 'elc-' . $clean_nama . '-' . $clean_type . '-' . rand(100,999);
    
    $stmt->execute([
        $id,
        $catId,
        $catName,
        $brand,
        $type,
        $volt,
        $volt_u,
        $amp,
        $daya,
        ($daya ? 'W' : null),
        1 // LEMARI KACA
    ]);
    echo "Inserted $type ($catName)\n";
}

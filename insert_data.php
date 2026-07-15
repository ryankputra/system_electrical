<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';
$db_config = $db['default'];
$dsn = 'mysql:host=' . $db_config['hostname'] . ';dbname=' . $db_config['database'] . ';charset=' . $db_config['char_set'];
$pdo = new PDO($dsn, $db_config['username'], $db_config['password']);

$data = [
    ['OMRON', 'IEC 255 (MY2N)', '250', 'VAC', '5', null],
    ['OMRON', 'MY2N-GS (5A)', '24', 'VDC', '5', null],
    ['OMRON', 'LY2N (12V)', '12', 'VDC', '7', null],
    ['OMRON', 'MY2N-GS (10A)', '24', 'VDC', '10', null],
    ['NNC', 'NNC 69KTL-22', '24', 'VDC', '8', null],
    ['OMRON', 'IEC 255 (MY4N)', '250', 'VAC', '5', null],
    ['OMRON', 'LY2N (110V)', '110', 'VAC', '10', null],
    ['OMRON', 'MY2N-GS-R', '240', 'VAC', '10', null],
    ['OMRON', 'MY2N-J', '250', 'VAC', '5', null],
    ['OMRON', 'MKS3P', '250', 'VAC', '10', null],
    ['AP422F', 'HL 2-H-DC24KF', '250', 'VAC', '10', null],
    ['AUTONICS', 'ABS-S04PA-CN', '24', 'VDC', '10.5', null],
    ['OMRON', 'MY4N-J', '240', 'VAC', '5', null]
];

$stmt = $pdo->prepare("INSERT IGNORE INTO as_electric (electric_id, type_id, nama, brand, type, voltage, voltage_unit, ampere, daya, location, created_at, updated_at, editor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 'SYSTEM')");

foreach ($data as $idx => $row) {
    $brand = $row[0];
    $type = $row[1];
    $volt = $row[2];
    $volt_u = $row[3];
    $amp = $row[4];
    $daya = $row[5];
    
    $clean_type = preg_replace('/[^a-z0-9]/', '-', strtolower($type));
    $clean_type = preg_replace('/-+/', '-', $clean_type);
    $clean_type = trim($clean_type, '-');
    $id = 'elc-relay-' . $clean_type . '-' . rand(100,999);
    
    $stmt->execute([
        $id,
        25, // RELAY
        'RELAY',
        $brand,
        $type,
        $volt,
        $volt_u,
        $amp,
        $daya,
        1 // LEMARI KACA
    ]);
    echo "Inserted $type\n";
}

<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';
$db_config = $db['default'];
$dsn = 'mysql:host=' . $db_config['hostname'] . ';dbname=' . $db_config['database'] . ';charset=' . $db_config['char_set'];
$pdo = new PDO($dsn, $db_config['username'], $db_config['password']);

echo "Locations:\n";
$stmt = $pdo->query('SELECT id, location_name FROM as_location');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "Types:\n";
$stmt = $pdo->query('SELECT id, type FROM as_electric_types WHERE type LIKE "%RELAY%"');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

<?php
require 'application/config/database.php';
$db = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

// defined('BASEPATH') or exit('No direct script access allowed'); // Temporarily disabled for debugging
echo "=== Semua Data as_history (type, qty, qty_sisa, electric_id) ===\n";
$res = $db->query('SELECT electric_id, type, qty, qty_sisa, keterangan FROM as_history ORDER BY id ASC');
while ($row = $res->fetch_assoc()) {
    echo $row['electric_id'] . ' | ' . $row['type'] . ' | qty=' . $row['qty'] . ' | qty_sisa=' . $row['qty_sisa'] . ' | ' . ($row['keterangan'] ?? '') . "\n";
}

echo "\n=== SUM(qty_sisa) grouped by type ===\n";
$res = $db->query('SELECT type, SUM(qty_sisa) as total FROM as_history GROUP BY type');
while ($row = $res->fetch_assoc()) {
    echo $row['type'] . ': ' . $row['total'] . "\n";
}

echo "\n=== SUM(qty_sisa) WHERE type=Masuk grouped by electric_id ===\n";
$res = $db->query("SELECT electric_id, SUM(qty_sisa) as total FROM as_history WHERE type='Masuk' GROUP BY electric_id");
while ($row = $res->fetch_assoc()) {
    echo $row['electric_id'] . ': ' . $row['total'] . "\n";
}

echo "\n=== Stock di as_electric ===\n";
$res = $db->query('SELECT electric_id, nama, type, stock FROM as_electric ORDER BY nama ASC');
while ($row = $res->fetch_assoc()) {
    echo $row['electric_id'] . ' | ' . $row['nama'] . ' | ' . $row['type'] . ' | stock=' . $row['stock'] . "\n";
}

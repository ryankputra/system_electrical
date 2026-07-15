<?php
define('BASEPATH', '1');
require 'application/config/database.php';
$db = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
$res = $db->query("SELECT id, keterangan FROM as_history WHERE type='Audit'");
while ($row = $res->fetch_assoc()) {
    $ket = $row['keterangan'];
    if (preg_match('/Stock Opname: system=(\d+), counted=(\d+), diff=(-?\d+)\.\s*(.*)/i', $ket, $m)) {
        $newKet = 'Penyesuaian Audit: Stok Sistem ' . $m[1] . ' menjadi Stok Fisik ' . $m[2] . '. Alasan: ' . $m[4];
        $db->query("UPDATE as_history SET keterangan = '" . $db->real_escape_string($newKet) . "' WHERE id = " . $row['id']);
    }
}
$res = $db->query("SELECT id, keterangan FROM as_history WHERE type='Keluar' AND keterangan LIKE 'Audit shortage%'");
while ($row = $res->fetch_assoc()) {
    $ket = $row['keterangan'];
    if (preg_match('/Audit shortage adjustment \((\d+) units\)\.\s*(?:From [^.]+.\s*)?(?:Reason:\s*)?(.*)/i', $ket, $m)) {
        $newKet = 'Pengurangan dari Hasil Audit (Kekurangan: ' . $m[1] . ' unit). Alasan: ' . $m[2];
        $db->query("UPDATE as_history SET keterangan = '" . $db->real_escape_string($newKet) . "' WHERE id = " . $row['id']);
    }
}
$res = $db->query("SELECT id, keterangan FROM as_history WHERE type='Masuk' AND keterangan LIKE 'Audit surplus%'");
while ($row = $res->fetch_assoc()) {
    $ket = $row['keterangan'];
    if (preg_match('/Audit surplus adjustment \((\d+) units\)/i', $ket, $m)) {
        $newKet = 'Penambahan dari Hasil Audit (Surplus: ' . $m[1] . ' unit).';
        $db->query("UPDATE as_history SET keterangan = '" . $db->real_escape_string($newKet) . "' WHERE id = " . $row['id']);
    }
}
echo "Done.";

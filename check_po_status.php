<?php
$db = new mysqli('localhost', 'root', '', 'electrical_system');
$res = $db->query("SHOW COLUMNS FROM as_purchase_orders LIKE 'status'");
$row = $res->fetch_assoc();
echo $row['Type'];

<?php
$db = new mysqli('localhost', 'root', '', 'electrical_system');
$res = $db->query("SHOW COLUMNS FROM as_history");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

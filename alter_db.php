<?php
$db = new mysqli('localhost', 'root', '', 'electrical_system');
$db->query("ALTER TABLE as_wo_details ADD COLUMN user_nik VARCHAR(20) NULL AFTER qty");
echo "Done.";

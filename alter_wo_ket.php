<?php
$db = new mysqli('localhost', 'root', '', 'electrical_system');
$db->query("ALTER TABLE as_wo_details ADD COLUMN keterangan TEXT NULL AFTER status");
echo "Done.";

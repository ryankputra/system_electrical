<?php
$db = new mysqli('localhost', 'root', '', 'electrical_system');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

$sql = "ALTER TABLE as_purchase_orders MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending'";
if ($db->query($sql) === TRUE) {
    echo "ENUM updated successfully.";
} else {
    echo "Error updating ENUM: " . $db->error;
}

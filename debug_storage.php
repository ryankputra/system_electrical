<?php
/**
 * Test API endpoints for storage debugging
 */

defined('BASEPATH') or exit('No direct script access allowed');

// Sample test for get_stock endpoint
echo "<h3>Testing Storage API Endpoints</h3>";

// Test data
$test_category = 'DRIVER MOTOR';
$test_type_id = 'elc-driver-motor---40-0';

echo "<h4>1. Testing get_stock endpoint</h4>";
echo "<p>Testing with category: {$test_category}, type_id: {$test_type_id}</p>";

$get_stock_url = site_url('storage/get_stock') . '?category=' . urlencode($test_category) . '&type_id=' . urlencode($test_type_id);
echo "<p>URL: <a href='{$get_stock_url}' target='_blank'>{$get_stock_url}</a></p>";

echo "<h4>2. Testing get_item_details endpoint</h4>";
$test_location_id = 'LEMARI KACA';
$get_details_url = site_url('storage/get_item_details') . '?location_id=' . urlencode($test_location_id) . '&category=' . urlencode($test_category) . '&type_id=' . urlencode($test_type_id);
echo "<p>URL: <a href='{$get_details_url}' target='_blank'>{$get_details_url}</a></p>";

echo "<h4>3. Manual Database Check</h4>";
// Connect to database and check manually
$this->load->database();
$this->load->model('Storage_model');

$stock_locations = $this->Storage_model->get_available_stock($test_category, $test_type_id);
$total_stock = $this->Storage_model->get_total_stock($test_category, $test_type_id);

echo "<p>Direct model call results:</p>";
echo "<pre>";
echo "Total Stock: " . $total_stock . "\n";
echo "Stock Locations: " . print_r($stock_locations, true);
echo "</pre>";
?>

<?php
define('BASEPATH', true);
require_once 'application/config/database.php';
$db_config = $db['default'];

try {
    $dsn = 'mysql:host=' . $db_config['hostname'] . ';dbname=' . $db_config['database'] . ';charset=' . $db_config['char_set'];
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $html_out = [];
    $t_num = 1; 
    
    foreach ($tables as $t_name) {
        $desc_stmt = $pdo->query("DESCRIBE `$t_name`");
        $cols = $desc_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $ai_col = '-';
        foreach ($cols as $col) {
            if (strpos($col['Extra'], 'auto_increment') !== false) {
                $ai_col = $col['Field'];
                break;
            }
        }
        
        $html_out[] = "<h5>4.3.1.$t_num. Struktur Tabel $t_name</h5>";
        $html_out[] = "<div style=\"margin-left: 0.5in; margin-bottom: 15px;\">";
        $html_out[] = "<p style=\"margin: 0;\">Nama tabel : <code>$t_name</code></p>";
        $html_out[] = "<p style=\"margin: 0;\">Auto Increment : $ai_col</p>";
        $html_out[] = "</div>";
        $html_out[] = "<p style=\"color: gray; font-style: italic; text-align: center; margin-bottom: 25px;\">Gambar 4." . (11 + $t_num) . " Tampilan Struktur Tabel $t_name di phpMyAdmin</p>";
        $t_num++;
    }
    
    file_put_contents('db_tables_html.txt', implode("\n", $html_out));
    echo "Success";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

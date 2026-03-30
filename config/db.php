<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change for production
define('DB_PASS', '');      // Change for production
define('DB_NAME', 'nexora');

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>


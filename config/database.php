<?php
function getDatabaseConnection(): mysqli {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'e-commerce';
    
    // Enable error reporting for MySQLi
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn = new mysqli($host, $user, $pass, $db);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Exception $e) {
        die("Database Connection failed: " . $e->getMessage());
    }
}

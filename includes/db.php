<?php
// Database connection for MySQL using the database named 'support'.
// Update host, user, and password as needed for your XAMPP setup.

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default password is empty for root
$DB_NAME = 'support';

// Enable mysqli exceptions for better error handling.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = null;

try {
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $mysqli->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}
<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/db.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Only POST is allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        exit;
    }

    $email = trim($input['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid email address']);
        exit;
    }

    // DB column is customer_mobile — we store email there since that's what we use as identifier
    $stmt = $mysqli->prepare(
        "SELECT id FROM customer_register WHERE customer_mobile = ?"
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    echo json_encode([
        'success' => true,
        'exists'  => $result->num_rows > 0
    ]);

    $stmt->close();

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server Error: ' . $e->getMessage()
    ]);
}
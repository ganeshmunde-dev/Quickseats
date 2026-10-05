<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/db.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Only POST is allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }

    $email = trim($input['email'] ?? '');
    $mobile = trim($input['mobile'] ?? '');
    $name = trim($input['name'] ?? '');
    $password = $input['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Enter a valid email address']);
        exit;
    }

    if (empty($name) || empty($mobile) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }

    // Check if email already exists
    $stmt = $mysqli->prepare("SELECT id FROM customer_register WHERE customer_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email is already registered']);
        exit;
    }
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare(
        "INSERT INTO customer_register (full_name, customer_mobile, customer_email, password) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param('ssss', $name, $mobile, $email, $hashed_password);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful'
    ]);

    $stmt->close();

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server Error: ' . $e->getMessage()
    ]);
}
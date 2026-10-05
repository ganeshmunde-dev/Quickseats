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
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email and password are required']);
        exit;
    }

    $stmt = $mysqli->prepare("SELECT id, full_name, password FROM customer_register WHERE customer_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'full_name' => $row['full_name']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    }

    $stmt->close();

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server Error: ' . $e->getMessage()
    ]);
}

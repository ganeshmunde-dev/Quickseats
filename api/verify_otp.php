<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/db.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        if ($action === 'status') {
            echo json_encode([
                'success'  => true,
                'loggedIn' => isset($_SESSION['customer_email']),
                'email'    => $_SESSION['customer_email'] ?? ''
            ]);
            exit;
        }

        if ($action === 'logout') {
            unset($_SESSION['customer_email']);
            echo json_encode(['success' => true]);
            exit;
        }
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

    $email      = trim($input['email'] ?? '');
    $enteredOtp = trim($input['otp'] ?? '');
    $action     = trim($input['action'] ?? 'login'); // 'login' or 'signup'

    if (empty($email) || empty($enteredOtp)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email and OTP are required']);
        exit;
    }

    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email']) || !isset($_SESSION['otp_expiry'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No active OTP session found. Please request a new OTP.']);
        exit;
    }

    if (time() > $_SESSION['otp_expiry']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'OTP has expired. Please request a new one.']);
        exit;
    }

    if ($_SESSION['otp_email'] !== $email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid session email context.']);
        exit;
    }

    if ($_SESSION['otp'] !== $enteredOtp) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid OTP code. Please enter the correct code.']);
        exit;
    }

    // OTP is valid — log user in
    $_SESSION['customer_email'] = $email;

    // Clear OTP session variables for security
    unset($_SESSION['otp']);
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_expiry']);

    // DB SCHEMA: customer_register has column `customer_mobile` (we store email there)
    if ($action === 'signup') {
        // Insert new user — store email in customer_mobile column
        $stmt = $mysqli->prepare(
            "INSERT INTO customer_register (customer_mobile) VALUES (?)
             ON DUPLICATE KEY UPDATE customer_mobile = customer_mobile"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Account created and logged in successfully'
        ]);
    } else {
        // Login flow — check if email is already registered in customer_mobile column
        $stmt = $mysqli->prepare(
            "SELECT id FROM customer_register WHERE customer_mobile = ?"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Auto-register new users on first login
            $stmt->close();
            $stmt = $mysqli->prepare(
                "INSERT INTO customer_register (customer_mobile) VALUES (?)"
            );
            $stmt->bind_param('s', $email);
            $stmt->execute();
        }
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Login successful'
        ]);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server Error: ' . $e->getMessage()
    ]);
}

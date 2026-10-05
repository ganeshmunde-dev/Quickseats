<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/db.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }



    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($action === 'status') {
            echo json_encode([
                'success' => true,
                'loggedIn' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true,
                'username' => $_SESSION['admin_username'] ?? ''
            ]);
            exit;
        }
        
        if ($action === 'logout') {
            unset($_SESSION['admin_logged_in']);
            unset($_SESSION['admin_username']);
            session_destroy();
            echo json_encode(['success' => true]);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'register') {
            $username = trim($input['username'] ?? '');
            $mobile = trim($input['mobile'] ?? '');
            $password = $input['password'] ?? '';
            
            if (strlen($username) < 3) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Username must be at least 3 characters.']);
                exit;
            }
            
            if (!preg_match('/^\d{10}$/', $mobile)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Enter a valid 10-digit mobile number.']);
                exit;
            }
            
            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
                exit;
            }

            // Check if username already exists
            $stmt = $mysqli->prepare("SELECT id FROM admin_accounts WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Admin username already registered.']);
                $stmt->close();
                exit;
            }
            $stmt->close();

            // Hash password securely
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $mysqli->prepare("INSERT INTO admin_accounts (username, mobile, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $username, $mobile, $hashedPassword);
            $stmt->execute();
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
            $stmt->close();
            exit;
        }

        if ($action === 'login') {
            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';

            $stmt = $mysqli->prepare("SELECT password FROM admin_accounts WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
                $stmt->close();
                exit;
            }
            
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
            }
            $stmt->close();
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>

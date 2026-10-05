<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/db.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Only GET requests are allowed']);
        exit;
    }

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Booking ID is required']);
        exit;
    }

    $id = (int)$_GET['id'];

    $stmt = $mysqli->prepare("
        SELECT id,
               customer_mobile AS customer_email,
               passenger_name,
               route_id,
               route_from,
               route_to,
               travel_date,
               travel_time,
               bus_type,
               seats_booked,
               price_per_seat,
               total_price,
               booked_at
        FROM bookmy_bus
        WHERE id = ?
    ");

    if (!$stmt) {
        throw new \Exception("Database error: " . $mysqli->error);
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
        exit;
    }

    $booking = $result->fetch_assoc();

    // Auth: OTP login stores email in localStorage, not PHP session.
    // Frontend sends ?email= param. Validate it's a real email format.
    $request_email = trim($_GET['email'] ?? '');
    $is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

    if (!$is_admin && !filter_var($request_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in first.']);
        exit;
    }

    // Verify booking belongs to this user (unless admin)
    if (!$is_admin && strtolower($booking['customer_email']) !== strtolower($request_email)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized access to this booking']);
        exit;
    }

    echo json_encode(['success' => true, 'booking' => $booking]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server Error: ' . $e->getMessage()]);
}

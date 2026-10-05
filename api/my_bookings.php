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

    // Auth: OTP login uses localStorage (not PHP sessions).
    // Frontend passes ?email= from localStorage.
    $email = trim($_GET['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
        exit;
    }

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
        WHERE customer_mobile = ?
        ORDER BY booked_at DESC
    ");

    if (!$stmt) {
        throw new \Exception("Database error: " . $mysqli->error);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    echo json_encode(['success' => true, 'bookings' => $bookings]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server Error: ' . $e->getMessage()]);
}

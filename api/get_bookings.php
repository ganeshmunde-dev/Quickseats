<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/db.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized access. Admins only.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Only GET requests are allowed']);
        exit;
    }

    $check = $mysqli->query("SHOW TABLES LIKE 'bookmy_bus'");
    if ($check && $check->num_rows === 0) {
        echo json_encode([]);
        exit;
    }

    // DB SCHEMA NOTE: bookmy_bus has both old-style and new-style columns.
    // Select only the new clean columns for admin view.
    // customer_mobile stores the user's email (used as identifier).
    $result = $mysqli->query(
        "SELECT id,
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
         ORDER BY booked_at DESC"
    );

    $bookings = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Add selected_seats if column exists
            $bookings[] = $row;
        }
    }

    echo json_encode($bookings);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server Error: ' . $e->getMessage()
    ]);
}

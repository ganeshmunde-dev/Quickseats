<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/db.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Only GET requests are allowed']);
        exit;
    }

    if (empty($_GET['route_id']) || empty($_GET['travel_date'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'route_id and travel_date are required']);
        exit;
    }

    $route_id = (int) $_GET['route_id'];
    $travel_date = trim($_GET['travel_date']);

    // Make sure table exists first
    $tableName = 'bookmy_bus';
    $check = $mysqli->query("SHOW TABLES LIKE '$tableName'");
    if ($check && $check->num_rows === 0) {
        echo json_encode(['success' => true, 'booked_seats' => []]);
        exit;
    }

    // Ensure selected_seats column exists
    $columns = $mysqli->query("SHOW COLUMNS FROM `$tableName` LIKE 'selected_seats'");
    if ($columns && $columns->num_rows === 0) {
        echo json_encode(['success' => true, 'booked_seats' => []]);
        exit;
    }

    $stmt = $mysqli->prepare("SELECT selected_seats FROM bookmy_bus WHERE route_id = ? AND travel_date = ?");
    $stmt->bind_param('is', $route_id, $travel_date);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $booked_seats = [];
    while ($row = $res->fetch_assoc()) {
        if (!empty($row['selected_seats'])) {
            $seats = array_map('trim', explode(',', $row['selected_seats']));
            $booked_seats = array_merge($booked_seats, $seats);
        }
    }
    $stmt->close();

    echo json_encode(['success' => true, 'booked_seats' => array_values(array_unique($booked_seats))]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch seats: ' . $e->getMessage()]);
}
?>

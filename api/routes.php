<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/db.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $result = $mysqli->query("SELECT * FROM bus_routes WHERE travel_date >= CURDATE() ORDER BY travel_date ASC, travel_time ASC");
        $routes = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['id'] = (int)$row['id'];
                $row['price'] = (float)$row['price'];
                $row['seats'] = (int)$row['seats'];
                $routes[] = $row;
            }
        }
        echo json_encode($routes);
        exit;
    }

    // Check admin authentication for writing actions (POST, DELETE)
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized admin access required.']);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $from = trim($input['from_location'] ?? $input['route_from'] ?? $input['from'] ?? '');
        $to = trim($input['to_location'] ?? $input['route_to'] ?? $input['to'] ?? '');
        $date = trim($input['travel_date'] ?? $input['date'] ?? '');
        $time = trim($input['travel_time'] ?? $input['time'] ?? '');
        $type = trim($input['bus_type'] ?? $input['type'] ?? '');
        $price = (float)($input['price'] ?? 0);
        $seats = (int)($input['seats'] ?? 0);

        if (empty($from) || empty($to) || empty($date) || empty($time) || empty($type) || $price <= 0 || $seats <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing or invalid parameters.']);
            exit;
        }

        $stmt = $mysqli->prepare("
            INSERT INTO bus_routes (route_from, route_to, travel_date, travel_time, bus_type, price, seats)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sssssdi', $from, $to, $date, $time, $type, $price, $seats);
        $stmt->execute();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Route added successfully',
            'id' => $stmt->insert_id
        ]);
        $stmt->close();
        exit;
    }

    if ($method === 'DELETE' || ($method === 'POST' && isset($_GET['clear']))) {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $mysqli->prepare("DELETE FROM bus_routes WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Route deleted.']);
        } else {
            $mysqli->query("TRUNCATE TABLE bus_routes");
            echo json_encode(['success' => true, 'message' => 'All routes cleared.']);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>

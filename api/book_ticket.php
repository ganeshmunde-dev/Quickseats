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
        echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }

    // Auth: Accept email from JSON payload (OTP-based login uses localStorage, not PHP sessions)
    // Fall back to session if set (admin or legacy flow)
    $request_email = trim($input['customer_email'] ?? '');
    if (!filter_var($request_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in first.']);
        exit;
    }
    $customer_mobile = filter_var($request_email, FILTER_SANITIZE_EMAIL);

    // Validate all required fields are present
    $required = ['passenger_name','route_id','route_from','route_to','travel_date','travel_time','bus_type','seats_booked','selected_seats','price_per_seat','total_price'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || ($input[$field] === '' && $input[$field] !== '0')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            exit;
        }
    }

    // Assign variables
    $passenger_name  = trim($input['passenger_name']);
    $route_id        = (int) $input['route_id'];
    $route_from      = trim($input['route_from']);
    $route_to        = trim($input['route_to']);
    $travel_date     = trim($input['travel_date']);
    $travel_time     = trim($input['travel_time']);
    $bus_type        = trim($input['bus_type']);
    $seats_booked    = (int) $input['seats_booked'];
    $selected_seats  = trim($input['selected_seats']);
    $price_per_seat  = (float) $input['price_per_seat'];
    $total_price     = (float) $input['total_price'];

    // Check which optional columns exist in bookmy_bus
    function columnExists($mysqli, $table, $column) {
        $res = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $res && $res->num_rows > 0;
    }

    $mysqli->begin_transaction();

    // Verify route exists and has enough seats
    $stmt = $mysqli->prepare("SELECT seats FROM bus_routes WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $route_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        throw new \Exception("Selected bus route not found.");
    }

    $route = $res->fetch_assoc();
    if ($route['seats'] < $seats_booked) {
        throw new \Exception("Not enough seats. Only " . $route['seats'] . " seats left.");
    }
    $stmt->close();

    // Check for seat conflicts if selected_seats column exists
    $hasSelectedSeats = columnExists($mysqli, 'bookmy_bus', 'selected_seats');
    if ($hasSelectedSeats && !empty($selected_seats)) {
        $requested_seats = array_map('trim', explode(',', $selected_seats));
        $stmt = $mysqli->prepare(
            "SELECT selected_seats FROM bookmy_bus WHERE route_id = ? AND travel_date = ?"
        );
        $stmt->bind_param('is', $route_id, $travel_date);
        $stmt->execute();
        $booked_res = $stmt->get_result();

        while ($row = $booked_res->fetch_assoc()) {
            if (!empty($row['selected_seats'])) {
                $existing   = array_map('trim', explode(',', $row['selected_seats']));
                $intersect  = array_intersect($requested_seats, $existing);
                if (count($intersect) > 0) {
                    throw new \Exception("Seats " . implode(', ', $intersect) . " are already booked.");
                }
            }
        }
        $stmt->close();
    }

    // Decrement available seats
    $stmt = $mysqli->prepare("UPDATE bus_routes SET seats = seats - ? WHERE id = ?");
    $stmt->bind_param('ii', $seats_booked, $route_id);
    $stmt->execute();
    $stmt->close();

    // Build INSERT dynamically based on which columns actually exist
    $hasUtr           = columnExists($mysqli, 'bookmy_bus', 'utr');
    $hasCustomerEmail = columnExists($mysqli, 'bookmy_bus', 'customer_email');

    // Always-present columns (confirmed in DB schema)
    $cols   = "customer_mobile, passenger_name, route_id, route_from, route_to, travel_date, travel_time, bus_type, seats_booked, price_per_seat, total_price, booked_at";
    $marks  = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()";
    $types  = "ssisssssidd";
    $params = [$customer_mobile, $passenger_name, $route_id, $route_from, $route_to,
               $travel_date, $travel_time, $bus_type, $seats_booked,
               $price_per_seat, $total_price];

    // Add optional columns if they exist
    if ($hasSelectedSeats) {
        $cols   .= ", selected_seats";
        $marks  .= ", ?";
        $types  .= "s";
        $params[] = $selected_seats;
    }

    if ($hasUtr) {
        $utr    = isset($input['utr']) ? trim($input['utr']) : null;
        $cols   .= ", utr";
        $marks  .= ", ?";
        $types  .= "s";
        $params[] = $utr;
    }

    // Also fill legacy old-style columns (they are NOT NULL so need values)
    $cols   .= ", passengername, mobileno, `route id`, routefrom, routeto, `bus type`, price, `seat booked`, `total price`, traveldate";
    $marks  .= ", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
    $types  .= "ssisssdids";
    $params[] = substr($passenger_name, 0, 30);
    $params[] = substr($customer_mobile, 0, 10);
    $params[] = $route_id;
    $params[] = substr($route_from, 0, 100);
    $params[] = substr($route_to, 0, 100);
    $params[] = substr($bus_type, 0, 50);
    $params[] = $price_per_seat;
    $params[] = $seats_booked;
    $params[] = $total_price;
    $params[] = $travel_date;

    $sql  = "INSERT INTO bookmy_bus ($cols) VALUES ($marks)";
    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $insert_id = $stmt->insert_id;
    $stmt->close();

    $mysqli->commit();

    echo json_encode(['success' => true, 'insert_id' => $insert_id]);

} catch (\Exception $e) {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->rollback();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Booking failed: ' . $e->getMessage()]);
}

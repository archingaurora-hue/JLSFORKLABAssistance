<?php
require 'db_conn.php';
header('Content-Type: application/json');

$load_id = isset($_POST['load_id']) ? intval($_POST['load_id']) : 0;

$durations = [
    'Washing' => 90,
    'Drying' => 120,
    'Folding' => 60
];

if ($load_id > 0) {
    // Fetch current status AND the order's services
    $res = $conn->query("
        SELECT pl.status, o.services_requested 
        FROM process_load pl
        JOIN `Order` o ON pl.order_id = o.order_id
        WHERE pl.load_id = $load_id
    ");

    if ($res && $row = $res->fetch_assoc()) {
        $current_status = $row['status'];
        $services = strtolower($row['services_requested']);

        $isWash = str_contains($services, 'wash');
        $isDry = str_contains($services, 'dry');
        $isFold = str_contains($services, 'fold');

        // Build cycle based on services picked
        $status_cycle = ['Pending Dropoff', 'In Queue'];
        if ($isWash) {
            $status_cycle[] = 'Washing';
            $status_cycle[] = 'Wash Complete';
        }
        if ($isDry) {
            $status_cycle[] = 'Drying';
            $status_cycle[] = 'Drying Complete';
        }
        if ($isFold) {
            $status_cycle[] = 'Folding';
            $status_cycle[] = 'Folding Complete';
        }
        $status_cycle[] = 'Awaiting Pickup';
        $status_cycle[] = 'Completed';

        $current_index = array_search($current_status, $status_cycle);
        $next_index = $current_index + 1;

        if (isset($status_cycle[$next_index])) {
            $next_status = $status_cycle[$next_index];
            $next_timer = $durations[$next_status] ?? 0;

            $stmt = $conn->prepare("UPDATE process_load SET status = ?, timer_paused = ?, end_time = NULL WHERE load_id = ?");
            $stmt->bind_param("sii", $next_status, $next_timer, $load_id);
            $stmt->execute();

            session_start();
            $source = $_POST['source'] ?? 'button';
            $employee_name = ($source === 'timer') ? 'Timer' : ($_SESSION['full_name'] ?? 'System');

            $logStmt = $conn->prepare("INSERT INTO System_Log (load_id, status_event, employee_name, timestamp) VALUES (?, ?, ?, NOW())");
            $logStmt->bind_param("iss", $load_id, $next_status, $employee_name);
            $logStmt->execute();
            $logStmt->close();

            echo json_encode([
                "success" => true,
                "next_status" => $next_status,
                "is_final" => ($next_status == 'Completed'),
                "timer_set" => $next_timer
            ]);
        }
    }
}
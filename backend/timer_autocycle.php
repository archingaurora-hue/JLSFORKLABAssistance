<?php
// to cycle next status

require 'db_conn.php';
header('Content-Type: application/json');

$load_id = isset($_POST['load_id']) ? intval($_POST['load_id']) : 0;

$status_cycle = [
    'Pending Dropoff',
    'In Queue',
    'Washing',
    'Wash Complete',
    'Drying',
    'Drying Complete',
    'Folding',
    'Folding Complete',
    'Awaiting Pickup',
    'Completed'
];

// SET DURATION FOR STATUSES (in seconds):
$durations = [
    'Washing' => 90,
    'Drying'  => 120,
    'Folding' => 60
];

if ($load_id > 0) {
    // Fetch current status
    $res = $conn->query("SELECT status FROM process_load WHERE load_id = $load_id");

    if ($res && $row = $res->fetch_assoc()) {
        $current_status = $row['status'];
        $current_index = array_search($current_status, $status_cycle);
        $next_index = $current_index + 1;

        if (isset($status_cycle[$next_index])) {
            $next_status = $status_cycle[$next_index];

            // fetch new status's duratioon
            $next_timer = isset($durations[$next_status]) ? $durations[$next_status] : 0;

            // Update Database (New Status, New Timer, Reset End Time)
            $stmt = $conn->prepare("UPDATE process_load SET status = ?, timer_paused = ?, end_time = NULL WHERE load_id = ?");
            $stmt->bind_param("sii", $next_status, $next_timer, $load_id);
            $stmt->execute();

            echo json_encode([
                "success" => true,
                "next_status" => $next_status,
                "is_final" => ($next_status == 'Completed'),
                "timer_set" => $next_timer
            ]);
        }
    }
}

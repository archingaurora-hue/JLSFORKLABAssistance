<?php
require 'db_conn.php';
require 'timer_config.php';
header('Content-Type: application/json');

$load_id = intval($_POST['load_id'] ?? 0);
if ($load_id <= 0) {
    exit;
}

// Get current status
$res = $conn->query("SELECT pl.status, o.services_requested FROM process_load pl 
                     JOIN `Order` o ON pl.order_id = o.order_id WHERE pl.load_id = $load_id");
$data = $res->fetch_assoc();

// Get next step
$cycle = getStatusCycle($data['services_requested']);
$curr_idx = array_search($data['status'], $cycle);
$next_status = $cycle[$curr_idx + 1] ?? null;

if ($next_status) {
    // Go to next status, Update Database, Set timer
    $timer = $durations[$next_status] ?? 0;
    $stmt = $conn->prepare("UPDATE process_load SET status = ?, timer_paused = ?, end_time = NULL WHERE load_id = ?");
    $stmt->bind_param("sii", $next_status, $timer, $load_id);
    $stmt->execute();

    // Log the event
    session_start();
    $user = ($_POST['source'] === 'timer') ? 'Timer' : ($_SESSION['full_name'] ?? 'System');
    $log = $conn->prepare("INSERT INTO System_Log (load_id, status_event, employee_name) VALUES (?, ?, ?)");
    $log->bind_param("iss", $load_id, $next_status, $user);
    $log->execute();
    echo json_encode(['success' => true, 'is_final' => ($next_status === 'Completed')]);
}
<?php
require 'db_conn.php';
require 'timer_config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$load_id = intval($_GET['load_id'] ?? 0);

// Get current status, Build cycle
$res = $conn->query("SELECT pl.status, o.services_requested 
                      FROM process_load pl 
                      JOIN `Order` o ON pl.order_id = o.order_id 
                      WHERE pl.load_id = $load_id");
$row = $res->fetch_assoc();
$cycle = getStatusCycle($row['services_requested'] ?? '');

// HANDLE ACTIONS
if ($action == 'reset') { // reset function
    $current_status = $row['status'];
    $current_duration = $durations[$current_status] ?? 0;

    if ($current_duration > 0) {
        // restart the current timer
        $conn->query("UPDATE process_load SET timer_paused = $current_duration, end_time = NULL WHERE load_id = $load_id");
    } else {
        // return to prev status if no timer
        $current_index = array_search($current_status, $cycle);
        if ($current_index > 0) {
            $prev_status = $cycle[$current_index - 1];
            $prev_duration = $durations[$prev_status] ?? 0;
            $conn->query("UPDATE process_load SET status = '$prev_status', timer_paused = $prev_duration, end_time = NULL WHERE load_id = $load_id");
        }
    }
} elseif ($action == 'pause') { // pause function
    $res = $conn->query("SELECT TIMESTAMPDIFF(SECOND, NOW(), end_time) as rem FROM process_load WHERE load_id = $load_id");
    $rem = max(0, intval($res->fetch_assoc()['rem'] ?? 0));
    $conn->query("UPDATE process_load SET timer_paused = $rem, end_time = NULL WHERE load_id = $load_id");
} elseif ($action == 'resume') { //resume function
    $res = $conn->query("SELECT timer_paused FROM process_load WHERE load_id = $load_id");
    $paused_sec = intval($res->fetch_assoc()['timer_paused'] ?? 0);
    $conn->query("UPDATE process_load SET end_time = DATE_ADD(NOW(), INTERVAL $paused_sec SECOND), timer_paused = NULL WHERE load_id = $load_id");
} elseif ($action == 'finish') { //finish function
    $conn->query("UPDATE process_load SET timer_paused = 0, end_time = NULL WHERE load_id = $load_id");
}

// return current state to JS
$res = $conn->query("SELECT status, timer_paused, end_time,
                     TIMESTAMPDIFF(SECOND, NOW(), end_time) AS live_rem 
                     FROM process_load WHERE load_id = $load_id");
$row = $res->fetch_assoc();

$is_paused = ($row['timer_paused'] !== null);
$remaining = $is_paused ? intval($row['timer_paused']) : intval($row['live_rem']);

echo json_encode([
    'remaining' => max(0, $remaining),
    'is_paused' => $is_paused,
    'status' => $row['status']
]);

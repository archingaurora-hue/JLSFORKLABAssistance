<?php
// to handle buttons

require 'db_conn.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$load_id = intval($_GET['load_id'] ?? 0);

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

// HANDLE ACTIONS
if ($action == 'reset') { // reset function
    $res = $conn->query("SELECT status FROM process_load WHERE load_id = $load_id");
    $row = $res->fetch_assoc();
    $current_status = $row['status'];
    $current_duration = $durations[$current_status] ?? 0;

    if ($current_duration > 0) {
        // restart the current timer
        $conn->query("UPDATE process_load SET timer_paused = $current_duration, end_time = NULL WHERE load_id = $load_id");
    } else {
        // return to prev status
        $current_index = array_search($current_status, $status_cycle);
        if ($current_index > 0) {
            $prev_status = $status_cycle[$current_index - 1];
            $prev_duration = $durations[$prev_status] ?? 0;
            $conn->query("UPDATE process_load SET status = '$prev_status', timer_paused = $prev_duration, end_time = NULL WHERE load_id = $load_id");
        }
    }
} elseif ($action == 'pause') { // pause function
    $res = $conn->query("SELECT TIMESTAMPDIFF(SECOND, NOW(), end_time) as rem FROM process_load WHERE load_id = $load_id");
    $row = $res->fetch_assoc();
    $rem = max(0, intval($row['rem'] ?? 0));
    $conn->query("UPDATE process_load SET timer_paused = $rem, end_time = NULL WHERE load_id = $load_id");
} elseif ($action == 'resume') { //resume function
    $res = $conn->query("SELECT timer_paused FROM process_load WHERE load_id = $load_id");
    $row = $res->fetch_assoc();
    $paused_sec = intval($row['timer_paused'] ?? 120);
    $conn->query("UPDATE process_load SET end_time = DATE_ADD(NOW(), INTERVAL $paused_sec SECOND), timer_paused = NULL WHERE load_id = $load_id");
} elseif ($action == 'finish') { //finish function
    $conn->query("UPDATE process_load SET timer_paused = 0, end_time = NULL WHERE load_id = $load_id");
}

// FETCH CURRENT STATE
$query = "SELECT status, timer_paused, end_time, TIMESTAMPDIFF(SECOND, NOW(), end_time) AS live_rem FROM process_load WHERE load_id = $load_id";
$res = $conn->query($query);
$row = $res->fetch_assoc();

$is_paused = ($row['timer_paused'] !== null);
$remaining = $is_paused ? intval($row['timer_paused']) : intval($row['live_rem']);

echo json_encode([
    'remaining' => max(0, $remaining),
    'is_paused' => $is_paused,
    'status' => $row['status']
]);

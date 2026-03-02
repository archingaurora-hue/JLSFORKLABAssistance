<?php
// to sync timers across all dashboards

require 'db_conn.php';
header('Content-Type: application/json');

// Get all unfinished loads:
$query = "SELECT load_id, status, timer_paused, end_time, 
          TIMESTAMPDIFF(SECOND, NOW(), end_time) as live_rem 
          FROM process_load 
          WHERE status NOT IN ('Completed', 'Order Completed')";
$result = $conn->query($query);

$timers = [];
while ($row = $result->fetch_assoc()) {
    $is_paused = ($row['timer_paused'] !== null);
    $timers[] = [
        'load_id' => $row['load_id'],
        'status' => $row['status'],
        'remaining' => $is_paused ? intval($row['timer_paused']) : intval($row['live_rem']),
        'is_paused' => $is_paused,
        'end_time' => $row['end_time'],
    ];
}
echo json_encode($timers);

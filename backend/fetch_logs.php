<?php
require 'db_conn.php';

if (isset($_GET['load_id'])) {
    $load_id = intval($_GET['load_id']);

    $query = "SELECT * FROM `System_Log` WHERE load_id = ? ORDER BY timestamp DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $load_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $time = date("M d, h:i A", strtotime($row['timestamp']));
            echo '<div class="mb-2 pb-2 border-bottom border-white">';
            echo '<div class="d-flex justify-content-between">';
            echo '<strong class="text-dark">' . htmlspecialchars($row['status_event']) . '</strong>';
            echo '<span class="text-muted" style="font-size:0.75rem">' . $time . '</span>';
            echo '</div>';
            echo '<div class="text-muted fst-italic small">Updated by ' . htmlspecialchars($row['employee_name']) . '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="text-center text-muted py-3">No history logs found for this bag.</div>';
    }
}

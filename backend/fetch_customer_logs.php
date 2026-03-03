<?php
require 'db_conn.php';

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    $query = "SELECT sl.*, pl.bag_label 
              FROM `System_Log` sl
              JOIN `Process_Load` pl ON sl.load_id = pl.load_id
              WHERE pl.order_id = ?
              ORDER BY sl.timestamp DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($log = $result->fetch_assoc()) {
            $time = date("M d, h:i A", strtotime($log['timestamp']));
            echo '<div class="mb-3 pb-2 border-bottom border-light last-no-border">';
            echo '<div class="d-flex justify-content-between">';
            echo '<strong class="small text-dark">' . htmlspecialchars($log['status_event']) . '</strong>';
            echo '<small class="text-muted" style="font-size: 0.7rem;">' . $time . '</small>';
            echo '</div>';
            echo '<div class="small text-muted fst-italic mt-1">';
            echo '<i class="bi bi-box-seam me-1"></i>' . htmlspecialchars($log['bag_label']);
            echo '<span class="mx-1">•</span>';
            echo 'updated by ' . htmlspecialchars($log['employee_name']);
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="text-center text-muted small py-3">';
        echo '<i class="bi bi-clock-history d-block mb-1 fs-4"></i>';
        echo 'No logs available yet.';
        echo '</div>';
    }
}
<?php
require 'db_conn.php';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    $query = "SELECT log_message, created_at 
              FROM `order_logs` 
              WHERE order_id = ? 
              ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($log = $result->fetch_assoc()) {
            $time = date("M d, h:i A", strtotime($log['created_at']));

            echo '<div class="mb-3 pb-2 border-bottom border-light last-no-border">';
            echo '<div class="d-flex justify-content-between align-items-start">';

            echo '<div class="text-dark small pe-2" style="line-height: 1.3;">' . htmlspecialchars($log['log_message']) . '</div>';
            echo '<small class="text-muted text-nowrap" style="font-size: 0.7rem;">' . $time . '</small>';

            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="text-center text-muted small py-4">';
        echo '<i class="bi bi-clock-history d-block mb-2 fs-4"></i>';
        echo 'No updates recorded for this order yet.';
        echo '</div>';
    }
    $stmt->close();
}
$conn->close();

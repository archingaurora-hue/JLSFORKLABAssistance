<?php
require 'db_conn.php';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Updated to query the correct table (order_logs) and columns (log_message, created_at)
    $query = "SELECT log_message, created_at 
              FROM `order_logs` 
              WHERE order_id = ? 
              ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $time = date("M d, h:i A", strtotime($row['created_at']));

            echo '<div class="mb-2 pb-2 border-bottom border-light">';
            echo '<div class="d-flex justify-content-between align-items-start">';

            echo '<div class="text-dark small" style="line-height: 1.2;">' . htmlspecialchars($row['log_message']) . '</div>';
            echo '<span class="text-muted ms-2" style="font-size:0.7rem; white-space: nowrap;">' . $time . '</span>';

            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="text-center text-muted py-3">No history logs found for this order.</div>';
    }
    $stmt->close();
}
$conn->close();

<?php
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../customer_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $user_id = $_SESSION['user_id'];
    $order_id = $_POST['order_id'];

    // Verify the order belongs to the logged-in user
    $stmt = $conn->prepare("SELECT status FROM `Order` WHERE order_id = ? AND customer_id = ?");
    $stmt->bind_param("si", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();

        // Prevent cancelling already cancelled or completed orders
        if ($order['status'] !== 'Cancelled' && $order['status'] !== 'Completed') {

            // Fetch all loads to ensure none have started washing/drying
            $loadsStmt = $conn->prepare("SELECT status FROM `Process_Load` WHERE order_id = ?");
            $loadsStmt->bind_param("s", $order_id);
            $loadsStmt->execute();
            $loadsResult = $loadsStmt->get_result();

            $canCancel = true;
            while ($load = $loadsResult->fetch_assoc()) {
                if (!in_array($load['status'], ['Pending Dropoff', 'In Queue', 'Pending'])) {
                    $canCancel = false;
                    break;
                }
            }

            if ($canCancel) {
                // Cancel the Order
                $cancelStmt = $conn->prepare("UPDATE `Order` SET status = 'Cancelled', current_phase = 'Cancelled' WHERE order_id = ?");
                $cancelStmt->bind_param("s", $order_id);
                $cancelStmt->execute();

                // Cancel all associated loads
                $cancelLoadsStmt = $conn->prepare("UPDATE `Process_Load` SET status = 'Cancelled' WHERE order_id = ?");
                $cancelLoadsStmt->bind_param("s", $order_id);
                $cancelLoadsStmt->execute();

                // Log the cancellation
                $logMsg = "Order cancelled by customer.";
                $logStmt = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
                $logStmt->bind_param("ss", $order_id, $logMsg);
                $logStmt->execute();

                $_SESSION['success_msg'] = "Order #$order_id has been successfully cancelled.";
            } else {
                $_SESSION['error_msg'] = "Order cannot be cancelled because it is already being processed.";
            }
        }
    }
    header("Location: ../dashboard.php");
    exit();
}

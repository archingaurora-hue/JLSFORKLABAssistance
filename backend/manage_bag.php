<?php
session_start();
require 'db_conn.php';

// Kick out if not an employee or manager
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    header("Location: ../staff_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $employee_name = $_SESSION['full_name'] ?? 'Staff';

    // ==========================================
    // ADD BAG LOGIC
    // ==========================================
    if ($action === 'add_bag') {
        $order_id = $_POST['order_id'] ?? '';
        $bag_label = trim($_POST['bag_label'] ?? '');

        if (!empty($order_id) && !empty($bag_label)) {
            // Fetch order details for pricing
            $orderQuery = $conn->query("SELECT services_requested, supplies_requested, final_price FROM `Order` WHERE order_id = '$order_id'");
            if ($orderRow = $orderQuery->fetch_assoc()) {

                $services = $orderRow['services_requested'];
                $supplies = $orderRow['supplies_requested'];
                $current_price = floatval($orderRow['final_price']);

                // Calculate price per load
                $costPerLoad = 0;
                $isWash = stripos($services, 'Wash') !== false;
                if ($isWash) $costPerLoad += 55;
                if (stripos($services, 'Dry') !== false) $costPerLoad += 60;
                if (stripos($services, 'Fold') !== false) $costPerLoad += 30;
                if ($isWash) {
                    if (stripos($supplies, 'Detergent') !== false) $costPerLoad += 20;
                    if (stripos($supplies, 'Fabric Softener') !== false) $costPerLoad += 10;
                }

                // Update total price
                $new_price = $current_price + $costPerLoad;
                $conn->query("UPDATE `Order` SET final_price = $new_price WHERE order_id = '$order_id'");

                // Insert new bag
                $load_category = "Extra Bag";
                $status = "In Queue";
                $stmt = $conn->prepare("INSERT INTO `Process_Load` (order_id, load_category, bag_label, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $order_id, $load_category, $bag_label, $status);

                if ($stmt->execute()) {
                    $log_msg = "$employee_name added an extra bag ($bag_label). Price auto-adjusted (+₱$costPerLoad).";
                    $log_stmt = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
                    $log_stmt->bind_param("ss", $order_id, $log_msg);
                    $log_stmt->execute();

                    $conn->query("UPDATE `Order` SET status = 'In Progress' WHERE order_id = '$order_id'");
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete_bag') {
        $load_id = intval($_POST['load_id'] ?? 0);
        $order_id = $_POST['order_id'] ?? '';

        if ($load_id > 0 && !empty($order_id)) {
            // Get bag and order details for pricing
            $stmt = $conn->prepare("SELECT pl.bag_label, o.services_requested, o.supplies_requested, o.final_price FROM `Process_Load` pl JOIN `Order` o ON pl.order_id = o.order_id WHERE pl.load_id = ?");
            $stmt->bind_param("i", $load_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $bag_label = $row['bag_label'];
                $services = $row['services_requested'];
                $supplies = $row['supplies_requested'];
                $current_price = floatval($row['final_price']);

                // Calculate price per load
                $costPerLoad = 0;
                $isWash = stripos($services, 'Wash') !== false;
                if ($isWash) $costPerLoad += 55;
                if (stripos($services, 'Dry') !== false) $costPerLoad += 60;
                if (stripos($services, 'Fold') !== false) $costPerLoad += 30;
                if ($isWash) {
                    if (stripos($supplies, 'Detergent') !== false) $costPerLoad += 20;
                    if (stripos($supplies, 'Fabric Softener') !== false) $costPerLoad += 10;
                }

                // Update price (prevent going below 0)
                $new_price = max(0, $current_price - $costPerLoad);
                $conn->query("UPDATE `Order` SET final_price = $new_price WHERE order_id = '$order_id'");

                // Delete the bag
                $conn->query("DELETE FROM `Process_Load` WHERE load_id = $load_id");

                // Log the action
                $log_msg = "$employee_name deleted bag: $bag_label. Price auto-adjusted (-₱$costPerLoad).";
                $log_stmt = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
                $log_stmt->bind_param("ss", $order_id, $log_msg);
                $log_stmt->execute();

                // Re-evaluate if the order is completed after deletion
                $rem = $conn->query("SELECT COUNT(*) as c FROM `Process_Load` WHERE order_id = '$order_id' AND status != 'Completed'")->fetch_assoc();
                $masterStatus = ($rem['c'] == 0) ? 'Completed' : 'In Progress';
                $conn->query("UPDATE `Order` SET status = '$masterStatus' WHERE order_id = '$order_id'");
            }
            $stmt->close();
        }
    } elseif ($action === 'cancel_order') {
        $order_id = $_POST['order_id'] ?? '';

        if (!empty($order_id)) {
            // Cancel the order
            $conn->query("UPDATE `Order` SET status = 'Cancelled' WHERE order_id = '$order_id'");

            // Cancel remaining loads
            $conn->query("UPDATE `Process_Load` SET status = 'Cancelled' WHERE order_id = '$order_id'");

            // Log the cancellation action
            $log_msg = "$employee_name cancelled the order by removing the final bag.";
            $log_stmt = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
            $log_stmt->bind_param("ss", $order_id, $log_msg);
            $log_stmt->execute();
        }
    }
}

// Redirect back
header("Location: ../employee_dashboard.php");
exit();

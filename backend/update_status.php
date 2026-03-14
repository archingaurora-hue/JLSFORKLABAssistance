<?php
session_start();
require 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $load_id = $_POST['load_id'];
    $action = $_POST['action'];
    $employee_name = $_SESSION['full_name'] ?? 'Staff';

    // Fetch bag details and parent services
    $stmt = $conn->prepare("SELECT pl.*, o.services_requested 
                            FROM `Process_Load` pl 
                            JOIN `Order` o ON pl.order_id = o.order_id 
                            WHERE pl.load_id = ?");
    $stmt->bind_param("i", $load_id);
    $stmt->execute();
    $bag = $stmt->get_result()->fetch_assoc();

    $services = $bag['services_requested'];
    $curr = $bag['status'];
    $order_id = $bag['order_id'];
    $bag_label = $bag['bag_label'];

    $hasW = stripos($services, 'Wash') !== false;
    $hasD = stripos($services, 'Dry') !== false;
    $hasF = stripos($services, 'Fold') !== false;

    if ($action === 'start_timer') {
        $mins = intval($_POST['minutes']);
        $end = date('Y-m-d H:i:s', strtotime("+$mins minutes"));

        $new_status = $curr;
        // Requirement: If In Queue, starting a timer moves it to the machine phase
        if ($curr === 'In Queue') {
            if ($hasW) $new_status = 'Washing';
            elseif ($hasD) $new_status = 'Drying';
        }

        $upd = $conn->prepare("UPDATE `Process_Load` SET timer_end = ?, status = ? WHERE load_id = ?");
        $upd->bind_param("ssi", $end, $new_status, $load_id);
        $upd->execute();

        $msg = "$employee_name started $mins min timer for $bag_label. Status: $new_status.";
        $log = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
        $log->bind_param("ss", $order_id, $msg);
        $log->execute();
    } elseif ($action === 'next_phase') {
        $next = 'Awaiting Pickup';

        if ($curr === 'Pending Dropoff') {
            $next = 'In Queue';
        }
        // Note: The 'In Queue' -> 'Washing' transition is now handled by 'start_timer'
        elseif ($curr === 'Washing') {
            if ($hasD) $next = 'Drying';
            elseif ($hasF) $next = 'Folding';
        } elseif ($curr === 'Drying') {
            if ($hasF) $next = 'Folding';
            else $next = 'Awaiting Pickup';
        } elseif ($curr === 'Folding') {
            $next = 'Awaiting Pickup';
        } elseif ($curr === 'Awaiting Pickup') {
            $next = 'Completed';
        }

        $upd = $conn->prepare("UPDATE `Process_Load` SET status = ?, timer_end = NULL WHERE load_id = ?");
        $upd->bind_param("si", $next, $load_id);
        $upd->execute();

        // Update Master Order status
        $rem = $conn->query("SELECT COUNT(*) as c FROM `Process_Load` WHERE order_id = '$order_id' AND status != 'Completed'")->fetch_assoc();
        $masterStatus = ($rem['c'] == 0) ? 'Completed' : 'In Progress';
        $conn->query("UPDATE `Order` SET status = '$masterStatus' WHERE order_id = '$order_id'");

        $msg = "$employee_name moved $bag_label to $next.";
        $log = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
        $log->bind_param("ss", $order_id, $msg);
        $log->execute();
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
}

<?php
session_start();
require 'db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['order_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'error' => 'Missing data or session expired']);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$order_id = $_POST['order_id'];
$message_text = trim($_POST['message']);

if (empty($message_text)) {
    echo json_encode(['success' => false, 'error' => 'Message is empty']);
    exit();
}

// Security Check: If customer, ensure they own the order
if ($role === 'Customer') {
    $checkQuery = $conn->prepare("SELECT customer_id FROM `order` WHERE order_id = ?");
    $checkQuery->bind_param("s", $order_id);
    $checkQuery->execute();
    $result = $checkQuery->get_result();
    $order = $result->fetch_assoc();

    if (!$order || $order['customer_id'] != $user_id) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }
}

// Insert message
$stmt = $conn->prepare("INSERT INTO `order_messages` (order_id, sender_id, message_text) VALUES (?, ?, ?)");
$stmt->bind_param("sis", $order_id, $user_id, $message_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

<?php
session_start();
require 'db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$notifications = [];

if ($role === 'Customer') {
    // Customers only see unread messages from Staff on THEIR specific orders
    $query = "
        SELECT m.message_id, m.order_id, m.message_text, u.first_name as sender_name, o.tracking_code 
        FROM `order_messages` m
        JOIN `order` o ON m.order_id = o.order_id
        JOIN `user` u ON m.sender_id = u.user_id
        WHERE o.customer_id = ? AND m.sender_id != ? AND m.is_read = 0
        ORDER BY m.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
} else {
    // Staff see unread messages from Customers
    $query = "
        SELECT m.message_id, m.order_id, m.message_text, u.first_name as sender_name, o.tracking_code 
        FROM `order_messages` m
        JOIN `order` o ON m.order_id = o.order_id
        JOIN `user` u ON m.sender_id = u.user_id
        WHERE m.sender_id != ? AND u.role = 'Customer' AND m.is_read = 0
        ORDER BY m.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'unread_count' => count($notifications),
    'notifications' => $notifications
]);

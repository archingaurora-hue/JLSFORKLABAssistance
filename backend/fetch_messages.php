<?php
session_start();
require 'db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$order_id = $_GET['order_id'];
$current_user_id = $_SESSION['user_id'];

// --- NEW: Mark messages as read automatically when this chat is loaded ---
$updateQuery = "UPDATE `order_messages` SET is_read = 1 WHERE order_id = ? AND sender_id != ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("si", $order_id, $current_user_id);
$updateStmt->execute();
// ------------------------------------------------------------------------

$query = "
    SELECT m.message_id, m.message_text, DATE_FORMAT(m.created_at, '%b %d, %h:%i %p') as time_sent, 
           u.user_id, u.first_name, u.role
    FROM `order_messages` m
    JOIN `user` u ON m.sender_id = u.user_id
    WHERE m.order_id = ?
    ORDER BY m.created_at ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $row['is_mine'] = ($row['user_id'] == $current_user_id);
    $messages[] = $row;
}

echo json_encode(['success' => true, 'messages' => $messages]);

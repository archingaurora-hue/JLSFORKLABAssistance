<?php
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../customer_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (!empty($full_name)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE `User` SET full_name = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $full_name, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE `User` SET full_name = ? WHERE user_id = ?");
            $stmt->bind_param("si", $full_name, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name; // Update session name immediately
            $_SESSION['success_msg'] = "Profile updated successfully.";
        } else {
            $_SESSION['error_msg'] = "Failed to update profile.";
        }
        $stmt->close();
    }
    header("Location: ../dashboard.php");
    exit();
}

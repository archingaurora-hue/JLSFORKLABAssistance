<?php
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../customer_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (!empty($first_name) && !empty($last_name)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE `User` SET first_name = ?, last_name = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $first_name, $last_name, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE `User` SET first_name = ?, last_name = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $first_name, $last_name, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;

            $_SESSION['success_msg'] = "Profile updated successfully.";
        } else {
            $_SESSION['error_msg'] = "Failed to update profile.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_msg'] = "First Name and Last Name cannot be empty.";
    }

    header("Location: ../dashboard.php");
    exit();
}

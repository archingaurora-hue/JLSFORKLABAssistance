<?php
session_start();
require 'db_conn.php';

// Ensure the user is logged in (either Manager or Employee)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Manager', 'Employee'])) {
    die("Access Denied");
}

if (isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    // Security check: Only Managers are allowed to update their email address
    if ($_SESSION['role'] === 'Manager' && isset($_POST['email'])) {
        $email = trim($_POST['email']);
    } else {
        // Employees keep their existing email
        $email = $_SESSION['email'];
    }

    // Grab new password fields
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Determine the correct dashboard for redirection
    $dashboard_url = "../" . strtolower($_SESSION['role']) . "_dashboard.php";

    // If the user filled out the new password field
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            header("Location: {$dashboard_url}?status=password_mismatch");
            exit();
        }

        // Hash the new password
        $hashed_pwd = password_hash($new_password, PASSWORD_DEFAULT);

        // Update profile AND password
        $update_stmt = $conn->prepare("UPDATE `User` SET first_name=?, last_name=?, email=?, password=? WHERE user_id=?");
        $update_stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashed_pwd, $user_id);
    } else {
        // Update profile info ONLY (no password change)
        $update_stmt = $conn->prepare("UPDATE `User` SET first_name=?, last_name=?, email=? WHERE user_id=?");
        $update_stmt->bind_param("sssi", $first_name, $last_name, $email, $user_id);
    }

    if ($update_stmt->execute()) {
        // Update session variables so the UI reflects the new data instantly
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['email'] = $email;
        header("Location: {$dashboard_url}?status=profile_updated");
    } else {
        header("Location: {$dashboard_url}?status=error");
    }

    $update_stmt->close();
} else {
    header("Location: ../staff_login.php");
}
$conn->close();

<?php
session_start();
require 'db_conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../customer_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_account') {
    $user_id = $_SESSION['user_id'];

    // Optional: You can check if the user has "Pending" or "In Progress" orders here
    // and prevent deletion if they do, returning them to the dashboard with an error.

    // Delete user from the database
    // Note: Ensure your database schema uses 'ON DELETE CASCADE' for foreign keys 
    // related to user_id (like Orders), otherwise you'll need to manually delete those related rows first.
    $stmt = $conn->prepare("DELETE FROM `User` WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Deletion successful, destroy the session
        session_unset();
        session_destroy();

        // Redirect to login page with a success query parameter
        header("Location: ../customer_login.php?msg=account_deleted");
        exit();
    } else {
        // Deletion failed (e.g., due to database constraints)
        $_SESSION['error_msg'] = "Failed to delete account. Please ensure all your active orders are resolved or contact support.";
        header("Location: ../dashboard.php");
        exit();
    }

    $stmt->close();
} else {
    // Invalid request method
    header("Location: ../dashboard.php");
    exit();
}

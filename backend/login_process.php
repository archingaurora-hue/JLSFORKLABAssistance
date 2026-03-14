<?php
session_start();
require 'db_conn.php';

if (isset($_POST['signin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Updated to use 'User' (singular) as the correct table name
    // Includes security check to ensure only 'Customer' roles can log in here
    $stmt = $conn->prepare("SELECT user_id, password, role, full_name FROM `User` WHERE email = ? AND role = 'Customer'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['email'] = $email;

            // Redirect to the customer dashboard
            header("Location: ../dashboard.php");
            exit();
        }
    }

    // Generic error for incorrect password, email not found, or unauthorized role
    $_SESSION['login_error'] = "Invalid email or password.";
    header("Location: ../customer_login.php");
    exit();
}

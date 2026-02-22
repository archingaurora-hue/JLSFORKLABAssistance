<?php
session_start();
require 'db_conn.php';

if (isset($_POST['signin'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role, full_name FROM `User` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['email'] = $email;

            header("Location: ../dashboard.php");
            exit();
        }
    }

    // Generic error for BOTH incorrect password and email not found
    $_SESSION['login_error'] = "Invalid email or password.";
    header("Location: ../customer_login.php");
    exit();
}

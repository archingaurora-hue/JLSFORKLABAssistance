<?php
session_start();
require 'db_conn.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role, first_name, last_name FROM `User` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['role'] !== 'Manager' && $row['role'] !== 'Employee') {
            // Redirect with access denied status for regular customers trying to use staff login
            header("Location: ../staff_login.php?status=access_denied");
            exit();
        }

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];

            // Remember Me Implementation
            if (isset($_POST['remember'])) {
                // Set cookies for 30 days
                setcookie("staff_email", $email, time() + (30 * 24 * 60 * 60), "/");
                setcookie("staff_password", $password, time() + (30 * 24 * 60 * 60), "/");
            } else {
                // Destroy existing cookies if checkbox is unchecked
                if (isset($_COOKIE['staff_email'])) {
                    setcookie("staff_email", "", time() - 3600, "/");
                }
                if (isset($_COOKIE['staff_password'])) {
                    setcookie("staff_password", "", time() - 3600, "/");
                }
            }

            if ($row['role'] === 'Manager') {
                header("Location: ../manager_dashboard.php");
            } else {
                header("Location: ../employee_dashboard.php");
            }
            exit();
        }
    }

    // Redirect with invalid credentials status
    header("Location: ../staff_login.php?status=invalid_credentials");
    exit();
}

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

        // Role check
        if ($row['role'] !== 'Manager' && $row['role'] !== 'Employee') {
            $_SESSION['employee_login_error'] = "Access Denied. Customers must use the Customer Login.";
            header("Location: ../staff_login.php");
            exit();
        }

        // Password check
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];

            if ($row['role'] === 'Manager') {
                header("Location: ../manager_dashboard.php");
            } else {
                header("Location: ../employee_dashboard.php");
            }
            exit();
        }
    }

    // Generic error for BOTH incorrect password and account not found
    $_SESSION['staff_login_error'] = "Invalid email or password.";
    header("Location: ../staff_login.php");
    exit();
}

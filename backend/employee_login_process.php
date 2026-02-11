<?php
session_start();
require 'db_conn.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use $conn from db_conn.php
    $stmt = $conn->prepare("SELECT user_id, password, role, full_name FROM `User` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['role'] !== 'Manager' && $row['role'] !== 'Employee') {
            echo "<script>alert('Access Denied. Customers must use the Customer Login.'); window.location.href='../employee_login.php';</script>";
            exit();
        }

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];

            if ($row['role'] === 'Manager') {
                header("Location: ../manager_dashboard.php");
            } else {
                header("Location: ../employee_dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid Password!'); window.location.href='../employee_login.php';</script>";
        }
    } else {
        echo "<script>alert('Account not found!'); window.location.href='../employee_login.php';</script>";
    }
}

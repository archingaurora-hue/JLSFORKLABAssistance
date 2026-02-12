<?php
session_start();
require 'db_conn.php';

// Manager access only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    die("Access Denied: You do not have permission to perform this action.");
}

// Handle Add/Update
if (isset($_POST['save_employee'])) {
    $id = $_POST['user_id']; // Empty = New, Value = Edit
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $raw_password = $_POST['password'];

    if (empty($id)) {
        // Create new employee

        // Check for duplicate email
        $check = $conn->prepare("SELECT user_id FROM `User` WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Error: This email is already registered!'); window.history.back();</script>";
            exit();
        }
        $check->close();

        // Hash password
        $hashed_pwd = password_hash($raw_password, PASSWORD_DEFAULT);

        // Force 'Employee' role
        $role = 'Employee';

        // Save to DB
        $stmt = $conn->prepare("INSERT INTO `User` (full_name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $email, $hashed_pwd, $role);

        if ($stmt->execute()) {
            header("Location: ../manager_employee_table.php?msg=created");
        } else {
            echo "Database Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Update existing

        if (!empty($raw_password)) {
            // Update with new password
            $hashed_pwd = password_hash($raw_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE `User` SET full_name=?, email=?, password=? WHERE user_id=? AND role='Employee'");
            $stmt->bind_param("sssi", $name, $email, $hashed_pwd, $id);
        } else {
            // Update info only
            $stmt = $conn->prepare("UPDATE `User` SET full_name=?, email=? WHERE user_id=? AND role='Employee'");
            $stmt->bind_param("ssi", $name, $email, $id);
        }

        if ($stmt->execute()) {
            header("Location: ../manager_employee_table.php?msg=updated");
        } else {
            echo "Database Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Self-deletion check
    if ($id != $_SESSION['user_id']) {
        // Only delete Employees
        $stmt = $conn->prepare("DELETE FROM `User` WHERE user_id=? AND role='Employee'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: ../manager_employee_table.php?msg=deleted");
}

$conn->close();

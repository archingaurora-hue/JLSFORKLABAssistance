<?php
session_start();
require 'db_conn.php'; // Uses your central database connection

// 1. SECURITY: Only Managers can access this file
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    die("Access Denied: You do not have permission to perform this action.");
}

// --- SAVE (ADD or UPDATE) ---
if (isset($_POST['save_employee'])) {
    $id = $_POST['user_id']; // Hidden ID field (Empty = New, Value = Edit)
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $raw_password = $_POST['password'];

    if (empty($id)) {
        // ====================================================
        // CREATE NEW EMPLOYEE LOGIC
        // ====================================================

        // Step A: Check if email already exists
        $check = $conn->prepare("SELECT user_id FROM `User` WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Error: This email is already registered!'); window.history.back();</script>";
            exit();
        }
        $check->close();

        // Step B: Secure the Data
        $hashed_pwd = password_hash($raw_password, PASSWORD_DEFAULT);

        // !!! CRITICAL !!! 
        // We HARDCODE the role here. The form cannot override this.
        $role = 'Employee';

        // Step C: Insert into User Table
        $stmt = $conn->prepare("INSERT INTO `User` (full_name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $email, $hashed_pwd, $role);

        if ($stmt->execute()) {
            header("Location: ../manager_employee_table.php?msg=created");
        } else {
            echo "Database Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // ====================================================
        // UPDATE EXISTING EMPLOYEE LOGIC
        // ====================================================

        if (!empty($raw_password)) {
            // Update Name, Email AND Password
            $hashed_pwd = password_hash($raw_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE `User` SET full_name=?, email=?, password=? WHERE user_id=? AND role='Employee'");
            $stmt->bind_param("sssi", $name, $email, $hashed_pwd, $id);
        } else {
            // Update Name and Email ONLY
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

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Prevent Manager from deleting themselves accidentally
    if ($id != $_SESSION['user_id']) {
        // Enforce role='Employee' in WHERE clause so they can't delete other Managers
        $stmt = $conn->prepare("DELETE FROM `User` WHERE user_id=? AND role='Employee'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: ../manager_employee_table.php?msg=deleted");
}

$conn->close();

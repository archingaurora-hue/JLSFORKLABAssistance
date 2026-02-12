<?php
session_start();
require 'db_conn.php';

// Auth check
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    // Redirect unauthorized
    header("Location: ../employee_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $load_id = intval($_POST['load_id']);
    $new_status = $_POST['new_status'];
    $employee_name = $_SESSION['full_name']; // Get current user

    // Validate
    if (empty($load_id) || empty($new_status)) {
        echo "<script>alert('Error: Missing ID or Status'); window.history.back();</script>";
        exit();
    }

    // Update status
    $stmt = $conn->prepare("UPDATE `Process_Load` SET status = ? WHERE load_id = ?");
    $stmt->bind_param("si", $new_status, $load_id);

    if ($stmt->execute()) {

        // Log change
        $logStmt = $conn->prepare("INSERT INTO `System_Log` (load_id, status_event, employee_name, timestamp) VALUES (?, ?, ?, NOW())");
        $logStmt->bind_param("iss", $load_id, $new_status, $employee_name);

        if (!$logStmt->execute()) {
            // Continue on log error
            error_log("Log Insert Error: " . $logStmt->error);
        }
        $logStmt->close();

        // Redirect success
        header("Location: ../employee_dashboard.php?msg=updated");
        exit();
    } else {
        // Handle errors
        echo "<div style='padding:20px; font-family:sans-serif;'>";
        echo "<h2>Error Updating Status</h2>";
        echo "<p><strong>Database says:</strong> " . htmlspecialchars($stmt->error) . "</p>";
        echo "<p><em>Tip: This usually happens if the 'status' value in the database ENUM definition doesn't match the value sent from the form.</em></p>";
        echo "<a href='../employee_dashboard.php'>Go Back</a>";
        echo "</div>";
    }
    $stmt->close();
}
$conn->close();

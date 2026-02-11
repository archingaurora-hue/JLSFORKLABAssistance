<?php
session_start();
require 'db_conn.php';

// 1. Auth Check: Only Employees/Managers allowed
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    // Redirect to the correct employee login, NOT login.php
    header("Location: ../employee_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $load_id = intval($_POST['load_id']);
    $new_status = $_POST['new_status'];
    $employee_name = $_SESSION['full_name']; // Ensure this session variable is set

    // 2. Validate inputs
    if (empty($load_id) || empty($new_status)) {
        echo "<script>alert('Error: Missing ID or Status'); window.history.back();</script>";
        exit();
    }

    // 3. Update Process_Load
    $stmt = $conn->prepare("UPDATE `Process_Load` SET status = ? WHERE load_id = ?");
    $stmt->bind_param("si", $new_status, $load_id);

    if ($stmt->execute()) {

        // 4. Log the change in System_Log
        $logStmt = $conn->prepare("INSERT INTO `System_Log` (load_id, status_event, employee_name, timestamp) VALUES (?, ?, ?, NOW())");
        $logStmt->bind_param("iss", $load_id, $new_status, $employee_name);

        if (!$logStmt->execute()) {
            // If log fails, show error but don't stop flow
            error_log("Log Insert Error: " . $logStmt->error);
        }
        $logStmt->close();

        // 5. Success: Redirect to Employee Dashboard
        header("Location: ../employee_dashboard.php?msg=updated");
        exit();
    } else {
        // 6. DB Error Handling (Shows the exact SQL error)
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

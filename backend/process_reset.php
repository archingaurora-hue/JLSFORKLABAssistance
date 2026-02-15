<?php
// backend/process_reset.php
require 'db_conn.php';

if (isset($_POST['reset_password_btn'])) {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Basic validation
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    // 2. Validate Token and Expiry
    $token_hash = hash("sha256", $token);

    $sql = "SELECT * FROM `User` WHERE email = ? AND reset_token_hash = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['reset_token_expires_at']) <= time()) {
            // Token expired
            header("Location: ../customer_login.php?status=invalid_token");
            exit();
        }

        // 3. Update Password and Clear Token
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE `User` SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE user_id = ?");
        $update->bind_param("si", $hashed_password, $row['user_id']);

        if ($update->execute()) {
            header("Location: ../customer_login.php?status=password_updated");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        // Token mismatch or email not found
        header("Location: ../customer_login.php?status=invalid_token");
    }
}

<?php
// backend/process_reset.php
require 'db_conn.php';

if (isset($_POST['reset_password_btn'])) {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. CONFIRM PASSWORDS MATCH
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    // 2. VERIFY TOKEN
    // We must hash the incoming token to see if it matches the one stored in the DB.
    $token_hash = hash("sha256", $token);

    // Select user where Email matches AND Token matches
    $sql = "SELECT * FROM `User` WHERE email = ? AND reset_token_hash = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        // 3. CHECK EXPIRY
        // If the token expiry time is in the past, it's invalid.
        if (strtotime($row['reset_token_expires_at']) <= time()) {
            header("Location: ../customer_login.php?status=invalid_token");
            exit();
        }

        // 4. UPDATE PASSWORD & CLEAR TOKEN
        // Hash the new password before storing.
        // Set token fields to NULL so the link cannot be reused.
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE `User` SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE user_id = ?");
        $update->bind_param("si", $hashed_password, $row['user_id']);

        if ($update->execute()) {
            // Success! Redirect to login page
            header("Location: ../customer_login.php?status=password_updated");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        // Token mismatch or email mismatch
        header("Location: ../customer_login.php?status=invalid_token");
    }
} else {
    // If accessed directly without POST
    header("Location: ../customer_login.php");
}

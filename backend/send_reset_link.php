<?php
// backend/send_reset_link.php
require 'db_conn.php';

if (isset($_POST['send_link'])) {
    $email = $_POST['email'];

    // 1. Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM `User` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 2. Generate Token and Hash it
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);

        // 3. Set Expiry 
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

        // 4. Update Database
        $update = $conn->prepare("UPDATE `User` SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?");
        $update->bind_param("sss", $token_hash, $expiry, $email);
        $update->execute();

        // 5. Send Email
        $resetLink = "http://localhost/LABAssistance/reset_password.php?token=$token&email=$email";

        echo "<div style='font-family: sans-serif; padding: 20px; text-align: center;'>";
        echo "<h1>Testing Mode</h1>";
        echo "<p>On a real server, this would be an email. Since you are on localhost, click below:</p>";
        echo "<a href='$resetLink' style='background: blue; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset My Password</a>";
        echo "<br><br><small>Link: $resetLink</small>";
        echo "</div>";
        exit();
    }

    // If email not found or skipped
    header("Location: ../customer_login.php?status=link_sent");
    exit();
}

<?php
// backend/send_reset_link.php

// -------------------------------------------------------------------------
// 1. LOAD PHPMAILER
// We load the library classes manually since we are not using Composer.
// Ensure the 'PHPMailer' folder is inside the 'backend' directory.
// -------------------------------------------------------------------------
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

require 'db_conn.php'; // Include database connection

if (isset($_POST['send_link'])) {
    $email = $_POST['email'];

    // -------------------------------------------------------------------------
    // 2. CHECK IF USER EXISTS
    // We only send an email if the address is found in our 'User' table.
    // -------------------------------------------------------------------------
    $stmt = $conn->prepare("SELECT user_id, full_name FROM `User` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        // -------------------------------------------------------------------------
        // 3. GENERATE SECURE TOKEN
        // We create a random 32-character hex string.
        // We also hash it before storing in the DB for extra security.
        // -------------------------------------------------------------------------
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);

        // Set Expiry: Current time + 30 minutes
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

        // -------------------------------------------------------------------------
        // 4. SAVE TOKEN TO DATABASE
        // Update the user record with the token hash and expiry time.
        // -------------------------------------------------------------------------
        $update = $conn->prepare("UPDATE `User` SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?");
        $update->bind_param("sss", $token_hash, $expiry, $email);
        $update->execute();

        // -------------------------------------------------------------------------
        // 5. SEND EMAIL VIA PHPMAILER (GMAIL CONFIGURATION)
        // -------------------------------------------------------------------------
        $mail = new PHPMailer(true);

        try {
            // Server Settings for Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sevillaralph1504@gmail.com';     // Replace with business email after hostinger
            $mail->Password   = 'dbzh zbkt fgie slfn';     // Replace with business email's app password after hostinger
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Gmail requires TLS
            $mail->Port       = 587;

            // Sender & Recipient
            $mail->setFrom('sevillaralph1504@gmail.com', 'LABAssistance Support');
            $mail->addAddress($email);

            // Email Content
            // The link points to 'reset_password.php' in your MAIN folder.
            // Note: We send the raw $token (not the hash) in the link.
            $resetLink = "http://localhost/LABAssistance/reset_password.php?token=$token&email=$email";

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password - LABAssistance';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 600px;'>
                    <h2 style='color: #333;'>Password Reset Request</h2>
                    <p>Hello " . htmlspecialchars($row['full_name']) . ",</p>
                    <p>We received a request to reset your password. Click the button below to create a new one:</p>
                    <p style='text-align: center;'>
                        <a href='$resetLink' style='background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
                    </p>
                    <p><small>This link expires in 30 minutes.</small></p>
                </div>
            ";

            $mail->send();

            // Redirect to Login Page with 'Success' flag
            header("Location: ../customer_login.php?status=link_sent");
        } catch (Exception $e) {
            // For debugging purposes only. In production, log this instead of showing user.
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            exit();
        }
    } else {
        // If email not found, we still redirect to 'success' to prevent attackers from checking which emails exist.
        header("Location: ../customer_login.php?status=link_sent");
    }
    exit();
}

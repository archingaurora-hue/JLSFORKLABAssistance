<?php
// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include required files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'db_conn.php';

// Check if the form was submitted
if (isset($_POST['send_link'])) {
    $email = $_POST['email'];

    // Check if the user email exists in the database
    $stmt = $conn->prepare("SELECT user_id, full_name FROM `User` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user exists, proceed with token generation
    if ($row = $result->fetch_assoc()) {

        // Generate a secure token and hash it
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);

        // Set token expiration to 30 minutes from now
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

        // Save the hashed token and expiry in the database
        $update = $conn->prepare("UPDATE `User` SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?");
        $update->bind_param("sss", $token_hash, $expiry, $email);
        $update->execute();

        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Gmail SMTP settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sevillaralph1504@gmail.com';
            $mail->Password   = 'wagc ultm nqrk hnfp'; // Use environment variables for production
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender and recipient details
            $mail->setFrom('sevillaralph1504@gmail.com', 'LABAssistance Support');
            $mail->addAddress($email);

            // Create the password reset link
            $resetLink = "http://localhost/LABAssistance/reset_password.php?token=$token&email=$email";

            // Set email subject and HTML body
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

            // Send the email
            $mail->send();

            // Redirect to login page on success
            header("Location: ../customer_login.php?status=link_sent");
        } catch (Exception $e) {
            // Display error if email fails to send (for debugging only)
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            exit();
        }
    } else {
        // Redirect even if email isn't found to prevent email scraping
        header("Location: ../customer_login.php?status=link_sent");
    }
    exit();
}

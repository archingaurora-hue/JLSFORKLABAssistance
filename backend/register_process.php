<?php
// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include required files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'db_conn.php';

// SweetAlert and redirect helper
function sweetAlertRedirect($icon, $title, $text, $redirect_url)
{
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { font-family: sans-serif; background-color: #f4f4f4; }</style>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: '{$icon}',
                title: '{$title}',
                text: '{$text}',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{$redirect_url}';
                }
            });
        </script>
    </body>
    </html>";
    exit();
}

// Handle registration request
if (isset($_POST['register'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        sweetAlertRedirect('error', 'Error!', 'Passwords do not match!', '../register.php');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'Customer';
    $verification_token = bin2hex(random_bytes(16));

    // Check if email exists
    $checkEmail = $conn->prepare("SELECT email FROM `User` WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        sweetAlertRedirect('warning', 'Oops...', 'Email already exists!', '../register.php');
    } else {
        $sql = "INSERT INTO `User` (email, password, role, first_name, last_name, created_at, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, NOW(), ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $email, $hashed_password, $role, $first_name, $last_name, $verification_token);

        if ($stmt->execute()) {

            // Initialize PHPMailer for Verification Email
            $mail = new PHPMailer(true);

            try {
                // Gmail SMTP settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sevillaralph1504@gmail.com'; // Adjust for production
                $mail->Password   = 'wagc ultm nqrk hnfp';        // Adjust for production
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Sender and recipient details
                $mail->setFrom('sevillaralph1504@gmail.com', 'LABAssistance Support');
                $mail->addAddress($email);

                // Create the verification link
                $verifyLink = "http://localhost/LABAssistance/backend/verify_account.php?token=$verification_token&email=$email";

                // Set email subject and HTML body
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Account - LABAssistance';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 600px;'>
                        <h2 style='color: #333;'>Account Verification</h2>
                        <p>Hello " . htmlspecialchars($first_name) . ",</p>
                        <p>Thank you for registering. Please click the button below to verify your email address and activate your account:</p>
                        <p style='text-align: center;'>
                            <a href='$verifyLink' style='background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify Account</a>
                        </p>
                        <p><small>If you did not register for this account, please ignore this email.</small></p>
                    </div>
                ";

                $mail->send();
                sweetAlertRedirect('success', 'Registration Successful!', 'Please check your email to verify your account before logging in.', '../customer_login.php');
            } catch (Exception $e) {
                // Remove the user if email failed to send, or just show an error.
                sweetAlertRedirect('error', 'Mail Error', 'Account created but failed to send verification email: ' . $mail->ErrorInfo, '../customer_login.php');
            }
        } else {
            $errorMsg = addslashes($stmt->error);
            sweetAlertRedirect('error', 'Database Error', $errorMsg, '../register.php');
        }
        $stmt->close();
    }
    $checkEmail->close();
}
$conn->close();

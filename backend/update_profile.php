<?php
session_start();
require 'db_conn.php';

// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Determine the correct dashboard redirect based on role
$dashboard_redirect = '../dashboard.php';
if ($role === 'Manager') {
    $dashboard_redirect = '../manager_dashboard.php';
} elseif ($role === 'Employee') {
    $dashboard_redirect = '../employee_dashboard.php';
}

if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];

    // Fetch user details to verify current password and get email
    $stmt = $conn->prepare("SELECT email, first_name, password FROM `User` WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 1. Verify Current Password
        if (!password_verify($current_password, $user['password'])) {
            sweetAlertRedirect('error', 'Authentication Failed', 'The current password you entered is incorrect.', $dashboard_redirect);
        }

        $user_email = $user['email'];
        $first_name = $user['first_name'];

        // 2. Generate a secure token
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // Valid for 30 minutes

        // 3. Save the hashed token and expiry in the database
        $update = $conn->prepare("UPDATE `User` SET reset_token_hash = ?, reset_token_expires_at = ? WHERE user_id = ?");
        $update->bind_param("ssi", $token_hash, $expiry, $user_id);

        if ($update->execute()) {
            // 4. Initialize PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sevillaralph1504@gmail.com'; // Update for production
                $mail->Password   = 'wagc ultm nqrk hnfp';        // Update for production
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('sevillaralph1504@gmail.com', 'LABAssistance Support');
                $mail->addAddress($user_email);

                // Create the password reset link linking to the existing form
                $resetLink = "http://localhost/LABAssistance/reset_password.php?token=$token&email=$user_email";

                $mail->isHTML(true);
                $mail->Subject = 'Verify Password Change - LABAssistance';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 600px;'>
                        <h2 style='color: #333;'>Password Change Request</h2>
                        <p>Hello " . htmlspecialchars($first_name) . ",</p>
                        <p>You recently requested to change your password from your dashboard. Please click the button below to verify this request and set your new password:</p>
                        <p style='text-align: center;'>
                            <a href='$resetLink' style='background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Update Password</a>
                        </p>
                        <p><small>If you did not initiate this request, please ignore this email. This link will expire in 30 minutes.</small></p>
                    </div>
                ";

                $mail->send();

                sweetAlertRedirect('success', 'Verification Required', 'A link to securely update your password has been sent to your email.', $dashboard_redirect);
            } catch (Exception $e) {
                sweetAlertRedirect('error', 'Mail Error', 'Failed to send the verification email. Please try again later.', $dashboard_redirect);
            }
        } else {
            sweetAlertRedirect('error', 'Database Error', 'An error occurred while generating your request.', $dashboard_redirect);
        }
        $update->close();
    }
    $stmt->close();
}
$conn->close();

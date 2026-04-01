<?php
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

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    // Check if the token matches the user
    $stmt = $conn->prepare("SELECT user_id FROM `User` WHERE email = ? AND verification_token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Update user to verified and clear the token
        $update = $conn->prepare("UPDATE `User` SET is_verified = 1, verification_token = NULL WHERE email = ?");
        $update->bind_param("s", $email);

        if ($update->execute()) {
            sweetAlertRedirect('success', 'Verified!', 'Your account has been successfully verified. You can now log in.', '../customer_login.php');
        } else {
            sweetAlertRedirect('error', 'Error', 'Something went wrong while verifying your account.', '../customer_login.php');
        }
        $update->close();
    } else {
        sweetAlertRedirect('error', 'Invalid Link', 'This verification link is invalid or has already been used.', '../customer_login.php');
    }
    $stmt->close();
} else {
    // Redirect to login if accessed without parameters
    header("Location: ../customer_login.php");
    exit();
}

$conn->close();

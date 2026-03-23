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

    // Check if email exists
    $checkEmail = $conn->prepare("SELECT email FROM `User` WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        sweetAlertRedirect('warning', 'Oops...', 'Email already exists!', '../register.php');
    } else {
        $sql = "INSERT INTO `User` (email, password, role, first_name, last_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $email, $hashed_password, $role, $first_name, $last_name);

        if ($stmt->execute()) {
            sweetAlertRedirect('success', 'Success!', 'Registration Successful! Please login.', '../customer_login.php');
        } else {
            $errorMsg = addslashes($stmt->error);
            sweetAlertRedirect('error', 'Database Error', $errorMsg, '../register.php');
        }
        $stmt->close();
    }
    $checkEmail->close();
}
$conn->close();

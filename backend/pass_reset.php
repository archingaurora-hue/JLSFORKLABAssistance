<?php
$conn = new mysqli("localhost", "root", "", "laundry_db");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $new_pwd = $_POST['new_password'];
    $confirm_pwd = $_POST['confirm_password'];

    if ($new_pwd !== $confirm_pwd) {
        // Redirect with an error flag
        header("Location: ../customer_login.php?status=mismatch");
        exit();
    }

    $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE `User` SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_pwd, $email);

    if ($stmt->execute()) {
        // Redirect with a success flag
        header("Location: ../customer_login.php?status=resetsuccess");
    } else {
        header("Location: ../login.php?status=error");
    }
    $stmt->close();
}
$conn->close();

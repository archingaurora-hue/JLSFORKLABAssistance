<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: employee_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/design.css">
</head>

<body class="bg-light">

    <div class="container d-flex flex-column align-items-center py-5" style="min-height: 100vh;">

        <div class="text-center mb-5">
            <h1 class="fw-bold">LABAssistance</h1>
            <p class="text-muted">Laundry Management System</p>
            <h2 class="mt-3">Manager Dashboard</h2>
        </div>

        <div class="text-center mb-5">
            <h3 class="fw-bold">SHOP STATUS</h3>
            <h1 class="fw-bold text-success display-3">OPEN</h1>
        </div>

        <div class="d-grid gap-3 w-100" style="max-width: 400px;">
            <button class="btn btn-dark py-3 fw-bold text-uppercase">Open/Close Shop</button>
            <button class="btn btn-dark py-3 fw-bold text-uppercase">View Orders</button>
            <a href="manager_employee_table.php" class="btn btn-dark py-3 fw-bold text-uppercase">View Employee Table</a>
        </div>

        <div class="mt-5">
            <a href="employee_login.php" class="btn btn-outline-danger">Log Out</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
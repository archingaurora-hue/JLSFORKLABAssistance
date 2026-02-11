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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manager Dashboard - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <span class="navbar-brand fw-bold fs-5">Manager Panel</span>
            <a href="employee_login.php" class="btn btn-sm btn-danger rounded-pill px-3">Exit</a>
        </div>
    </nav>

    <div class="container page-container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="text-center mb-5">
                    <h1 class="fw-bold">LABAssistance</h1>
                    <p class="text-muted mb-1">Laundry Management System</p>
                    <h3 class="fw-bold text-uppercase small text-muted ls-1">Manager Dashboard</h3>
                </div>

                <div class="app-card p-4 text-center mb-4">
                    <h6 class="text-muted fw-bold text-uppercase small mb-2">Current Shop Status</h6>
                    <h1 class="fw-bold text-success display-3 mb-0">OPEN</h1>
                </div>

                <div class="d-grid gap-3">
                    <button class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-power me-2"></i>Open/Close Shop
                    </button>

                    <button class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-receipt me-2"></i>View Orders
                    </button>

                    <a href="manager_employee_table.php" class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-people-fill me-2"></i>View Employee Table
                    </a>
                </div>

                <div class="text-center mt-5">
                    <p class="text-muted small">Logged in as: <strong>Manager</strong></p>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
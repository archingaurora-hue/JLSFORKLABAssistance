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

    <nav class="navbar navbar-light bg-dark shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold text-white">LAB<span class="text-primary">Assistance</span></span>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-sm-inline">Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="employee_login.php" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container page-container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="app-card p-4 text-center mb-4">
                    <h6 class="text-muted fw-bold text-uppercase small mb-2">Current Shop Status</h6>
                    <h1 class="fw-bold text-success display-3 mb-0">OPEN</h1>
                </div>

                <div class="d-grid gap-3">
                    <button class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-power me-2"></i>Open/Close Shop
                    </button>

                    <a href="employee_dashboard.php" class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-receipt me-2"></i>View Orders
                    </a>

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
<?php
session_start();
require 'backend/db_conn.php';

// Security Check: Only Managers can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: staff_login.php");
    exit();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // 1. Handle OPEN/CLOSE Actions
    if ($action === 'close_shop') {
        $stmt = $conn->prepare("UPDATE Shop_Status SET is_shop_open = 0 WHERE status_id = 1");
        $stmt->execute();
        $_SESSION['msg'] = "Shop is now CLOSED.";
        $_SESSION['msg_type'] = "danger";
    } elseif ($action === 'open_shop') {
        $stmt = $conn->prepare("UPDATE Shop_Status SET is_shop_open = 1 WHERE status_id = 1");
        $stmt->execute();
        $_SESSION['msg'] = "Shop is now OPEN.";
        $_SESSION['msg_type'] = "success";
    }
    // 2. Handle Updating Time Settings
    elseif ($action === 'update_times') {
        $closing_time = $_POST['closing_time'];
        $next_open_date = $_POST['next_open_date'];
        $next_open_time = $_POST['next_open_time'];
        $def_open = $_POST['default_open'];
        $def_close = $_POST['default_close'];

        // Combine date and time for DATETIME field
        $next_manual_datetime = $next_open_date . ' ' . $next_open_time;

        $stmt = $conn->prepare("UPDATE Shop_Status SET 
            current_closing_time = ?, 
            next_manual_open_time = ?, 
            default_open_time = ?, 
            default_close_time = ? 
            WHERE status_id = 1");
        $stmt->bind_param("ssss", $closing_time, $next_manual_datetime, $def_open, $def_close);
        $stmt->execute();

        $_SESSION['msg'] = "Timings updated successfully!";
        $_SESSION['msg_type'] = "success";
    }

    // Refresh to show changes and prevent form resubmission
    header("Location: shop_status.php");
    exit();
}

// Fetch Current Status
$result = $conn->query("SELECT * FROM Shop_Status WHERE status_id = 1");
$shop = $result->fetch_assoc();

// Format data for inputs
$isOpen = $shop['is_shop_open'];
$nextOpenObj = new DateTime($shop['next_manual_open_time']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Shop Status - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-dark shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold text-white">LAB<span class="text-primary">Assistance</span></span>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-sm-inline">Hi, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Manager'); ?></span>
                <a href="staff_login.php" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container page-container py-4 py-md-5">

        <div class="row justify-content-center mb-3">
            <div class="col-12 col-md-8 col-lg-6">
                <a href="manager_dashboard.php" class="text-decoration-none text-muted small fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">

                <?php if (isset($_SESSION['msg'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show small py-2" role="alert">
                        <?php
                        echo $_SESSION['msg'];
                        unset($_SESSION['msg']);
                        unset($_SESSION['msg_type']);
                        ?>
                        <button type="button" class="btn-close mt-1" style="padding: 0.5rem;" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="app-card bg-white p-4 p-md-5 rounded-3 shadow-sm text-center">

                    <h4 class="fw-bold text-uppercase mb-1">Shop Status</h4>

                    <?php if ($isOpen): ?>
                        <h1 class="fw-bold text-success display-2 mb-1">OPEN</h1>
                    <?php else: ?>
                        <h1 class="fw-bold text-danger display-2 mb-1">CLOSED</h1>
                    <?php endif; ?>

                    <p class="text-muted mb-4 fs-5"><?php echo date("F j, Y"); ?></p>

                    <form method="POST" action="shop_status.php">
                        <input type="hidden" name="action" value="update_times">

                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold text-dark">Set Today's Closing Time</label>
                            <input type="time" name="closing_time" class="form-control form-control-lg fs-6"
                                value="<?php echo $shop['current_closing_time']; ?>">
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold text-dark">Set Next Opening Time</label>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <input type="time" name="next_open_time" class="form-control form-control-lg fs-6"
                                        value="<?php echo $nextOpenObj->format('H:i'); ?>">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <input type="date" name="next_open_date" class="form-control form-control-lg fs-6"
                                        value="<?php echo $nextOpenObj->format('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 text-start">
                            <label class="form-label small fw-bold text-dark">Default Store Hours</label>
                            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                <input type="time" name="default_open" class="form-control form-control-lg fs-6"
                                    value="<?php echo $shop['default_open_time']; ?>">
                                <span class="small text-muted text-center d-none d-sm-block">to</span>
                                <input type="time" name="default_close" class="form-control form-control-lg fs-6"
                                    value="<?php echo $shop['default_close_time']; ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-outline-secondary fw-bold w-100 mb-4 py-2">SAVE SHOP HOURS</button>
                    </form>

                    <hr class="mb-4 bg-secondary opacity-25">

                    <form id="closeShopForm" method="POST" action="shop_status.php" style="display: none;">
                        <input type="hidden" name="action" value="close_shop">
                    </form>

                    <form id="openShopForm" method="POST" action="shop_status.php" style="display: none;">
                        <input type="hidden" name="action" value="open_shop">
                    </form>

                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-dark w-100 py-3 fw-bold text-uppercase"
                            onclick="confirmStatusChange('close')"
                            <?php echo (!$isOpen) ? 'disabled style="opacity: 0.4;"' : ''; ?>>
                            Close Now
                        </button>

                        <button type="button" class="btn btn-success w-100 py-3 fw-bold text-uppercase"
                            onclick="confirmStatusChange('open')"
                            <?php echo ($isOpen) ? 'disabled style="opacity: 0.4;"' : ''; ?>>
                            Open Now
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Trigger SweetAlert confirmation before submitting forms
        function confirmStatusChange(action) {
            let titleText = '';
            let confirmText = '';
            let confirmColor = '';
            let formId = '';

            if (action === 'close') {
                titleText = "Close the Shop?";
                confirmText = "Yes, Close Now";
                confirmColor = "#212529"; // Match btn-dark
                formId = "closeShopForm";
            } else {
                titleText = "Open the Shop?";
                confirmText = "Yes, Open Now";
                confirmColor = "#198754"; // Match btn-success
                formId = "openShopForm";
            }

            Swal.fire({
                title: titleText,
                text: "Are you sure you want to change the shop's operational status?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
</body>

</html>
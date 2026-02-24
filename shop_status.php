<?php
session_start();
require 'backend/db_conn.php';

date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: staff_login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'close_shop') {
        $q = $conn->query("SELECT default_open_time FROM Shop_Status WHERE status_id = 1");
        $d = $q->fetch_assoc();

        $tomorrow_open = date('Y-m-d', strtotime('+1 day')) . ' ' . $d['default_open_time'];
        $time_now = date('H:i:s');

        $stmt = $conn->prepare("UPDATE Shop_Status SET is_shop_open = 0, next_manual_open_time = ?, current_closing_time = ? WHERE status_id = 1");
        $stmt->bind_param("ss", $tomorrow_open, $time_now);
        $stmt->execute();

        $_SESSION['msg'] = "Shop closed early at " . date('H:i') . ". It will auto-reopen tomorrow.";
        $_SESSION['msg_type'] = "danger";
    } elseif ($action === 'open_shop') {
        $conn->query("UPDATE Shop_Status SET is_shop_open = 1, next_manual_open_time = NULL, current_closing_time = NULL WHERE status_id = 1");
        $_SESSION['msg'] = "Shop opened. Normal schedule resumed.";
        $_SESSION['msg_type'] = "success";
    } elseif ($action === 'update_times') {
        $def_open = $_POST['default_open'];
        $def_close = $_POST['default_close'];

        if (isset($_POST['override_active'])) {
            $closing_time = $_POST['closing_time'];
            $next_manual_datetime = $_POST['next_open_date'] . ' ' . $_POST['next_open_time'];
        } else {
            $closing_time = null;
            $next_manual_datetime = null;
        }

        $stmt = $conn->prepare("UPDATE Shop_Status SET current_closing_time = ?, next_manual_open_time = ?, default_open_time = ?, default_close_time = ? WHERE status_id = 1");
        $stmt->bind_param("ssss", $closing_time, $next_manual_datetime, $def_open, $def_close);
        $stmt->execute();

        $_SESSION['msg'] = "Schedule settings updated!";
        $_SESSION['msg_type'] = "success";
    }

    header("Location: shop_status.php");
    exit();
}

// Fetch data & auto-evaluate status
$result = $conn->query("SELECT * FROM Shop_Status WHERE status_id = 1");
$shop = $result->fetch_assoc();

$now = new DateTime();
$currentTime = $now->format('H:i:s');
$currentDateTime = $now->format('Y-m-d H:i:s');

$expected_status = 0;

if (!empty($shop['next_manual_open_time']) && $shop['next_manual_open_time'] > $currentDateTime) {
    $expected_status = 0;
} else {
    $close_time = !empty($shop['current_closing_time']) ? $shop['current_closing_time'] : $shop['default_close_time'];
    if ($currentTime >= $shop['default_open_time'] && $currentTime <= $close_time) {
        $expected_status = 1;
    }
}

if ($shop['is_shop_open'] != $expected_status) {
    $conn->query("UPDATE Shop_Status SET is_shop_open = $expected_status WHERE status_id = 1");
    $shop['is_shop_open'] = $expected_status;
}

// Format data for HTML
$isOpen = $shop['is_shop_open'];
$has_override = !empty($shop['next_manual_open_time']);

$nextOpenObj = $has_override ? new DateTime($shop['next_manual_open_time']) : new DateTime();

$effective_close = !empty($shop['current_closing_time']) ? $shop['current_closing_time'] : $shop['default_close_time'];

// Convert all displayed times to 24-hour (00:00) format
$formatted_close = date("H:i", strtotime($effective_close));
$formatted_open = $has_override ? $nextOpenObj->format("F j, Y, H:i") : date("H:i", strtotime($shop['default_open_time']));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show small py-2">
                        <?php
                        echo $_SESSION['msg'];
                        unset($_SESSION['msg']);
                        unset($_SESSION['msg_type']);
                        ?>
                        <button type="button" class="btn-close mt-1" style="padding: 0.5rem;" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="app-card bg-white p-4 p-md-5 rounded-3 shadow-sm text-center">

                    <h4 class="fw-bold text-uppercase mb-1">Live Status</h4>

                    <?php if ($isOpen): ?>
                        <h1 class="fw-bold text-success display-2 mb-1">OPEN</h1>
                        <p class="text-muted fw-bold mb-4 fs-6">
                            <i class="bi bi-clock me-1"></i> Closes at <?php echo $formatted_close; ?>
                        </p>
                    <?php else: ?>
                        <h1 class="fw-bold text-danger display-2 mb-1">CLOSED</h1>
                        <p class="text-muted fw-bold mb-4 fs-6">
                            <i class="bi bi-clock me-1"></i> Opens on <?php echo $formatted_open; ?>
                        </p>
                    <?php endif; ?>

                    <div class="d-flex gap-2 mb-4">
                        <form id="closeShopForm" method="POST" action="shop_status.php" class="w-50">
                            <input type="hidden" name="action" value="close_shop">
                            <button type="button" class="btn btn-dark w-100 py-3 fw-bold text-uppercase"
                                onclick="confirmStatusChange('close')" <?php echo (!$isOpen) ? 'disabled style="opacity: 0.4;"' : ''; ?>>
                                Close Now
                            </button>
                        </form>

                        <form id="openShopForm" method="POST" action="shop_status.php" class="w-50">
                            <input type="hidden" name="action" value="open_shop">
                            <button type="button" class="btn btn-success w-100 py-3 fw-bold text-uppercase"
                                onclick="confirmStatusChange('open')" <?php echo ($isOpen) ? 'disabled style="opacity: 0.4;"' : ''; ?>>
                                Open Now
                            </button>
                        </form>
                    </div>

                    <hr class="mb-4 bg-secondary opacity-25">

                    <form method="POST" action="shop_status.php">
                        <input type="hidden" name="action" value="update_times">

                        <div class="bg-light p-3 rounded-3 mb-4 text-start border">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-clock-history me-2"></i>Standard Store Hours</h6>
                            <p class="small text-muted mb-3">Your regular operating schedule.</p>

                            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                <input type="time" name="default_open" class="form-control" value="<?php echo date('H:i', strtotime($shop['default_open_time'])); ?>" required>
                                <span class="small text-muted text-center d-none d-sm-block">to</span>
                                <input type="time" name="default_close" class="form-control" value="<?php echo date('H:i', strtotime($shop['default_close_time'])); ?>" required>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-4 text-start border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-event me-2"></i>Temporary Overrides</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="overrideToggle" name="override_active" value="1" <?php echo $has_override ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">Toggle ON to schedule a special closing time or an exact opening date.</p>

                            <div id="overrideFields" style="<?php echo $has_override ? '' : 'opacity: 0.5; pointer-events: none;'; ?>">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Today's Closing Time:</label>
                                    <input type="time" name="closing_time" id="overrideClose" class="form-control" value="<?php echo !empty($shop['current_closing_time']) ? date('H:i', strtotime($shop['current_closing_time'])) : ''; ?>">
                                </div>

                                <div>
                                    <label class="form-label small fw-bold text-dark">Next Scheduled Opening:</label>
                                    <div class="row g-2">
                                        <div class="col-12 col-sm-6">
                                            <input type="date" name="next_open_date" id="overrideDate" class="form-control"
                                                min="<?php echo date('Y-m-d'); ?>"
                                                value="<?php echo $has_override ? $nextOpenObj->format('Y-m-d') : ''; ?>">
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <input type="time" name="next_open_time" id="overrideOpen" class="form-control"
                                                value="<?php echo $has_override ? $nextOpenObj->format('H:i') : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">SAVE ALL SETTINGS</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const overrideToggle = document.getElementById('overrideToggle');
        const overrideFields = document.getElementById('overrideFields');
        const inputs = overrideFields.querySelectorAll('input');

        overrideToggle.addEventListener('change', function() {
            if (this.checked) {
                overrideFields.style.opacity = '1';
                overrideFields.style.pointerEvents = 'auto';
                inputs.forEach(input => input.required = true);
            } else {
                overrideFields.style.opacity = '0.5';
                overrideFields.style.pointerEvents = 'none';
                inputs.forEach(input => {
                    input.required = false;
                    input.value = '';
                });
            }
        });

        function confirmStatusChange(action) {
            let config = action === 'close' ? {
                title: "Close Shop?",
                color: "#212529",
                form: "closeShopForm"
            } : {
                title: "Open Shop?",
                color: "#198754",
                form: "openShopForm"
            };

            Swal.fire({
                title: config.title,
                text: "Are you sure you want to manually change the live status?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: config.color,
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, do it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(config.form).submit();
                }
            });
        }
    </script>
</body>

</html>
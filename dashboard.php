<?php
session_start();
require 'backend/db_conn.php';

// Check auth
if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Capture session alerts
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Check if shop is open or closed
$statusResult = $conn->query("SELECT is_shop_open FROM Shop_Status WHERE status_id = 1");
$shopData = $statusResult->fetch_assoc();
$isOpen = ($shopData && $shopData['is_shop_open'] == 1);

// Fetch user orders
$orderQuery = "SELECT * FROM `Order` WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
    <style>
        .tracking-card {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9fa;
        }

        .order-summary-card {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold">LAB<span class="text-primary">Assistance</span></span>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-sm-inline">Hi,
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>

                <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#profileModal" title="Profile Settings">
                    <i class="bi bi-person-gear"></i>
                </button>

                <a href="customer_login.php" class="btn btn-sm btn-outline-danger rounded-pill" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container page-container mt-4">

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="app-card p-4 text-center shadow-sm bg-white rounded-3">
                    <h5 class="fw-bold text-uppercase mb-0 text-dark" style="letter-spacing: 1px;">Shop Status</h5>
                    <?php if ($isOpen): ?>
                        <h1 class="display-2 mb-0" style="color: #198754; font-weight: 800;">OPEN</h1>
                    <?php else: ?>
                        <h1 class="display-2 mb-0" style="color: #dc3545; font-weight: 800;">CLOSED</h1>
                    <?php endif; ?>
                    <p class="text-muted mb-0 fs-5 mt-1">
                        <?php echo date("F j, Y"); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="app-card p-4 text-center bg-dark text-white rounded-3 shadow-sm">
                    <h4 class="fw-bold mb-1">Need Laundry Service?</h4>
                    <p class="text-white-50 small mb-3">We wash, dry and fold!</p>
                    <a href="order.php" class="btn btn-light text-dark fw-bold w-100 rounded-pill py-3 shadow-sm">
                        <i class="bi bi-plus-lg me-2"></i>Place New Order
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <h6 class="text-muted fw-bold text-uppercase small mb-3 ps-2">Recent Orders</h6>

                <?php if ($ordersResult->num_rows > 0): ?>
                    <?php while ($order = $ordersResult->fetch_assoc()): ?>

                        <div class="app-card mb-3 p-3 bg-white rounded-3 shadow-sm border" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modal<?php echo $order['order_id']; ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border">#<?php echo $order['order_id']; ?></span>
                                    <span class="small fw-bold text-primary"><?php echo $order['status']; ?></span>
                                </div>
                                <h5 class="fw-bold mb-0 text-dark">₱<?php echo number_format($order['final_price'], 2); ?></h5>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-basket-fill me-1"></i> <?php echo $order['bag_counts']; ?> bags • <?php echo $order['services_requested']; ?>
                            </div>
                        </div>

                        <?php
                        // Check loads outside modal body to determine if order is cancellable
                        $loadQuery = "SELECT * FROM `Process_Load` WHERE order_id = '" . $order['order_id'] . "'";
                        $loadsResult = $conn->query($loadQuery);

                        $loadArray = [];
                        $canCancel = ($order['status'] !== 'Cancelled' && $order['status'] !== 'Completed');

                        while ($load = $loadsResult->fetch_assoc()) {
                            $loadArray[] = $load;
                            if (!in_array($load['status'], ['Pending Dropoff', 'In Queue', 'Pending'])) {
                                $canCancel = false;
                            }
                        }
                        ?>

                        <div class="modal fade" id="modal<?php echo $order['order_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 rounded-4">
                                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                                        <h5 class="modal-title fw-bold">Order Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div>
                                                <span class="text-muted small d-block text-uppercase fw-bold">Tracking Number</span>
                                                <span class="fw-bold fs-5 text-dark"><?php echo $order['tracking_code']; ?></span>
                                            </div>
                                            <?php
                                            $masterStatusClass = $order['status'] == 'Completed' ? 'bg-success' : ($order['status'] == 'Cancelled' ? 'bg-danger' : 'bg-primary');
                                            ?>
                                            <span class="badge <?php echo $masterStatusClass; ?> fs-6 px-3 py-2 rounded-pill"><?php echo $order['status']; ?></span>
                                        </div>

                                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Track Order Progress</h6>
                                        <div class="mb-4">
                                            <?php if (count($loadArray) > 0): ?>
                                                <?php foreach ($loadArray as $load): ?>
                                                    <div class="tracking-card rounded-3 p-3 mb-2 shadow-sm">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="fw-bold text-dark">
                                                                <i class="bi bi-bag-check text-primary me-1"></i> <?php echo $load['bag_label']; ?>
                                                            </div>
                                                            <?php
                                                            $s = $load['status'];
                                                            $badgeClass = 'bg-secondary';
                                                            if ($s == 'In Queue') $badgeClass = 'bg-dark';
                                                            elseif (strpos($s, 'Washing') !== false) $badgeClass = 'bg-primary';
                                                            elseif (strpos($s, 'Drying') !== false) $badgeClass = 'bg-warning text-dark';
                                                            elseif ($s == 'Awaiting Pickup') $badgeClass = 'bg-success';
                                                            elseif ($s == 'Completed') $badgeClass = 'bg-success bg-opacity-75';
                                                            elseif ($s == 'Cancelled') $badgeClass = 'bg-danger';
                                                            ?>
                                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $s; ?></span>
                                                        </div>

                                                        <?php if (!empty($load['timer_end']) && $s !== 'Cancelled'): ?>
                                                            <div class="d-flex justify-content-between align-items-center bg-white border rounded px-2 py-1 mt-2">
                                                                <span class="small text-muted fw-bold"><i class="bi bi-stopwatch"></i> TIME LEFT</span>
                                                                <span class="fw-bold text-danger live-timer" data-end="<?php echo date('c', strtotime($load['timer_end'])); ?>">--:--</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="text-muted small text-center bg-light p-3 rounded">Bags are being processed.</div>
                                            <?php endif; ?>
                                        </div>

                                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Order Information</h6>
                                        <div class="order-summary-card rounded-3 p-3 shadow-sm mb-2">
                                            <div class="d-flex justify-content-between mb-2 small">
                                                <span class="text-muted">Services</span>
                                                <span class="fw-bold text-end"><?php echo $order['services_requested']; ?></span>
                                            </div>
                                            <?php if (!empty($order['supplies_requested'])): ?>
                                                <div class="d-flex justify-content-between mb-2 small">
                                                    <span class="text-muted">Add-ons</span>
                                                    <span class="fw-bold text-end"><?php echo $order['supplies_requested']; ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between mb-2 small">
                                                <span class="text-muted">Bags</span>
                                                <span class="fw-bold text-end"><?php echo $order['bag_counts']; ?></span>
                                            </div>
                                            <?php if (!empty($order['customer_note'])): ?>
                                                <div class="d-flex justify-content-between mb-2 small">
                                                    <span class="text-muted">Instructions</span>
                                                    <span class="fw-bold text-end text-truncate" style="max-width: 60%;" title="<?php echo htmlspecialchars($order['customer_note']); ?>">
                                                        <?php echo htmlspecialchars($order['customer_note']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <hr class="my-2 border-secondary opacity-25">

                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-dark">Total</span>
                                                <span class="fw-bold fs-5 text-primary">₱<?php echo number_format($order['final_price'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-0 px-4 pb-4 flex-column">
                                        <?php if ($canCancel): ?>
                                            <form action="backend/cancel_order.php" method="POST" class="w-100 mb-2">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger w-100 fw-bold border" onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                                                    Cancel Order
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-light w-100 fw-bold border" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <div class="mb-3">
                            <i class="bi bi-basket display-1 text-light bg-secondary p-4 rounded-circle bg-opacity-10"></i>
                        </div>
                        <h5 class="fw-bold">No orders yet</h5>
                        <p class="text-muted small">Your active and past orders will appear here.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <form method="POST" action="backend/update_profile.php" onsubmit="return validatePasswordMatch()">
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold">Update Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Leave blank to keep current password">

                            <ul id="dash_criteria" class="list-unstyled small mt-2 mb-0" style="font-size: 0.8rem;">
                                <li id="dash_crit_len" class="text-danger"><i class="bi bi-x-circle me-1"></i>8+ characters</li>
                                <li id="dash_crit_up" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 uppercase letter</li>
                                <li id="dash_crit_low" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 lowercase letter</li>
                                <li id="dash_crit_num" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 number</li>
                                <li id="dash_crit_spec" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 special character</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-type new password">
                            <div class="form-text text-danger d-none mt-2 fw-bold" id="password_error">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Passwords do not match!
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light fw-bold border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Live Countdown Timer Logic for Customers
        document.addEventListener('DOMContentLoaded', function() {
            function updateTimers() {
                const timers = document.querySelectorAll('.live-timer');
                const now = new Date().getTime();

                timers.forEach(timer => {
                    const endTimeStr = timer.getAttribute('data-end');
                    if (!endTimeStr) return;

                    const endTime = new Date(endTimeStr).getTime();
                    const distance = endTime - now;

                    if (distance <= 0) {
                        timer.innerText = "00:00 (FINISHED)";
                        timer.classList.remove('text-danger');
                        timer.classList.add('text-success');
                    } else {
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        timer.innerText =
                            (minutes < 10 ? "0" : "") + minutes + ":" +
                            (seconds < 10 ? "0" : "") + seconds;
                    }
                });
            }

            // Run timer every second
            setInterval(updateTimers, 1000);
            updateTimers();
        });
    </script>
    <script>
        // Form Validation for Password Matching
        function validatePasswordMatch() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorText = document.getElementById('password_error');

            if (newPassword !== '' && newPassword !== confirmPassword) {
                errorText.classList.remove('d-none'); // Show error message
                return false; // Prevent form submission
            }

            errorText.classList.add('d-none'); // Hide error message
            return true; // Allow form submission
        }
        const dashPwd = document.getElementById('new_password');
        const dashConfPwd = document.getElementById('confirm_password');
        const dashCriteria = document.getElementById('dash_criteria');
        const errorText = document.getElementById('password_error');

        function updateDashCriterion(id, isMet) {
            const el = document.getElementById(id);
            const icon = el.querySelector('i');
            if (isMet) {
                el.classList.replace('text-danger', 'text-success');
                icon.classList.replace('bi-x-circle', 'bi-check-circle');
            } else {
                el.classList.replace('text-success', 'text-danger');
                icon.classList.replace('bi-check-circle', 'bi-x-circle');
            }
        }

        function validateDashPassword() {
            const p = dashPwd.value;
            const c = dashConfPwd.value;

            const hasLen = p.length >= 8;
            const hasUp = /[A-Z]/.test(p);
            const hasLow = /[a-z]/.test(p);
            const hasNum = /[0-9]/.test(p);
            const hasSpec = /[^A-Za-z0-9]/.test(p);

            updateDashCriterion('dash_crit_len', hasLen);
            updateDashCriterion('dash_crit_up', hasUp);
            updateDashCriterion('dash_crit_low', hasLow);
            updateDashCriterion('dash_crit_num', hasNum);
            updateDashCriterion('dash_crit_spec', hasSpec);

            const isStrong = hasLen && hasUp && hasLow && hasNum && hasSpec;

            if (c.length > 0 && p !== c) {
                errorText.classList.remove('d-none');
            } else {
                errorText.classList.add('d-none');
            }

            // If leaving blank to keep current password, it's valid
            if (p === "") {
                errorText.classList.add('d-none');
                return true;
            }

            return isStrong && p === c;
        }

        dashPwd.addEventListener('input', validateDashPassword);
        dashConfPwd.addEventListener('input', validateDashPassword);

        function validatePasswordMatch() {
            return validateDashPassword();
        }

        // Initialize on page load so the red criteria are visible immediately
        validateDashPassword();

        dashPwd.addEventListener('input', validateDashPassword);
        dashConfPwd.addEventListener('input', validateDashPassword);

        function validatePasswordMatch() {
            return validateDashPassword();
        }
    </script>
</body>

</html>
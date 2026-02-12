<?php
session_start();
require 'backend/db_conn.php';

// Check auth
if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold">LAB<span class="text-primary">Assistance</span></span>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-sm-inline">Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="customer_login.php" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container page-container">

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="app-card p-4 text-center bg-dark text-white">
                    <h4 class="fw-bold mb-1">Need Laundry Service?</h4>
                    <p class="text-white-50 small mb-3">We pick up, wash, and deliver.</p>
                    <a href="order.php" class="btn btn-light text-dark fw-bold w-100 rounded-pill py-3">
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

                        <div class="app-card mb-3 p-3" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modal<?php echo $order['order_id']; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-light text-dark border">#<?php echo $order['order_id']; ?></span>
                                        <span class="small text-muted">Tracking: <strong><?php echo $order['tracking_code']; ?></strong></span>
                                    </div>
                                    <h5 class="fw-bold mb-0">₱<?php echo number_format($order['final_price'], 2); ?></h5>
                                    <p class="text-muted small mb-0 mt-1">
                                        <i class="bi bi-basket-fill me-1"></i> <?php echo $order['bag_counts']; ?> bags •
                                        <?php echo $order['services_requested']; ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary rounded-pill mb-1"><?php echo $order['status']; ?></span>
                                    <div class="small text-muted" style="font-size: 0.7rem;">Tap for details</div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modal<?php echo $order['order_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0">
                                    <div class="modal-header border-0 pb-0">
                                        <div>
                                            <h5 class="modal-title fw-bold">Order #<?php echo $order['order_id']; ?></h5>
                                            <p class="small text-muted mb-0">Tracking: <?php echo $order['tracking_code']; ?></p>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body pt-4">

                                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Bag Status</h6>
                                        <?php
                                        // Get bag details
                                        $loadQuery = "SELECT * FROM `Process_Load` WHERE order_id = '" . $order['order_id'] . "'";
                                        $loads = $conn->query($loadQuery);
                                        ?>
                                        <ul class="list-group list-group-flush mb-4 rounded-3 border">
                                            <?php if ($loads->num_rows > 0): ?>
                                                <?php while ($load = $loads->fetch_assoc()): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?php echo $load['bag_label']; ?></strong>
                                                            <div class="small text-muted"><?php echo $load['load_category']; ?></div>
                                                        </div>
                                                        <span class="badge bg-secondary"><?php echo $load['status']; ?></span>
                                                    </li>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <li class="list-group-item text-muted small text-center">No bags assigned yet.</li>
                                            <?php endif; ?>
                                        </ul>

                                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Order Timeline</h6>
                                        <div class="bg-light p-3 rounded-3" style="max-height: 250px; overflow-y: auto;">
                                            <?php
                                            // Get order logs
                                            $logQuery = "SELECT sl.*, pl.bag_label 
                                                         FROM `System_Log` sl
                                                         JOIN `Process_Load` pl ON sl.load_id = pl.load_id
                                                         WHERE pl.order_id = '" . $order['order_id'] . "'
                                                         ORDER BY sl.timestamp DESC";
                                            $logs = $conn->query($logQuery);
                                            ?>

                                            <?php if ($logs->num_rows > 0): ?>
                                                <?php while ($log = $logs->fetch_assoc()): ?>
                                                    <div class="mb-3 pb-2 border-bottom border-light last-no-border">
                                                        <div class="d-flex justify-content-between">
                                                            <strong class="small text-dark"><?php echo $log['status_event']; ?></strong>
                                                            <small class="text-muted" style="font-size: 0.7rem;"><?php echo date("M d, h:i A", strtotime($log['timestamp'])); ?></small>
                                                        </div>
                                                        <div class="small text-muted fst-italic mt-1">
                                                            <i class="bi bi-box-seam me-1"></i><?php echo $log['bag_label']; ?>
                                                            <span class="mx-1">•</span>
                                                            updated by <?php echo $log['employee_name']; ?>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <div class="text-center text-muted small py-3">
                                                    <i class="bi bi-clock-history d-block mb-1 fs-4"></i>
                                                    No logs available yet.
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light w-100 fw-bold" data-bs-dismiss="modal">Close Details</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
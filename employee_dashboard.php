<?php
session_start();
require 'backend/db_conn.php';

// Kick out anyone who isn't an Employee or Manager
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    header("Location: staff_login.php");
    exit();
}

// Check if shop is open or closed
$statusResult = $conn->query("SELECT is_shop_open FROM Shop_Status WHERE status_id = 1");
$shopData = $statusResult->fetch_assoc();
$isOpen = ($shopData && $shopData['is_shop_open'] == 1);

// Get active tasks (Filter out completed orders and completed bags)
$query = "SELECT o.order_id, o.customer_name, o.services_requested, o.status as order_status,
                 pl.load_id, pl.bag_label, pl.status, pl.timer_end
          FROM `Order` o
          JOIN `Process_Load` pl ON o.order_id = pl.order_id
          WHERE o.status != 'Completed' AND pl.status != 'Completed'
          ORDER BY o.order_id DESC, pl.bag_label ASC";
$result = $conn->query($query);

// Group tasks by order
$groupedOrders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];
        if (!isset($groupedOrders[$order_id])) {
            $groupedOrders[$order_id] = [
                'customer_name' => $row['customer_name'],
                'services_requested' => $row['services_requested'],
                'order_status' => $row['order_status'],
                'loads' => []
            ];
        }
        // Push the entire row so we have bag_label, load_id, status, and timer_end
        $groupedOrders[$order_id]['loads'][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Staff Task Queue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
    <style>
        .order-group-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        .order-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 15px;
        }

        .bag-item {
            background-color: #fafafa;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-dark shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold text-white">LAB<span class="text-primary">Assistance</span></span>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-sm-inline">Hi,
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="staff_login.php" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container page-container mt-4">

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="app-card p-4 text-center shadow-sm bg-white rounded-3">
                    <h5 class="fw-bold text-uppercase mb-0 text-dark" style="letter-spacing: 1px;">Shop Status</h5>
                    <?php if ($isOpen): ?>
                        <h1 class="display-2 mb-0" style="color: #198754; font-weight: 800;">OPEN</h1>
                    <?php else: ?>
                        <h1 class="display-2 mb-0" style="color: #dc3545; font-weight: 800;">CLOSED</h1>
                    <?php endif; ?>
                    <p class="text-muted mb-0 fs-5 mt-1"><?php echo date("F j, Y"); ?></p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center pb-3">
            <h3 class="fw-bold">Active Task Queue</h3>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <?php if (!empty($groupedOrders)): ?>
                    <?php foreach ($groupedOrders as $order_id => $order): ?>
                        <div class="order-group-card mb-4 shadow-sm">

                            <div class="order-header">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-person-fill me-1"></i>
                                        <?php echo htmlspecialchars($order['customer_name']); ?>
                                    </h6>
                                    <span class="badge bg-dark">Order #<?php echo $order_id; ?></span>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="bi bi-gear-fill me-1"></i>
                                    <?php echo htmlspecialchars($order['services_requested']); ?>
                                </small>
                            </div>

                            <div class="order-body p-2 bg-white">
                                <?php foreach ($order['loads'] as $load): ?>
                                    <div class="bag-item border rounded p-3 mb-2 shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="fw-bold text-primary">
                                                <i class="bi bi-bag-fill me-1"></i>
                                                <?php echo htmlspecialchars($load['bag_label']); ?>
                                            </div>

                                            <?php
                                            $s = $load['status'];
                                            $badgeClass = 'bg-secondary';
                                            if ($s == 'In Queue') $badgeClass = 'bg-dark';
                                            elseif (strpos($s, 'Washing') !== false) $badgeClass = 'bg-primary';
                                            elseif (strpos($s, 'Drying') !== false) $badgeClass = 'bg-warning text-dark';
                                            elseif ($s == 'Awaiting Pickup') $badgeClass = 'bg-success';
                                            ?>
                                            <span class="badge rounded-pill <?php echo $badgeClass; ?>"><?php echo $s; ?></span>
                                        </div>

                                        <?php if (!empty($load['timer_end'])): ?>
                                            <div class="alert alert-warning py-1 px-2 mb-2 d-flex justify-content-between align-items-center small">
                                                <span class="fw-bold"><i class="bi bi-stopwatch"></i> <?php echo strtoupper($s); ?>:</span>
                                                <span class="fs-6 fw-bold live-timer text-danger" data-end="<?php echo date('c', strtotime($load['timer_end'])); ?>">--:--</span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($isOpen && $s !== 'Completed'): ?>
                                            <button class="btn btn-sm btn-outline-secondary w-100 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#bag-controls-<?php echo $load['load_id']; ?>">
                                                <i class="bi bi-gear"></i> Manage Bag
                                            </button>

                                            <div class="collapse mt-2" id="bag-controls-<?php echo $load['load_id']; ?>">
                                                <div class="bg-light p-2 rounded">

                                                    <?php
                                                    $needsMachine = (stripos($order['services_requested'], 'Wash') !== false || stripos($order['services_requested'], 'Dry') !== false);
                                                    ?>

                                                    <?php if (($s === 'In Queue' && $needsMachine) || $s === 'Washing' || $s === 'Drying'): ?>
                                                        <form action="backend/update_status.php" method="POST" class="mb-2">
                                                            <input type="hidden" name="action" value="start_timer">
                                                            <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                            <label class="small fw-bold text-muted mb-1">
                                                                <?php echo ($s === 'In Queue') ? 'Start Machine (Enter Mins):' : 'Reset/Set Timer:'; ?>
                                                            </label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" name="minutes" class="form-control" placeholder="Mins" required min="1">
                                                                <button type="submit" class="btn btn-primary">Start Timer</button>
                                                            </div>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if (!($s === 'In Queue' && $needsMachine)): ?>
                                                        <form action="backend/update_status.php" method="POST">
                                                            <input type="hidden" name="action" value="next_phase">
                                                            <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                            <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                                                                <?php
                                                                if ($s === 'Pending Dropoff') echo 'Mark as Received (Queue)';
                                                                elseif ($s === 'Washing' || $s === 'Drying' || $s === 'Folding') echo 'Move to Next Phase';
                                                                elseif ($s === 'Awaiting Pickup') echo 'Complete Order';
                                                                else echo 'Next Step';
                                                                ?>
                                                                <i class="bi bi-arrow-right-short"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="p-2 bg-white border-top text-center">
                                <button class="btn btn-sm btn-link text-decoration-none w-100" onclick="viewLogs('<?php echo $order_id; ?>')">
                                    <i class="bi bi-journal-text"></i> View Order History Logs
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle-fill display-1 text-success opacity-50"></i>
                        <h4 class="mt-3 fw-bold">All Clear!</h4>
                        <p>No active laundry tasks at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Order History Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div id="logContainer" class="bg-light p-3 rounded-3 small">
                        <span class="text-muted">Fetching logs...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Live Countdown Timer Logic for individual bags
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

            setInterval(updateTimers, 1000);
            updateTimers();
        });

        // Logs Viewer Logic
        var logsModal = new bootstrap.Modal(document.getElementById('logsModal'));

        function viewLogs(orderId) {
            const container = document.getElementById('logContainer');
            container.innerHTML = '<div class="text-center text-muted spinner-border spinner-border-sm" role="status"></div> Loading...';
            logsModal.show();

            fetch('backend/fetch_logs.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => container.innerHTML = data)
                .catch(err => container.innerHTML = '<span class="text-danger">Error loading logs.</span>');
        }
    </script>
</body>

</html>
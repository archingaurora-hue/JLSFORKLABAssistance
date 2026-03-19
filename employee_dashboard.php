<?php
session_start();
require 'backend/db_conn.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    header("Location: staff_login.php");
    exit();
}

$statusResult = $conn->query("SELECT is_shop_open FROM Shop_Status WHERE status_id = 1");
$shopData = $statusResult->fetch_assoc();
$isOpen = ($shopData && $shopData['is_shop_open'] == 1);

$query = "SELECT o.order_id, o.customer_name, o.services_requested, o.supplies_requested, o.status as order_status, o.final_price,
                 pl.load_id, pl.bag_label, pl.status, pl.timer_end, pl.timer_duration, pl.timer_remaining
          FROM `Order` o
          JOIN `Process_Load` pl ON o.order_id = pl.order_id
          WHERE o.status != 'Completed' AND pl.status != 'Completed'
          ORDER BY o.order_id DESC, pl.bag_label ASC";
$result = $conn->query($query);

$groupedOrders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];
        if (!isset($groupedOrders[$order_id])) {
            $groupedOrders[$order_id] = [
                'customer_name' => $row['customer_name'],
                'services_requested' => $row['services_requested'],
                'supplies_requested' => $row['supplies_requested'],
                'order_status' => $row['order_status'],
                'final_price' => $row['final_price'],
                'loads' => []
            ];
        }
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

        .delete-bag-btn {
            background: none;
            border: none;
            color: #dc3545;
            padding: 0 5px;
            font-size: 1rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .delete-bag-btn:hover {
            opacity: 1;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-dark shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold text-white">LAB<span class="text-primary">Assistance</span></span>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-sm-inline">Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>

                <?php if ($_SESSION['role'] === 'Manager'): ?>
                    <a href="manager_dashboard.php" class="btn btn-sm btn-outline-primary rounded-pill fw-bold" title="Go to Manager Dashboard">
                        <i class="bi bi-speedometer2"></i> <span class="d-none d-sm-inline">Manager DB</span>
                    </a>
                <?php endif; ?>

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

        <div class="row justify-content-center mb-3">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchCustomer" class="form-control border-start-0 ps-0 form-control-lg" placeholder="Search for customer name, bag, or order #" onkeyup="filterOrders()">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center pb-3">
            <h3 class="fw-bold">Active Task Queue</h3>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <?php if (!empty($groupedOrders)): ?>
                    <?php foreach ($groupedOrders as $order_id => $order):

                        $totalBags = count($order['loads']);
                        $pendingCount = 0;
                        $awaitingCount = 0;
                        foreach ($order['loads'] as $l) {
                            if ($l['status'] === 'Pending Dropoff') $pendingCount++;
                            if ($l['status'] === 'Awaiting Pickup') $awaitingCount++;
                        }
                    ?>
                        <div class="order-group-card mb-4 shadow-sm">

                            <div class="order-header">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-person-fill me-1"></i>
                                        <?php echo htmlspecialchars($order['customer_name']); ?>
                                    </h6>
                                    <span class="badge bg-dark">Order #<?php echo $order_id; ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-start mt-2">
                                    <div>
                                        <small class="text-muted d-block mb-1">
                                            <i class="bi bi-gear-fill me-1"></i>
                                            <?php echo htmlspecialchars($order['services_requested']); ?>
                                        </small>

                                        <div class="mb-2">
                                            <?php if (!empty($order['supplies_requested'])): ?>
                                                <span class="badge bg-info text-dark border border-info" style="font-size: 0.7rem;">
                                                    <i class="bi bi-droplet-fill me-1"></i> <?php echo htmlspecialchars($order['supplies_requested']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                                                    <i class="bi bi-x-circle me-1"></i> No Add-ons
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <button class="btn btn-sm btn-outline-primary shadow-sm rounded-pill fw-bold" onclick="openAddBagModal('<?php echo htmlspecialchars($order_id); ?>')">
                                            <i class="bi bi-plus-circle"></i> Add Bag
                                        </button>
                                    </div>

                                    <div>
                                        <div id="price-view-<?php echo $order_id; ?>" class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-success" style="font-size: 1.1rem;">₱<?php echo number_format($order['final_price'], 2); ?></span>
                                            <?php
                                            if (!isset($_SESSION['role']) || ($_SESSION['role'] == 'Manager')) { ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="togglePriceEdit('<?php echo $order_id; ?>')" title="Edit Price">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                            <?php }
                                            ?>
                                        </div>

                                        <form id="price-form-<?php echo $order_id; ?>" action="backend/update_price.php" method="POST" class="d-none align-items-center" style="max-width: 200px;">
                                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text fw-bold">₱</span>
                                                <input type="number" step="0.01" min="0" name="final_price" class="form-control text-center fw-bold text-success" value="<?php echo htmlspecialchars($order['final_price']); ?>" required>
                                                <button class="btn btn-success" type="submit" title="Save Price"><i class="bi bi-check-lg"></i></button>
                                                <button class="btn btn-secondary" type="button" onclick="togglePriceEdit('<?php echo $order_id; ?>')" title="Cancel"><i class="bi bi-x-lg"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="order-body p-2 bg-white">
                                <?php foreach ($order['loads'] as $load): ?>
                                    <div class="bag-item border rounded p-3 mb-2 shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="fw-bold text-primary">
                                                <i class="bi bi-bag-fill me-1"></i>
                                                <?php echo htmlspecialchars($load['bag_label']); ?>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <?php
                                                $s = $load['status'];
                                                $badgeClass = 'bg-secondary';
                                                if ($s == 'In Queue') $badgeClass = 'bg-dark';
                                                elseif (strpos($s, 'Washing') !== false) $badgeClass = 'bg-primary';
                                                elseif (strpos($s, 'Drying') !== false) $badgeClass = 'bg-warning text-dark';
                                                elseif ($s == 'Awaiting Pickup') $badgeClass = 'bg-success';
                                                ?>
                                                <span class="badge rounded-pill <?php echo $badgeClass; ?> me-2"><?php echo $s; ?></span>

                                                <form action="backend/manage_bag.php" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this bag? The price will automatically decrease.');">
                                                    <input type="hidden" name="action" value="delete_bag">
                                                    <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                    <button type="submit" class="delete-bag-btn" title="Delete Bag"><i class="bi bi-trash-fill"></i></button>
                                                </form>
                                            </div>
                                        </div>

                                        <?php
                                        $hasTimer = !empty($load['timer_end']);
                                        $isPaused = ($load['timer_remaining'] !== null);

                                        if ($hasTimer || $isPaused):
                                            $duration = intval($load['timer_duration'] ?? 1);
                                        ?>
                                            <div class="border rounded p-2 mb-3 bg-white shadow-sm border-warning">
                                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                                    <span class="fw-bold text-dark"><i class="bi bi-stopwatch me-1"></i> <?php echo strtoupper($s); ?> TIMER</span>
                                                    <?php if ($isPaused): ?>
                                                        <span class="badge bg-warning text-dark px-2"><i class="bi bi-pause-fill"></i> PAUSED</span>
                                                    <?php else: ?>
                                                        <span class="live-timer text-danger fw-bold" data-end="<?php echo date('c', strtotime($load['timer_end'])); ?>" data-duration="<?php echo $duration; ?>">--:--</span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="progress" style="height: 6px; background-color: #e9ecef;">
                                                    <?php if ($isPaused):
                                                        $rem = intval($load['timer_remaining']);
                                                        $progressPct = max(0, min(100, (1 - ($rem / $duration)) * 100));
                                                    ?>
                                                        <div class="progress-bar bg-warning progress-bar-striped" role="progressbar" style="width: <?php echo $progressPct; ?>%;"></div>
                                                    <?php else: ?>
                                                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated live-progress" role="progressbar" style="width: 0%;" data-end="<?php echo date('c', strtotime($load['timer_end'])); ?>" data-duration="<?php echo $duration; ?>"></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($isOpen && $s !== 'Completed' && $s !== 'Pending Dropoff' && $s !== 'Awaiting Pickup'): ?>
                                            <button class="btn btn-sm btn-outline-secondary w-100 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#bag-controls-<?php echo $load['load_id']; ?>">
                                                <i class="bi bi-gear"></i> Manage Bag
                                            </button>

                                            <div class="collapse mt-2" id="bag-controls-<?php echo $load['load_id']; ?>">
                                                <div class="bg-light p-2 rounded border">

                                                    <?php
                                                    // Determine the specific Next Phase Name
                                                    $hasW = (stripos($order['services_requested'], 'Wash') !== false);
                                                    $hasD = (stripos($order['services_requested'], 'Dry') !== false);
                                                    $hasF = (stripos($order['services_requested'], 'Fold') !== false);
                                                    $needsMachine = ($hasW || $hasD);

                                                    $nextPhase = 'Awaiting Pickup';
                                                    if ($s === 'In Queue') {
                                                        $nextPhase = $hasW ? 'Washing' : ($hasD ? 'Drying' : 'Folding');
                                                    } elseif ($s === 'Washing') {
                                                        $nextPhase = $hasD ? 'Drying' : ($hasF ? 'Folding' : 'Awaiting Pickup');
                                                    } elseif ($s === 'Drying') {
                                                        $nextPhase = $hasF ? 'Folding' : 'Awaiting Pickup';
                                                    } elseif ($s === 'Folding') {
                                                        $nextPhase = 'Awaiting Pickup';
                                                    }
                                                    ?>

                                                    <?php if (($s === 'In Queue' && $needsMachine) || $s === 'Washing' || $s === 'Drying'): ?>

                                                        <?php if ($hasTimer): ?>
                                                            <div class="d-flex gap-2 mb-2">
                                                                <form action="backend/update_status.php" method="POST" class="flex-grow-1">
                                                                    <input type="hidden" name="action" value="pause_timer">
                                                                    <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                    <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold"><i class="bi bi-pause-fill"></i> Pause</button>
                                                                </form>
                                                                <form action="backend/update_status.php" method="POST" class="flex-grow-1">
                                                                    <input type="hidden" name="action" value="reset_timer">
                                                                    <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                                                                </form>
                                                            </div>

                                                        <?php elseif ($isPaused): ?>
                                                            <div class="d-flex gap-2 mb-2">
                                                                <form action="backend/update_status.php" method="POST" class="flex-grow-1">
                                                                    <input type="hidden" name="action" value="resume_timer">
                                                                    <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                    <button type="submit" class="btn btn-success btn-sm w-100 fw-bold"><i class="bi bi-play-fill"></i> Resume</button>
                                                                </form>
                                                                <form action="backend/update_status.php" method="POST" class="flex-grow-1">
                                                                    <input type="hidden" name="action" value="reset_timer">
                                                                    <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                                                                </form>
                                                            </div>

                                                        <?php else: ?>
                                                            <form action="backend/update_status.php" method="POST" class="mb-2">
                                                                <input type="hidden" name="action" value="start_timer">
                                                                <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                <label class="small fw-bold text-muted mb-1">
                                                                    <?php echo ($s === 'In Queue') ? 'Start Machine:' : 'Set New Timer:'; ?>
                                                                </label>
                                                                <div class="input-group input-group-sm" style="max-width: 180px;">
                                                                    <input type="number" name="minutes" class="form-control text-center fw-bold" placeholder="Mins" required min="1">
                                                                    <button type="submit" class="btn btn-primary fw-bold px-3">Start</button>
                                                                </div>
                                                            </form>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <?php
                                                    // Hides "Move to Next Phase" if timer hasn't been started yet
                                                    $showNextPhase = true;
                                                    if ($s === 'In Queue' && $needsMachine) $showNextPhase = false;
                                                    if (($s === 'Washing' || $s === 'Drying') && !$hasTimer && !$isPaused) $showNextPhase = false;

                                                    if ($showNextPhase):
                                                    ?>
                                                        <form action="backend/update_status.php" method="POST">
                                                            <input type="hidden" name="action" value="next_phase">
                                                            <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                            <button type="submit" class="btn btn-success btn-sm w-100 fw-bold mt-2">
                                                                Move to <?php echo $nextPhase; ?> <i class="bi bi-arrow-right-short"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="p-3 bg-white border-top d-flex flex-column gap-2">

                                <?php if ($pendingCount > 0 && $isOpen): ?>
                                    <form action="backend/update_status.php" method="POST" class="m-0">
                                        <input type="hidden" name="action" value="receive_order">
                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                            <i class="bi bi-box-seam me-1"></i> Mark Order as Received
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($awaitingCount === $totalBags && $totalBags > 0 && $isOpen): ?>
                                    <form action="backend/update_status.php" method="POST" class="m-0">
                                        <input type="hidden" name="action" value="complete_order">
                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                                            <i class="bi bi-check2-circle me-1"></i> Complete Order
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <button class="btn btn-sm btn-link text-decoration-none w-100 mt-1" onclick="viewLogs('<?php echo $order_id; ?>')">
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

    <div class="modal fade" id="addBagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Add Extra Bag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-4">
                    <form action="backend/manage_bag.php" method="POST">
                        <input type="hidden" name="action" value="add_bag">
                        <input type="hidden" name="order_id" id="add_bag_order_id">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Bag Label</label>
                            <input type="text" class="form-control" name="bag_label" placeholder="e.g. Bag 2, Whites, Comforter" required>
                        </div>
                        <button type="submit" class="btn-primary-app w-100 py-2 fw-bold">Add to Queue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterOrders() {
            const input = document.getElementById('searchCustomer').value.toLowerCase();
            const cards = document.querySelectorAll('.order-group-card');

            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                if (text.includes(input)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        }

        function togglePriceEdit(orderId) {
            const viewDiv = document.getElementById('price-view-' + orderId);
            const formDiv = document.getElementById('price-form-' + orderId);

            if (viewDiv.classList.contains('d-none')) {
                viewDiv.classList.remove('d-none');
                viewDiv.classList.add('d-flex');
                formDiv.classList.remove('d-flex');
                formDiv.classList.add('d-none');
            } else {
                viewDiv.classList.remove('d-flex');
                viewDiv.classList.add('d-none');
                formDiv.classList.remove('d-none');
                formDiv.classList.add('d-flex');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            function updateTimers() {
                const timers = document.querySelectorAll('.live-timer');
                const bars = document.querySelectorAll('.live-progress');
                const now = new Date().getTime();

                timers.forEach((timer, index) => {
                    const endTimeStr = timer.getAttribute('data-end');
                    const durationSecs = parseInt(timer.getAttribute('data-duration'));
                    if (!endTimeStr) return;

                    const endTime = new Date(endTimeStr).getTime();
                    const distance = endTime - now;
                    const bar = bars[index];

                    if (distance <= 0) {
                        timer.innerText = "00:00 (FINISHED)";
                        timer.classList.remove('text-danger');
                        timer.classList.add('text-success');

                        if (bar) {
                            bar.style.width = "100%";
                            bar.classList.remove('bg-primary', 'progress-bar-animated', 'progress-bar-striped');
                            bar.classList.add('bg-success');
                        }
                    } else {
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        timer.innerText =
                            (minutes < 10 ? "0" : "") + minutes + ":" +
                            (seconds < 10 ? "0" : "") + seconds;

                        if (bar && durationSecs > 0) {
                            const distanceSecs = distance / 1000;
                            const progressPct = Math.max(0, Math.min(100, (1 - (distanceSecs / durationSecs)) * 100));
                            bar.style.width = progressPct + "%";
                        }
                    }
                });
            }

            setInterval(updateTimers, 1000);
            updateTimers();
        });

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

        var addBagModalObj = new bootstrap.Modal(document.getElementById('addBagModal'));

        function openAddBagModal(orderId) {
            document.getElementById('add_bag_order_id').value = orderId;
            addBagModalObj.show();
        }
    </script>
</body>

</html>
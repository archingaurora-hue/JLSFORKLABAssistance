<?php
session_start();
require 'backend/db_conn.php';

// Check permissions
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    header("Location: employee_login.php");
    exit();
}

// Get active tasks
$query = "SELECT pl.*, o.customer_name, o.services_requested 
          FROM `Process_Load` pl
          JOIN `Order` o ON pl.order_id = o.order_id
          WHERE pl.status != 'Completed' AND pl.status != 'Order Completed'
          ORDER BY pl.order_id DESC, pl.bag_label ASC";
$result = $conn->query($query);

// Group by order
$groupedOrders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];
        if (!isset($groupedOrders[$order_id])) {
            $groupedOrders[$order_id] = [
                'customer_name' => $row['customer_name'],
                'services_requested' => $row['services_requested'],
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
            border-bottom: 1px solid #f0f0f0;
            padding: 12px 15px;
        }

        .bag-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <span class="navbar-brand fw-bold fs-5">Task Queue</span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-light rounded-circle" onclick="location.reload();"><i class="bi bi-arrow-clockwise"></i></button>
                <a href="employee_login.php" class="btn btn-sm btn-danger rounded-pill px-3">Exit</a>
            </div>
        </div>
    </nav>

    <div class="container page-container mt-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">

                <?php if (!empty($groupedOrders)): ?>
                    <?php foreach ($groupedOrders as $order_id => $order): ?>

                        <div class="order-group-card mb-4 shadow-sm">

                            <div class="order-header">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-person-fill me-1"></i> <?php echo htmlspecialchars($order['customer_name']); ?>
                                    </h6>
                                    <span class="badge bg-dark">Order #<?php echo $order_id; ?></span>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="bi bi-gear-fill me-1"></i> <?php echo htmlspecialchars($order['services_requested']); ?>
                                </small>
                            </div>

                            <div class="order-body">
                                <?php foreach ($order['loads'] as $load): ?>
                                    <div class="bag-item">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="fw-bold text-primary">
                                                <i class="bi bi-bag-fill me-1"></i> <?php echo htmlspecialchars($load['bag_label']); ?>
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

                                        <button class="btn btn-sm btn-outline-dark w-100 rounded-pill fw-bold"
                                            onclick="openUpdateModal('<?php echo $load['load_id']; ?>', '<?php echo $load['bag_label']; ?>', '<?php echo $load['status']; ?>')">
                                            Update Bag Status
                                        </button>
                                    </div>
                                <?php endforeach; ?>
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

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Update Bag Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <p class="text-muted mb-3">Bag: <span id="modalBagLabel" class="fw-bold text-dark"></span></p>

                    <form action="backend/update_status.php" method="POST">
                        <input type="hidden" name="load_id" id="modalLoadId">

                        <div class="form-floating mb-3">
                            <select class="form-select" name="new_status" id="modalStatusSelect">
                                <option value="Pending Dropoff">Pending Dropoff</option>
                                <option value="In Queue">In Queue</option>
                                <option value="Washing">Washing</option>
                                <option value="Wash Complete">Wash Complete</option>
                                <option value="Drying">Drying</option>
                                <option value="Drying Complete">Drying Complete</option>
                                <option value="Folding">Folding</option>
                                <option value="Folding Complete">Folding Complete</option>
                                <option value="Awaiting Pickup">Awaiting Pickup</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <label>Select New Status</label>
                        </div>

                        <button type="submit" class="btn-primary-app mb-4 w-100">Confirm Update</button>
                    </form>

                    <h6 class="fw-bold small text-muted text-uppercase mb-2">History Log</h6>
                    <div id="logContainer" class="bg-light p-3 rounded-3 small" style="max-height: 150px; overflow-y: auto;">
                        <span class="text-muted">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));

        function openUpdateModal(loadId, bagLabel, currentStatus) {
            document.getElementById('modalLoadId').value = loadId;
            document.getElementById('modalBagLabel').innerText = bagLabel;
            document.getElementById('modalStatusSelect').value = currentStatus;
            fetchLogs(loadId);
            statusModal.show();
        }

        function fetchLogs(loadId) {
            const container = document.getElementById('logContainer');
            container.innerHTML = '<div class="text-center text-muted">Loading...</div>';
            fetch('backend/fetch_logs.php?load_id=' + loadId)
                .then(response => response.text())
                .then(data => container.innerHTML = data)
                .catch(err => container.innerHTML = 'Error loading logs.');
        }
    </script>
</body>

</html>
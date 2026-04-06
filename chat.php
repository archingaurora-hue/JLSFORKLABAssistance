<?php
session_start();
require 'backend/db_conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("Error: Order ID is missing.");
}

$isStaff = isset($_SESSION['role']) && in_array($_SESSION['role'], ['Employee', 'Manager']);
$backLink = $isStaff ? 'employee_dashboard.php' : 'dashboard.php';

// --- Fetch Order and Bag details ---
$orderQuery = "SELECT o.order_id, o.tracking_code, o.customer_name, o.created_at,
                      (SELECT GROUP_CONCAT(bag_label SEPARATOR ', ') FROM `process_load` WHERE order_id = o.order_id) as bags
               FROM `order` o
               WHERE o.order_id = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    die("Error: Order not found.");
}
$orderData = $orderResult->fetch_assoc();

$tracking_code = $orderData['tracking_code'] ?? 'N/A';
$customer_name = $orderData['customer_name'] ?? 'Customer';
$bags_involved = $orderData['bags'] ? $orderData['bags'] : 'No bags assigned yet';

// Format the date for the UI
$order_date = isset($orderData['created_at']) ? date('M j, Y, g:i A', strtotime($orderData['created_at'])) : 'Unknown';

// --- 30 Days Auto-Close Logic ---
$created_at = new DateTime($orderData['created_at']);
$now = new DateTime();
$days_passed = $now->diff($created_at)->days;
$is_closed = $days_passed >= 30;

// --- Fetch Staff Involved (For Staff POV Banner) ---
$staffInvolved = "";
if ($isStaff) {
    // Query to get distinct staff members who have sent messages in this order
    $staffQuery = "SELECT DISTINCT u.first_name, u.role 
                   FROM `order_messages` m
                   JOIN `user` u ON m.sender_id = u.user_id
                   WHERE m.order_id = ? AND u.role IN ('Employee', 'Manager')";
    $staffStmt = $conn->prepare($staffQuery);
    $staffStmt->bind_param("s", $order_id);
    $staffStmt->execute();
    $staffResult = $staffStmt->get_result();

    $staffList = [];
    while ($row = $staffResult->fetch_assoc()) {
        $staffList[] = $row['first_name'] . ' (' . $row['role'] . ')';
    }
    $staffInvolved = !empty($staffList) ? implode(', ', $staffList) : 'None yet';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chat - Order #<?php echo htmlspecialchars($order_id); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            background-color: #f4f6f9;
            display: flex;
            flex-direction: column;
        }

        /* Adjust chat container to fit below the navbar */
        .chat-wrapper {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            padding: 15px;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 800px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .chat-header {
            background-color: <?php echo $isStaff ? '#343a40' : '#0d6efd'; ?>;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background-color: #f8f9fa;
        }

        .chat-input-area {
            padding: 15px;
            background: white;
            border-top: 1px solid #dee2e6;
        }

        /* Quick Replies Styling */
        .quick-replies-container {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .quick-replies-container::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        .quick-reply-btn {
            white-space: nowrap;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Message Bubbles */
        .msg-mine {
            align-self: flex-end;
            background-color: <?php echo $isStaff ? '#212529' : '#0d6efd'; ?>;
            color: white;
            padding: 10px 15px;
            border-radius: 15px 15px 0 15px;
            max-width: 85%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .msg-theirs {
            align-self: flex-start;
            background-color: #ffffff;
            color: #333;
            padding: 10px 15px;
            border-radius: 15px 15px 15px 0;
            max-width: 85%;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .msg-meta {
            font-size: 0.7rem;
            color: #6c757d;
            margin-top: 4px;
        }

        .msg-mine .msg-meta {
            color: #e9ecef;
            text-align: right;
        }

        /* Dynamic Role Badge Colors */
        .badge-Manager {
            background-color: #0a2e7a !important;
            /* Dark Blue */
            color: #ffffff !important;
        }

        .badge-Employee {
            background-color: #0d6efd !important;
            /* Standard Bootstrap Blue */
            color: #ffffff !important;
        }

        .badge-Customer {
            background-color: #198754 !important;
            /* Standard Bootstrap Green */
            color: #ffffff !important;
        }
    </style>
</head>

<body>

    <?php if ($isStaff): ?>
        <nav class="navbar navbar-light bg-dark shadow-sm sticky-top">
            <div class="container">
                <span class="navbar-brand fw-bold text-white d-flex align-items-center gap-2">
                    <img src="assets/labaratory_logo_white.png" alt="LABAssistance Logo" style="height: 28px; width: auto;">
                    <span>LAB<span class="text-primary">Assistance</span></span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white small d-none d-sm-inline me-2"><i class="bi bi-person-circle text-primary me-1"></i> <?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?></span>
                    <a href="<?php echo $backLink; ?>" class="btn btn-sm btn-outline-light rounded-pill">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </div>
            </div>
        </nav>
    <?php else: ?>
        <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
            <div class="container">
                <span class="navbar-brand fw-bold d-flex align-items-center gap-2">
                    <img src="assets/labaratory_logo.png" alt="LABAssistance Logo" style="height: 28px; width: auto;">
                    <span>LAB<span class="text-primary">Assistance</span></span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted d-none d-sm-inline me-2">Hi, <?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?></span>
                    <a href="<?php echo $backLink; ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <div class="chat-wrapper">
        <div class="chat-container">
            <div class="chat-header">
                <a href="<?php echo $backLink; ?>" class="btn btn-sm btn-light rounded-circle text-dark d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div class="flex-grow-1">
                    <?php if ($isStaff): ?>
                        <h5 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="bi bi-person-fill me-2"></i> <?php echo htmlspecialchars($customer_name); ?>
                        </h5>
                        <div class="small mt-1 opacity-75" style="line-height: 1.4;">
                            <strong>TRK:</strong> <?php echo htmlspecialchars($tracking_code); ?> &nbsp;|&nbsp; <strong>Order:</strong> #<?php echo htmlspecialchars($order_id); ?><br>
                            <strong>Placed:</strong> <?php echo htmlspecialchars($order_date); ?> &nbsp;|&nbsp; <strong>Bags:</strong> <?php echo htmlspecialchars($bags_involved); ?>
                        </div>
                    <?php else: ?>
                        <h5 class="mb-0 fw-bold d-flex align-items-center" style="letter-spacing: 1px;">
                            <i class="bi bi-geo-alt-fill me-2"></i> TRK: <?php echo htmlspecialchars($tracking_code); ?>
                        </h5>
                        <div class="small mt-1 opacity-75" style="line-height: 1.4;">
                            <strong>Order:</strong> #<?php echo htmlspecialchars($order_id); ?> &nbsp;|&nbsp; <strong>Bags:</strong> <?php echo htmlspecialchars($bags_involved); ?><br>
                            <strong>Placed:</strong> <?php echo htmlspecialchars($order_date); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isStaff): ?>
                <div class="bg-light p-2 small border-bottom text-muted text-center shadow-sm" style="z-index: 10;">
                    <i class="bi bi-people-fill me-1"></i> <strong>Staff participating:</strong> <?php echo htmlspecialchars($staffInvolved); ?>
                </div>
            <?php endif; ?>

            <div class="chat-messages" id="chatMessagesContainer">
                <div class="text-center text-muted small mt-4">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div><br>
                    Loading conversation...
                </div>
            </div>

            <div class="chat-input-area">
                <?php if ($is_closed): ?>
                    <div class="alert alert-secondary mb-0 text-center shadow-sm border border-secondary border-opacity-25">
                        <i class="bi bi-lock-fill me-1"></i> This chat session is permanently closed (Older than 30 days).
                    </div>
                <?php else: ?>

                    <?php if (!$isStaff): ?>
                        <div class="quick-replies-container">
                            <button type="button" class="btn btn-outline-primary rounded-pill quick-reply-btn py-1" onclick="sendQuickReply('When can I get my laundry?')">When can I get my laundry?</button>
                            <button type="button" class="btn btn-outline-primary rounded-pill quick-reply-btn py-1" onclick="sendQuickReply('Can you follow up my laundry?')">Can you follow up my laundry?</button>
                            <button type="button" class="btn btn-outline-primary rounded-pill quick-reply-btn py-1" onclick="sendQuickReply('Will you be closing early?')">Will you be closing early?</button>
                        </div>
                    <?php endif; ?>

                    <form id="chatSendForm" onsubmit="sendChatMessage(event)">
                        <div class="input-group input-group-lg shadow-sm rounded">
                            <input type="text" id="chatMessageInput" class="form-control fs-6 border-secondary border-opacity-25" placeholder="Type your message here..." required autocomplete="off">
                            <button class="btn <?php echo $isStaff ? 'btn-dark' : 'btn-primary'; ?> px-4" type="submit" id="chatSendBtn">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const orderId = "<?php echo htmlspecialchars($order_id); ?>";
        const container = document.getElementById('chatMessagesContainer');
        let autoScroll = true;

        // Detect if user scrolls up so we don't forcefully yank them down during auto-refresh
        container.addEventListener('scroll', () => {
            const isAtBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 50;
            autoScroll = isAtBottom;
        });

        function fetchMessages() {
            fetch('backend/fetch_messages.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderMessages(data.messages);
                    } else {
                        container.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
                    }
                })
                .catch(err => console.error("Error:", err));
        }

        function renderMessages(messages) {
            if (messages.length === 0) {
                container.innerHTML = `
            <div class="text-center text-muted small mt-5">
                <i class="bi bi-chat-square-text fs-1 d-block mb-3 text-secondary opacity-25"></i>
                No messages yet. Send a message to get started!
            </div>`;
                return;
            }

            let html = '';
            messages.forEach(msg => {
                let safeRole = escapeHtml(msg.role);
                // Create the color-coded badge dynamically based on the CSS classes defined above
                let roleBadge = `<span class="badge badge-${safeRole} ms-1" style="font-size:0.65rem;">${safeRole}</span>`;

                if (msg.is_mine) {
                    html += `
                <div class="msg-mine">
                    <div>${escapeHtml(msg.message_text)}</div>
                    <div class="msg-meta">${msg.time_sent}</div>
                </div>`;
                } else {
                    let senderName = escapeHtml(msg.first_name) + roleBadge;

                    html += `
                <div class="msg-theirs">
                    <div class="small fw-bold mb-1 text-primary">${senderName}</div>
                    <div>${escapeHtml(msg.message_text)}</div>
                    <div class="msg-meta">${msg.time_sent}</div>
                </div>`;
                }
            });

            container.innerHTML = html;

            if (autoScroll) {
                container.scrollTop = container.scrollHeight;
            }
        }

        <?php if (!$is_closed): ?>

            // Function to handle quick reply clicks
            function sendQuickReply(text) {
                const input = document.getElementById('chatMessageInput');
                input.value = text;
                // Automatically click the send button to submit the form
                document.getElementById('chatSendBtn').click();
            }

            function sendChatMessage(e) {
                e.preventDefault();
                const input = document.getElementById('chatMessageInput');
                const msgText = input.value.trim();
                const btn = document.getElementById('chatSendBtn');

                if (msgText === '') return;

                input.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('message', msgText);

                // Corrected endpoint here:
                fetch('backend/send_message.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            input.value = '';
                            autoScroll = true; // Force scroll to bottom on new message
                            fetchMessages();
                        } else {
                            alert('Failed to send: ' + data.error);
                        }
                    })
                    .finally(() => {
                        input.disabled = false;
                        btn.innerHTML = '<i class="bi bi-send-fill"></i>';
                        input.focus();
                    });
            }
        <?php endif; ?>

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        // Initial load and start sync loop
        fetchMessages();
        setInterval(fetchMessages, 3000);
    </script>
</body>

</html>
<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "laundry_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Fetch shop settings
$status_query = $conn->query("SELECT * FROM Shop_Status WHERE status_id = 1");

if ($status_query && $status_query->num_rows > 0) {
    $shop_data = $status_query->fetch_assoc();

    $now = new DateTime();
    $currentTime = $now->format('H:i:s');
    $currentDateTime = $now->format('Y-m-d H:i:s');

    $expected_status = 0;


    // Determine if shop should be open
    // PRIORITY 1: Check if there is an active "OPEN NOW" override
    if (!empty($shop_data['next_manual_close_time']) && $shop_data['next_manual_close_time'] > $currentDateTime) {
        $expected_status = 1;
    }
    // PRIORITY 2: Check if there is an active "CLOSE NOW" override
    elseif (!empty($shop_data['next_manual_open_time']) && $shop_data['next_manual_open_time'] > $currentDateTime) {
        $expected_status = 0;
    }
    // PRIORITY 3: Fall back to normal operating hours
    else {
        $close_time = !empty($shop_data['current_closing_time']) ? $shop_data['current_closing_time'] : $shop_data['default_close_time'];

        if ($currentTime >= $shop_data['default_open_time'] && $currentTime <= $close_time) {
            $expected_status = 1;
        }
    }
    // Update DB if status changed
    if ($shop_data['is_shop_open'] != $expected_status) {
        $conn->query("UPDATE Shop_Status SET is_shop_open = $expected_status WHERE status_id = 1");
        $shop_data['is_shop_open'] = $expected_status;
    }

    // Prepare variables for frontend UI
    $is_shop_open = $shop_data['is_shop_open'];
    $has_override = !empty($shop_data['next_manual_open_time']);

    $effective_close = !empty($shop_data['current_closing_time']) ? $shop_data['current_closing_time'] : $shop_data['default_close_time'];
    $display_close_time = date("g:i A", strtotime($effective_close));

    if ($has_override) {
        $nextOpenObj = new DateTime($shop_data['next_manual_open_time']);
        $display_open_time = $nextOpenObj->format("F j, Y, g:i A");
    } else {
        $display_open_time = date("g:i A", strtotime($shop_data['default_open_time']));
    }

    $shop_status_message = $is_shop_open
        ? "Open today until " . $display_close_time
        : "Currently closed. Opens on " . $display_open_time;
}

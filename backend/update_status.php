<?php
session_start();
require 'db_conn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $employee_name = $_SESSION['full_name'] ?? 'Staff';

    // ==========================================
    // ORDER-LEVEL ACTIONS
    // ==========================================
    if ($action === 'receive_order') {
        $order_id = $_POST['order_id'];
        $upd = $conn->prepare("UPDATE `Process_Load` SET status = 'In Queue' WHERE order_id = ? AND status = 'Pending Dropoff'");
        $upd->bind_param("s", $order_id);
        $upd->execute();

        $conn->query("UPDATE `Order` SET status = 'In Progress' WHERE order_id = '$order_id'");
        $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name marked the order as received. All pending bags moved to Queue.')");

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if ($action === 'complete_order') {
        $order_id = $_POST['order_id'];
        $upd = $conn->prepare("UPDATE `Process_Load` SET status = 'Completed' WHERE order_id = ? AND status = 'Awaiting Pickup'");
        $upd->bind_param("s", $order_id);
        $upd->execute();

        $conn->query("UPDATE `Order` SET status = 'Completed' WHERE order_id = '$order_id'");
        $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name completed the order. Customer picked up the laundry.')");

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // ==========================================
    // BAG-LEVEL ACTIONS
    // ==========================================
    $load_id = $_POST['load_id'] ?? 0;
    if ($load_id) {
        $stmt = $conn->prepare("SELECT pl.*, o.services_requested, o.final_price, o.customer_name 
                                FROM `Process_Load` pl 
                                JOIN `Order` o ON pl.order_id = o.order_id 
                                WHERE pl.load_id = ?");
        $stmt->bind_param("i", $load_id);
        $stmt->execute();
        $bag = $stmt->get_result()->fetch_assoc();

        $services = $bag['services_requested'];
        $curr = $bag['status'];
        $order_id = $bag['order_id'];
        $bag_label = $bag['bag_label'];

        $hasW = stripos($services, 'Wash') !== false;
        $hasD = stripos($services, 'Dry') !== false;
        $hasF = stripos($services, 'Fold') !== false;

        if ($action === 'start_timer') {
            $mins = intval($_POST['minutes']);
            $duration_secs = $mins * 60;
            $end = date('Y-m-d H:i:s', strtotime("+$mins minutes"));

            $new_status = $curr;
            if ($curr === 'In Queue') {
                if ($hasW) $new_status = 'Washing';
                elseif ($hasD) $new_status = 'Drying';
            }

            $upd = $conn->prepare("UPDATE `Process_Load` SET timer_end = ?, timer_duration = ?, timer_remaining = NULL, status = ? WHERE load_id = ?");
            $upd->bind_param("sisi", $end, $duration_secs, $new_status, $load_id);
            $upd->execute();

            $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name started a $mins min timer for $bag_label. Status: $new_status.')");
        } elseif ($action === 'pause_timer') {
            if (!empty($bag['timer_end'])) {
                $end_time = strtotime($bag['timer_end']);
                $remaining = max(0, $end_time - time());

                $upd = $conn->prepare("UPDATE `Process_Load` SET timer_end = NULL, timer_remaining = ? WHERE load_id = ?");
                $upd->bind_param("ii", $remaining, $load_id);
                $upd->execute();

                $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name paused the timer for $bag_label.')");
            }
        } elseif ($action === 'resume_timer') {
            if ($bag['timer_remaining'] !== null) {
                $rem = intval($bag['timer_remaining']);
                $end = date('Y-m-d H:i:s', time() + $rem);

                $upd = $conn->prepare("UPDATE `Process_Load` SET timer_end = ?, timer_remaining = NULL WHERE load_id = ?");
                $upd->bind_param("si", $end, $load_id);
                $upd->execute();

                $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name resumed the timer for $bag_label.')");
            }
        } elseif ($action === 'reset_timer') {
            $upd = $conn->prepare("UPDATE `Process_Load` SET timer_end = NULL, timer_duration = NULL, timer_remaining = NULL WHERE load_id = ?");
            $upd->bind_param("i", $load_id);
            $upd->execute();

            $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name reset the timer for $bag_label.')");
        } elseif ($action === 'next_phase') {
            $next = 'Awaiting Pickup';

            // Fixed logic to match the new UI buttons perfectly
            if ($curr === 'In Queue') $next = 'Folding'; // Only hit if it skipped the machine entirely
            elseif ($curr === 'Washing') $next = $hasD ? 'Drying' : ($hasF ? 'Folding' : 'Awaiting Pickup');
            elseif ($curr === 'Drying') $next = $hasF ? 'Folding' : 'Awaiting Pickup';
            elseif ($curr === 'Folding') $next = 'Awaiting Pickup';

            $upd = $conn->prepare("UPDATE `Process_Load` SET status = ?, timer_end = NULL, timer_duration = NULL, timer_remaining = NULL WHERE load_id = ?");
            $upd->bind_param("si", $next, $load_id);
            $upd->execute();

            $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name moved $bag_label to $next.')");

            // --- Email Completion Report ---
            $not_ready_query = $conn->query("SELECT COUNT(*) as c FROM `Process_Load` WHERE order_id = '$order_id' AND status IN ('Pending Dropoff', 'In Queue', 'Washing', 'Drying', 'Folding')");
            if ($not_ready_query->fetch_assoc()['c'] == 0 && $next === 'Awaiting Pickup') {
                $orderQuery = $conn->query("SELECT email FROM `User` WHERE user_id = (SELECT customer_id FROM `Order` WHERE order_id = '$order_id')");
                if ($orderQuery && $orderQuery->num_rows > 0) {
                    $customerEmail = $orderQuery->fetch_assoc()['email'];
                    if (!empty($customerEmail)) {
                        $shopQuery = $conn->query("SELECT IFNULL(current_closing_time, default_close_time) as close_time FROM `Shop_Status` WHERE status_id = 1");
                        $close_time_formatted = date("g:i A", strtotime($shopQuery->fetch_assoc()['close_time'] ?? '20:00:00'));

                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'sevillaralph1504@gmail.com';
                            $mail->Password   = 'wagc ultm nqrk hnfp';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;

                            $mail->setFrom('sevillaralph1504@gmail.com', 'LABAssistance Support');
                            $mail->addAddress($customerEmail);
                            $mail->isHTML(true);
                            $mail->Subject = "Your Laundry is Ready for Pick-up! (Order #$order_id)";

                            $price = number_format($bag['final_price'], 2);
                            $mail->Body = "
                                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 600px; margin: 0 auto;'>
                                    <h2 style='color: #198754; text-align: center;'>Laundry Ready for Pick-up!</h2>
                                    <p>Hello <strong>" . htmlspecialchars($bag['customer_name']) . "</strong>,</p>
                                    <p>Great news! Your laundry order is completely finished and ready for you to pick up.</p>
                                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                                        <h3 style='margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 5px; color: #333;'>Completion Report</h3>
                                        <p><strong>Order ID:</strong> #{$order_id}</p>
                                        <p><strong>Services Completed:</strong> {$services}</p>
                                        <p><strong>Total Amount Due:</strong> ₱{$price}</p>
                                    </div>
                                    <p>Please visit our shop at your earliest convenience to collect your items. <strong style='color: #dc3545;'>Please note that our shop closes at {$close_time_formatted} today.</strong></p><br>
                                    <p>Thank you for choosing LABAssistance!</p>
                                </div>
                            ";
                            $mail->send();
                            $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', 'Completion report successfully emailed to customer.')");
                        } catch (Exception $e) {
                        }
                    }
                }
            }
        }
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
}

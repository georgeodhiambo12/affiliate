<?php
include 'config.php';
include 'OrderUpdater.php';

$orderUpdater = new OrderUpdater($conn);

// Automatically process pending orders
$orders_query = "SELECT transcation_track_id FROM orders WHERE approved = 'pending'";
$orders_result = mysqli_query($conn, $orders_query);

while ($order = mysqli_fetch_assoc($orders_result)) {
    $orderUpdater->updateOrderStatus($order['transcation_track_id']);
}

// Automatically process pending withdrawals
$withdrawals_query = "SELECT w.id, w.affiliate_id, w.amount, u.earnings 
                      FROM withdrawals_affiliate w 
                      JOIN users_affiliate u ON w.affiliate_id = u.id 
                      WHERE w.status = 'pending'";
$withdrawals_result = mysqli_query($conn, $withdrawals_query);

while ($withdrawal = mysqli_fetch_assoc($withdrawals_result)) {
    $affiliate_id = $withdrawal['affiliate_id'];
    $amount = $withdrawal['amount'];
    $earnings = $withdrawal['earnings'];

    if ($earnings >= $amount) {
        // Approve withdrawal
        $updateWithdrawalSql = "UPDATE withdrawals_affiliate SET status = 'approved' WHERE id = {$withdrawal['id']}";
        $conn->query($updateWithdrawalSql);

        // Deduct the amount from affiliate's earnings
        $updateEarningsSql = "UPDATE users_affiliate SET earnings = earnings - $amount WHERE id = $affiliate_id";
        $conn->query($updateEarningsSql);

        // Send email notification to the affiliate
        $affiliate_query = "SELECT email FROM users_affiliate WHERE id = $affiliate_id";
        $affiliate_result = $conn->query($affiliate_query);
        $affiliate_email = $affiliate_result->fetch_assoc()['email'];

        $subject = "Withdrawal Approved";
        $message = "Your withdrawal request of $amount has been approved and processed.";
        sendNotification($affiliate_email, $subject, $message);
    } else {
        // Deny withdrawal due to insufficient funds
        $updateWithdrawalSql = "UPDATE withdrawals_affiliate SET status = 'denied' WHERE id = {$withdrawal['id']}";
        $conn->query($updateWithdrawalSql);

        // Send email notification to the affiliate
        $affiliate_query = "SELECT email FROM users_affiliate WHERE id = $affiliate_id";
        $affiliate_result = $conn->query($affiliate_query);
        $affiliate_email = $affiliate_result->fetch_assoc()['email'];

        $subject = "Withdrawal Denied";
        $message = "Your withdrawal request of $amount has been denied due to insufficient funds.";
        sendNotification($affiliate_email, $subject, $message);
    }
}

// Function to send notification emails
function sendNotification($email, $subject, $message) {
    $headers = "From: no-reply@tendersoko.com\r\n";
    $headers .= "Reply-To: no-reply@tendersoko.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    mail($email, $subject, $message, $headers);
}

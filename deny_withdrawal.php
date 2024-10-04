<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_id'])) {
    $withdrawal_id = (int) $_POST['withdrawal_id'];

    // Update withdrawal status to denied
    $deny_query = "UPDATE withdrawals_affiliate SET status='denied' WHERE id='$withdrawal_id'";
    mysqli_query($conn, $deny_query);

    $_SESSION['message'] = 'Withdrawal request denied successfully.';
}

header('Location: admin_index.php');
exit();
?>

<?php
session_start();
include 'config.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve the form inputs
    $affiliate_id = mysqli_real_escape_string($conn, $_POST['affiliate_id']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $payment_details = mysqli_real_escape_string($conn, $_POST['payment_details']);

    // Update the payment method and details in the database
    $update_query = "UPDATE users_affiliate SET payment_method='$payment_method', payment_details='$payment_details' WHERE id='$affiliate_id'";

    // Check if the query was successful and redirect appropriately
    if (mysqli_query($conn, $update_query)) {
        // Redirect back to the admin dashboard with a success message
        header('Location: admin_index.php?message=Payment method updated successfully');
    } else {
        // Redirect back to the admin dashboard with an error message
        header('Location: admin_index.php?error=Failed to update payment method');
    }
} else {
    // Redirect if the form wasn't submitted properly
    header('Location: admin_index.php');
}

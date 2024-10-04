<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invitation_id = $_POST['invitation_id'];

    // Fetch invitation details
    $invitation_query = "SELECT supplier_email, supplier_name FROM invitations_affiliate WHERE id='$invitation_id'";
    $invitation_result = mysqli_query($conn, $invitation_query);
    $invitation_row = mysqli_fetch_assoc($invitation_result);
    $supplier_email = $invitation_row['supplier_email'];
    $supplier_name = $invitation_row['supplier_name'];

    // Update the invitation status to rejected
    $update_query = "UPDATE invitations_affiliate SET status='rejected' WHERE id='$invitation_id'";
    if (mysqli_query($conn, $update_query)) {
        // Send rejection email to the supplier
        $subject = "Your Supplier Application Has Been Rejected";
        $message = "Dear $supplier_name,\n\n";
        $message .= "We regret to inform you that your application to become a supplier on TenderSoko has been rejected. We encourage you to review our requirements and reapply in the future.\n\n";
        $message .= "If you have any questions, please contact our support team at info@tendersoko.com.\n\n";
        $message .= "Best regards,\n\n";
        $message .= "The TenderSoko Team";

        $headers = "From: no-reply@tendersoko.com\r\nContent-type: text/plain; charset=UTF-8\r\n";

        if (mail($supplier_email, $subject, $message, $headers)) {
            $_SESSION['message'] = "Invitation rejected successfully and email sent.";
        } else {
            $_SESSION['message'] = "Invitation rejected but failed to send email.";
        }
    } else {
        $_SESSION['error'] = "Failed to reject invitation.";
    }
}

header('Location: admin_index.php');
exit();
?>

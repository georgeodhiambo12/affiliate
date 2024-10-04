<?php
session_start();
include 'config.php';
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invitation_id = $_POST['invitation_id'];

    // Fetch the invitation details
    $invitation_query = "SELECT * FROM suppliers_affiliate WHERE id='$invitation_id'";
    $invitation_result = mysqli_query($conn, $invitation_query);
    if ($invitation_row = mysqli_fetch_assoc($invitation_result)) {
        $affiliate_id = $invitation_row['affiliate_id'];
        $supplier_email = $invitation_row['email'];
        $supplier_name = $invitation_row['name'];

        // Fetch affiliate's email
        $affiliate_query = "SELECT email, first_name FROM users_affiliate WHERE id='$affiliate_id'";
        $affiliate_result = mysqli_query($conn, $affiliate_query);
        $affiliate_row = mysqli_fetch_assoc($affiliate_result);
        $affiliate_email = $affiliate_row['email'];
        $affiliate_name = $affiliate_row['first_name'];

        // Update the supplier status to rejected
        $update_supplier_query = "UPDATE suppliers_affiliate SET status='rejected' WHERE id='$invitation_id'";
        if (mysqli_query($conn, $update_supplier_query)) {
            // Send rejection email to the supplier using SMTP2GO
            $mail = new PHPMailer(true);
            try {
                // SMTP2GO settings
                $mail->isSMTP();
                $mail->Host = 'mail.smtp2go.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'tendersoko'; 
                $mail->Password = 'Barcampivorycoast!'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 443;

                // Email settings for supplier
                $mail->setFrom('no-reply@tendersoko.com', 'TenderSoko');
                $mail->addAddress($supplier_email, $supplier_name);

                $mail->isHTML(true);
                $mail->Subject = "Your Application has been Rejected";
                $mail->Body = "
                    <p>Dear $supplier_name,</p>
                    <p>We regret to inform you that your application has been rejected. Thank you for your interest in TenderSoko.</p>
                    <p>Best regards,<br>The TenderSoko Team</p>
                ";
                $mail->send();

                // Send rejection email to the affiliate
                $mail->clearAddresses(); // Clear previous recipient data
                $mail->addAddress($affiliate_email, $affiliate_name);
                $mail->Subject = "Your Referral has been Rejected";
                $mail->Body = "
                    <p>Dear $affiliate_name,</p>
                    <p>Unfortunately, the supplier you referred, $supplier_name, has been rejected.</p>
                    <p>Best regards,<br>The TenderSoko Team</p>
                ";
                $mail->send();

                // Redirect back to admin dashboard with success message
                header('Location: admin_index.php?message=Supplier rejected successfully');
                exit();
            } catch (Exception $e) {
                error_log("Failed to send email: {$mail->ErrorInfo}");
                header('Location: admin_index.php?error=Supplier rejected but failed to send email');
                exit();
            }
        } else {
            // Redirect back to admin dashboard with error message if update failed
            error_log("Failed to update supplier status: " . mysqli_error($conn));
            header('Location: admin_index.php?error=Failed to update supplier status');
            exit();
        }
    } else {
        // Redirect back to admin dashboard with error message if invitation not found
        error_log("Invitation not found with ID $invitation_id");
        header('Location: admin_index.php?error=Invitation not found');
        exit();
    }
} else {
    // Redirect back to admin dashboard with error message if not POST request
    header('Location: admin_index.php?error=Invalid request');
    exit();
}
?>

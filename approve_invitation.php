<?php
session_start();
include 'config.php';
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['invitation_id']) && !empty($_POST['invitation_id'])) {
        $invitation_id = $_POST['invitation_id'];

        // Fetch the invitation details
        $invitation_query = "SELECT * FROM invitations_affiliate WHERE id='$invitation_id'";
        $invitation_result = mysqli_query($conn, $invitation_query);
        if ($invitation_row = mysqli_fetch_assoc($invitation_result)) {
            $affiliate_id = $invitation_row['affiliate_id'];
            $supplier_email = $invitation_row['supplier_email'];
            $supplier_name = $invitation_row['supplier_name'];

            // Fetch affiliate's details
            $affiliate_query = "SELECT email, first_name FROM users_affiliate WHERE id='$affiliate_id'";
            $affiliate_result = mysqli_query($conn, $affiliate_query);
            $affiliate_row = mysqli_fetch_assoc($affiliate_result);
            $affiliate_email = $affiliate_row['email'];
            $affiliate_name = $affiliate_row['first_name'];

            // Move the invitation to the suppliers_affiliate table and update status
            $supplier_insert_query = "INSERT INTO suppliers_affiliate (affiliate_id, email, name, phone, status, referral_code, registration_date) 
                                      SELECT affiliate_id, supplier_email, supplier_name, supplier_phone, 'active', referral_code, NOW() 
                                      FROM invitations_affiliate WHERE id='$invitation_id'";

            if (mysqli_query($conn, $supplier_insert_query)) {
                // Update the invitation status to approved
                $update_invitation_query = "UPDATE invitations_affiliate SET status='approved' WHERE id='$invitation_id'";
                mysqli_query($conn, $update_invitation_query);

                // Send approval email to the supplier using SMTP2GO
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

                    // Supplier email settings
                    $mail->setFrom('no-reply@tendersoko.com', 'TenderSoko');
                    $mail->addAddress($supplier_email, $supplier_name);

                    $mail->isHTML(true);
                    $mail->Subject = "Your Application has been Approved!";
                    $mail->Body = "
                        <p>Dear $supplier_name,</p>
                        <p>Congratulations! Your application has been approved. Welcome to TenderSoko.</p>
                    ";

                    $mail->send();

                    // Send notification email to the affiliate
                    $mail->clearAddresses();
                    $mail->addAddress($affiliate_email, $affiliate_name);
                    $mail->Subject = "Your Referral has been Approved!";
                    $mail->Body = "
                        <p>Dear $affiliate_name,</p>
                        <p>Good news! The supplier you referred, $supplier_name, has been approved.</p>
                    ";

                    $mail->send();

                    // Redirect back to admin dashboard with success message
                    header('Location: admin_index.php?message=Supplier approved successfully');
                    exit();
                } catch (Exception $e) {
                    error_log("Failed to send email: {$mail->ErrorInfo}");
                    header('Location: admin_index.php?error=Supplier approved but failed to send emails');
                    exit();
                }
            } else {
                error_log("Failed to insert supplier into suppliers_affiliate table: " . mysqli_error($conn));
                header('Location: admin_index.php?error=Failed to approve supplier');
                exit();
            }
        } else {
            error_log("Invitation not found with ID $invitation_id");
            header('Location: admin_index.php?error=Invitation not found');
            exit();
        }
    } else {
        error_log("Invitation ID is missing in POST data");
        header('Location: admin_index.php?error=Invalid request');
        exit();
    }
} else {
    error_log("Invalid request method");
    header('Location: admin_index.php?error=Invalid request method');
    exit();
}
?>

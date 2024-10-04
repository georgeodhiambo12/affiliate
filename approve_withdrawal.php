<?php
session_start();
include 'config.php';
include 'mpesa_payment.php';
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_id'])) {
    $withdrawal_id = (int) $_POST['withdrawal_id'];

    // Fetch withdrawal details
    $withdrawal_query = "SELECT w.*, u.mpesa_number, u.email, u.first_name FROM withdrawals_affiliate w JOIN users_affiliate u ON w.affiliate_id = u.id WHERE w.id='$withdrawal_id'";
    $withdrawal_result = mysqli_query($conn, $withdrawal_query);
    $withdrawal = mysqli_fetch_assoc($withdrawal_result);

    if ($withdrawal && $withdrawal['status'] === 'pending') {
        // Process payment through M-PESA
        $payment_response = mpesaPayment($withdrawal['mpesa_number'], $withdrawal['amount'], $withdrawal_id);

        if ($payment_response['ResponseCode'] == '0') {
            // Update withdrawal status to approved
            $approve_query = "UPDATE withdrawals_affiliate SET status='approved', approval_date=NOW() WHERE id='$withdrawal_id'";
            mysqli_query($conn, $approve_query);

            // Send approval email to the affiliate using SMTP2GO
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

                // Email settings
                $mail->setFrom('no-reply@tendersoko.com', 'TenderSoko');
                $mail->addAddress($withdrawal['email'], $withdrawal['first_name']);

                $mail->isHTML(true);
                $mail->Subject = "Your Withdrawal Request Has Been Approved!";
                $mail->Body = "
                    <p>Dear {$withdrawal['first_name']},</p>
                    <p>Your withdrawal request of KES {$withdrawal['amount']} has been approved and processed successfully via M-PESA.</p>
                    <p>Thank you for being a part of the TenderSoko community.</p>
                    <p>Best regards,<br>The TenderSoko Team</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                error_log("Failed to send email: {$mail->ErrorInfo}");
            }

            $_SESSION['message'] = 'Withdrawal request approved and processed successfully.';
        } else {
            // Send failure notification email
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
                
                // Email settings
                $mail->setFrom('no-reply@tendersoko.com', 'TenderSoko');
                $mail->addAddress($withdrawal['email'], $withdrawal['first_name']);

                $mail->isHTML(true);
                $mail->Subject = "Withdrawal Request Failed";
                $mail->Body = "
                    <p>Dear {$withdrawal['first_name']},</p>
                    <p>Unfortunately, your withdrawal request of KES {$withdrawal['amount']} could not be processed due to the following reason: {$payment_response['ResponseDescription']}.</p>
                    <p>Please contact support for further assistance.</p>
                    <p>Best regards,<br>The TenderSoko Team</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                error_log("Failed to send email: {$mail->ErrorInfo}");
            }

            $_SESSION['error'] = 'M-PESA payment failed: ' . $payment_response['ResponseDescription'];
        }
    } else {
        $_SESSION['error'] = 'Invalid withdrawal request.';
    }
}

header('Location: admin_index.php');
exit();
?>

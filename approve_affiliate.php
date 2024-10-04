<?php
session_start();
include 'config.php';
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['affiliate_id'])) {
        // Approving an affiliate
        $affiliate_id = $_POST['affiliate_id'];

        // Fetch affiliate details
        $affiliate_query = "SELECT email, first_name FROM users_affiliate WHERE id='$affiliate_id'";
        $affiliate_result = mysqli_query($conn, $affiliate_query);
        $affiliate_row = mysqli_fetch_assoc($affiliate_result);

        if ($affiliate_row) {
            $affiliate_email = $affiliate_row['email'];
            $affiliate_name = $affiliate_row['first_name'];

            $update_query = "UPDATE users_affiliate SET affiliate_status='approved' WHERE id='$affiliate_id'";
            if (mysqli_query($conn, $update_query)) {
                // Send approval email to the affiliate using SMTP2GO
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'mail.smtp2go.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'tendersoko'; 
                    $mail->Password = 'Barcampivorycoast!'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 443;

                    // Recipients
                    $mail->setFrom('no-reply@tendersoko.com', 'TenderSoko');
                    $mail->addAddress($affiliate_email, $affiliate_name);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Congratulations and Welcome to the TenderSoko Affiliate Program!";
                    $mail->Body = "
                        <p>Dear $affiliate_name,</p>
                        <p>Congratulations! We are thrilled to inform you that your application to join the TenderSoko Affiliate Program has been approved. Welcome to our growing community of passionate affiliates.</p>
                        <p>We are excited to have you on board and are confident that your journey with us will be both rewarding and fulfilling. As an affiliate, you now have the opportunity to share TenderSoko's innovative platform with your audience, while earning attractive commissions for your efforts.</p>
                        <p>To get started, here are a few steps to help you make the most out of your affiliate partnership:</p>
                        <ul>
                            <li><strong>Access Your Affiliate Dashboard:</strong> Log in to your account at <a href='https://tendersoko.com/affiliate/login.php'>TenderSoko Affiliate Dashboard</a> to explore your personalized affiliate dashboard. Here, you can track your performance, access marketing materials, and monitor your earnings in real-time.</li>
                            <li><strong>Utilize Marketing Tools:</strong> We have a range of promotional tools available to help you succeed, including banners, links, and content suggestions. Be sure to utilize these resources to effectively promote TenderSoko and maximize your commissions.</li>
                            <li><strong>Connect with Us:</strong> If you have any questions or need assistance, our support team is here to help. Don't hesitate to reach out to us at <a href='mailto:info@tendersoko.com'>info@tendersoko.com</a>. We're committed to your success and are eager to assist you in any way we can.</li>
                            <li><strong>Stay Updated:</strong> Follow us on our social media channels and subscribe to our newsletter to stay informed about the latest updates, promotions, and tips for affiliates. Staying connected will help you stay ahead in the affiliate game.</li>
                        </ul>
                        <p>We believe that your passion and drive will make a significant impact in promoting TenderSoko, and we look forward to seeing all the great things you will accomplish as a member of our affiliate program.</p>
                        <p>Once again, welcome to the TenderSoko family! We're excited to have you with us and can't wait to see the amazing results you'll achieve.</p>
                        <p>Warm regards,</p>
                        <p>The TenderSoko Team</p>
                    ";

                    $mail->send();
                    header('Location: admin_index.php?message=Affiliate approved successfully and email sent');
                } catch (Exception $e) {
                    error_log("Failed to send approval email to $affiliate_email. Mailer Error: {$mail->ErrorInfo}");
                    header('Location: admin_index.php?error=Affiliate approved but failed to send email');
                }
            } else {
                error_log("Failed to update affiliate status for ID $affiliate_id: " . mysqli_error($conn));
                header('Location: admin_index.php?error=Failed to approve affiliate');
            }
        } else {
            error_log("Affiliate not found with ID $affiliate_id");
            header('Location: admin_index.php?error=Affiliate not found');
        }
    } else {
        error_log("Affiliate ID is missing in POST data");
        header('Location: admin_index.php?error=Invalid request');
    }
} else {
    error_log("Invalid request method");
    header('Location: admin_index.php?error=Invalid request method');
}
?>

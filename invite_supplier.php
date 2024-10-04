<?php
session_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Affiliate') {
    error_log('Session error: user_id or account_type not set properly');
    header('Location: login.php');
    exit();
}

$affiliate_id = $_SESSION['user_id'];

// Fetch affiliate's name
$name_query = "SELECT first_name, last_name FROM users_affiliate WHERE id='$affiliate_id'";
$name_result = mysqli_query($conn, $name_query);

if ($name_result && mysqli_num_rows($name_result) > 0) {
    $name_row = mysqli_fetch_assoc($name_result);
    $affiliate_name = $name_row['first_name'] . ' ' . $name_row['last_name'];
} else {
    error_log("Failed to fetch affiliate's name for user_id: $affiliate_id. Error: " . mysqli_error($conn));
    $affiliate_name = 'Affiliate'; // Default name if query fails
}

// Fetch affiliate's referral code
$referral_query = "SELECT referral_code FROM users_affiliate WHERE id='$affiliate_id'";
$referral_result = mysqli_query($conn, $referral_query);

if ($referral_result && mysqli_num_rows($referral_result) > 0) {
    $referral_row = mysqli_fetch_assoc($referral_result);
    $referral_code = $referral_row['referral_code'];
    
    if (empty($referral_code)) {
        error_log("Referral code is empty for user_id: $affiliate_id");
    }
} else {
    error_log("Failed to fetch referral code for user_id: $affiliate_id. Error: " . mysqli_error($conn));
    $referral_code = '';
}

$supplier_email = isset($_POST['supplier_email']) ? mysqli_real_escape_string($conn, $_POST['supplier_email']) : '';
$supplier_name = isset($_POST['supplier_name']) ? mysqli_real_escape_string($conn, $_POST['supplier_name']) : '';
$supplier_phone = isset($_POST['supplier_phone']) ? mysqli_real_escape_string($conn, $_POST['supplier_phone']) : '';
$personalized_message = isset($_POST['personalized_message']) ? mysqli_real_escape_string($conn, $_POST['personalized_message']) : '';

$errors = [];
$successes = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($supplier_email) || empty($supplier_name) || empty($supplier_phone)) {
        $errors[] = "2. All fields are required.";
    } elseif (!preg_match('/^\+\d{1,3}\d{7,14}$/', $supplier_phone)) {
        $errors[] = "3. The phone number must include a country code.";
    } elseif (str_word_count($supplier_name) < 2) {
        $errors[] = "4. The supplier name must contain at least two words.";
    } else {
        // Check if the supplier has been invited in the last year
        $check_invitation_query = "SELECT last_invited_at FROM invitations_affiliate 
                                   WHERE supplier_email='$supplier_email' 
                                   AND affiliate_id='$affiliate_id' 
                                   ORDER BY last_invited_at DESC LIMIT 1";
        $check_invitation_result = mysqli_query($conn, $check_invitation_query);
        
        if ($check_invitation_result && mysqli_num_rows($check_invitation_result) > 0) {
            $invitation_row = mysqli_fetch_assoc($check_invitation_result);
            $last_invited_at = strtotime($invitation_row['last_invited_at']);
            $one_year_ago = strtotime('-1 year');

            if ($last_invited_at > $one_year_ago) {
                $errors[] = count($errors) + 5 . ". Supplier $supplier_name ($supplier_email) has already been invited within the last year.";
                // Skip sending invitation and storing in database
                goto skip_invitation;
            }
        }

        // Proceed to send the invitation
        if (empty($errors)) {
            $invitation_query = "INSERT INTO invitations_affiliate (affiliate_id, supplier_email, supplier_name, supplier_phone, referral_code, status, created_at, last_invited_at) 
                                 VALUES ('$affiliate_id', '$supplier_email', '$supplier_name', '$supplier_phone', '$referral_code', 'no', NOW(), NOW())";
            if (mysqli_query($conn, $invitation_query)) {
                $invite_link = "https://www.tendersoko.com/register?email=$supplier_email&referral_code=$referral_code";
                $subject = "[TenderSoko] You're invited to join our platform";

                // Check if the user has provided a personalized message
                if (!empty($personalized_message)) {
                    // Use the personalized message
                    $message = "<p>Hello $supplier_name,</p>";
                    $message .= "<p>$affiliate_name has sent you a personalized message:</p>";
                    $message .= "<blockquote style='border-left:2px solid #ccc;padding-left:10px;color:#555;'>$personalized_message</blockquote>";
                    $message .= "<p>Use the following link to register and enjoy a 10% discount:</p>";
                    $message .= "<p><a href='$invite_link' style='color:#003C71;text-decoration:none;'>$invite_link</a></p>";
                    $message .= "<p>Regards,<br>TenderSoko Team</p>";
                } else {
                    // Use the default system-generated message
                    $message = "<p>Hello $supplier_name,</p>";
                    $message .= "<p>You have been invited to join our platform by $affiliate_name. Please use the following link to register:</p>";
                    $message .= "<p>You will get a 10% discount upon successful subscription.</p>";
                    $message .= "<p><a href='$invite_link' style='color:#003C71;text-decoration:none;'>$invite_link</a></p>";
                    $message .= "<p>Regards,<br>TenderSoko Team</p>";
                }

                // Send the email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'mail.smtp2go.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'tendersoko';
                    $mail->Password   = 'Barcampivorycoast!';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 443;

                    $mail->setFrom('no-reply@tendersoko.com', 'TenderSoko');
                    $mail->addAddress($supplier_email, $supplier_name);
                    $mail->addAddress('info@tendersoko.com'); // Info email added directly here

                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body    = $message;

                    $mail->send();
                    $successes[] = (count($successes) + 1) . ". Invitation link has been sent successfully to $supplier_name ($supplier_email).";
                } catch (Exception $e) {
                    error_log("Failed to send invitation email to $supplier_email. Mailer Error: {$mail->ErrorInfo}");
                    $errors[] = (count($errors) + 1) . ". Failed to send invitation email to $supplier_name ($supplier_email).";
                }
            } else {
                error_log("Error: " . mysqli_error($conn));
                $errors[] = (count($errors) + 1) . ". Database error for supplier $supplier_name ($supplier_email).";
            }
        }
    }

    skip_invitation:

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
    if (!empty($successes)) {
        $_SESSION['message'] = implode('<br>', $successes);
    }

    header('Location: affiliate_index.php');
    exit();
}
?>

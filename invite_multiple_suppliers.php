<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include 'config.php';

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $sheetData = $sheet->toArray();

    array_shift($sheetData); // Skip header

    $affiliate_id = $_SESSION['user_id'];

    // Fetch the affiliate's first name and last name from the database
    $name_query = "SELECT first_name, last_name FROM users_affiliate WHERE id='$affiliate_id'";
    $name_result = mysqli_query($conn, $name_query);

    if ($name_result && mysqli_num_rows($name_result) > 0) {
        $name_row = mysqli_fetch_assoc($name_result);
        $affiliate_name = $name_row['first_name'] . ' ' . $name_row['last_name'];
    } else {
        error_log("Failed to fetch affiliate's name for user_id: $affiliate_id. Error: " . mysqli_error($conn));
        $affiliate_name = 'Affiliate'; // Default name if query fails
    }

    // Fetch the referral code from the affiliate's record
    $referral_query = "SELECT referral_code FROM users_affiliate WHERE id='$affiliate_id'";
    $referral_result = mysqli_query($conn, $referral_query);
    $referral_row = mysqli_fetch_assoc($referral_result);
    $referral_code = $referral_row['referral_code'];

    // Fetch personalized message, if provided
    $personalized_message = isset($_POST['personalized_message']) ? mysqli_real_escape_string($conn, $_POST['personalized_message']) : '';

    $successes = [];
    $errors = [];
    
    $batch_size = 50; // Number of emails to send per batch
    $batch_counter = 0; // Counter to track emails sent

    foreach ($sheetData as $index => $row) {
        $supplier_email = mysqli_real_escape_string($conn, $row[0]);
        $supplier_first_name = mysqli_real_escape_string($conn, $row[1]);
        $supplier_phone = mysqli_real_escape_string($conn, $row[2]);

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
                $errors[] = ($index + 1) . ". Supplier $supplier_first_name ($supplier_email) has already been invited within the last year.";
                // Skip sending invitation and storing in database
                continue;
            }
        }

        // Proceed to send the invitation
        $invitation_query = "INSERT INTO invitations_affiliate (affiliate_id, supplier_email, supplier_name, supplier_phone, referral_code, status, created_at, last_invited_at) 
                             VALUES ('$affiliate_id', '$supplier_email', '$supplier_first_name', '$supplier_phone', '$referral_code', 'no', NOW(), NOW())";
        if (mysqli_query($conn, $invitation_query)) {
            $registration_link = "https://www.tendersoko.com/register?referral_code=" . urlencode($referral_code) . "&email=" . urlencode($supplier_email);

            $subject = "[TenderSoko] You're invited to join our affiliate program!";
            
            // Check if a personalized message is provided, use it if so
            if (!empty($personalized_message)) {
                $message = "<p>Hello $supplier_first_name,</p>";
                $message .= "<p>$affiliate_name has sent you a personalized message:</p>";
                $message .= "<blockquote style='border-left:2px solid #ccc;padding-left:10px;color:#555;'>$personalized_message</blockquote>";
                $message .= "<p>Please click the following link to sign up and enjoy a 10% discount:</p>";
                $message .= "<p><a href='$registration_link' style='color:#003C71;text-decoration:none;'>$registration_link</a></p>";
                $message .= "<p>Regards,<br>TenderSoko Team</p>";
            } else {
                // Use the default message
                $message = "<p>Hello $supplier_first_name,</p>";
                $message .= "<p>You have been invited by $affiliate_name to join our affiliate program.</p>";
                $message .= "<p>Please click the following link to sign up:</p>";
                $message .= "<p>You will get a 10% discount upon successful subscription.</p>";
                $message .= "<p><a href='$registration_link' style='color:#003C71;text-decoration:none;'>$registration_link</a></p>";
                $message .= "<p>Regards,<br>TenderSoko Team</p>";
            }

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
                $mail->addAddress($supplier_email, $supplier_first_name);  // The supplier email
                $mail->addAddress('info@tendersoko.com');  // Info email added as direct recipient

                $mail->isHTML(true);  // Ensure email is sent as HTML
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
                $successes[] = ($index + 1) . ". Invitation sent successfully to $supplier_first_name ($supplier_email).";
            } catch (Exception $e) {
                error_log("Failed to send invitation email to $supplier_email. Mailer Error: {$mail->ErrorInfo}");
                $errors[] = ($index + 1) . ". Failed to send invitation to $supplier_first_name ($supplier_email).";
            }

            // Increment the batch counter
            $batch_counter++;

            // Check if we have reached the batch limit
            if ($batch_counter % $batch_size === 0) {
                // Pause for 10 seconds before sending the next batch
                sleep(10);
            }
        } else {
            error_log("Failed to insert into invitations_affiliate. Error: " . mysqli_error($conn));
            $errors[] = ($index + 1) . ". Database error for supplier $supplier_first_name ($supplier_email).";
        }
    }

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

<?php
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

$message = ''; // Initialize the message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $country = $_POST['country'];
    $phone_number = $_POST['phone_number'];
    $social_media = $_POST['social_media'];
    $verified = 0; // Assuming verification is required
    $verification_token = bin2hex(random_bytes(16));

    // Set the default role to recruiter (affiliate)
    $role = 'affiliate';

    // Check if the email or phone number already exists
    $check_query = "SELECT * FROM users_affiliate WHERE email='$email' OR phone_number='$phone_number'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "A user with this email or phone number already exists.";
    } else {
        $sql = "INSERT INTO users_affiliate (first_name, last_name, email, password, country, phone_number, social_media, role, verified, verification_token) 
                VALUES ('$first_name', '$last_name', '$email', '$password', '$country', '$phone_number', '$social_media', '$role', '$verified', '$verification_token')";
        if (mysqli_query($conn, $sql)) {
            // Send verification email using PHPMailer
            $subject = "Verify Your Email Address";
            $message_content = "Please click the following link to verify your email: ";
            $message_content .= "https://tendersoko.com/affiliate/verify.php?token=$verification_token";

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
                $mail->addAddress($email, "$first_name $last_name");

                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body    = $message_content;

                $mail->send();
                $message = "Registration successful! Please check your email to verify your account.";
            } catch (Exception $e) {
                error_log("Failed to send verification email to $email. Mailer Error: {$mail->ErrorInfo}");
                $message = "Registration successful, but failed to send verification email.";
            }
        } else {
            $message = "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}
?>
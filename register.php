<?php
// register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash(mysqli_real_escape_string($conn, $_POST['password']), PASSWORD_BCRYPT);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $account_type = mysqli_real_escape_string($conn, $_POST['account_type']);
    $social_media = mysqli_real_escape_string($conn, $_POST['social_media']);

    // Check if the email already exists in the database
    $check_email_sql = "SELECT * FROM users_affiliate WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email_sql);

    if (mysqli_num_rows($result) > 0) {
        // Email already exists
        echo "<div style='text-align: center; padding: 20px;'>This email is already registered. Please use a different email or log in.</div>";
    } else {
        // Generate a verification token
        $verification_token = bin2hex(random_bytes(50));

        // Generate a referral code
        function generateReferralCode($length = 8) {
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $referralCode = '';
            for ($i = 0; $i < $length; $i++) {
                $referralCode .= $characters[rand(0, $charactersLength - 1)];
            }
            return $referralCode;
        }
        $referral_code = generateReferralCode();

        // Insert user into the database with a pending status
        $sql = "INSERT INTO users_affiliate (email, password, first_name, last_name, verification_token, verified, referral_code, affiliate_status, country, phone_number, account_type, social_media)
                VALUES ('$email', '$password', '$first_name', '$last_name', '$verification_token', 0, '$referral_code', 'pending', '$country', '$phone_number', '$account_type', '$social_media')";
        
        if (mysqli_query($conn, $sql)) {
            // Send the verification email using PHPMailer and SMTP2GO
            $verification_link = "https://tendersoko.com/affiliate/verify_email.php?token=$verification_token";
            $subject = "Email Verification";
            $message = "Hello $first_name,\n\nTo verify your email, please click the following link:\n$verification_link\n\nIf you did not request this, please ignore this email.";

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
                
                // Add the user's email and the admin's email to the "To" field
                $mail->addAddress($email, "$first_name $last_name");
                $mail->addAddress('info@tendersoko.com');  // Add the admin's email here

                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
                echo "<div style='text-align: center; padding: 20px;'>
                        <p>Registration successful! A verification link has been sent to your email. Your account is pending approval.</p>
                        <a href='login.php' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: #fff; text-decoration: none; border-radius: 5px;'>Login</a>
                      </div>";
            } catch (Exception $e) {
                echo "<div style='text-align: center; padding: 20px;'>Failed to send verification email.</div>";
                error_log("Failed to send verification email to $email. Mailer Error: {$mail->ErrorInfo}");
            }
        } else {
            echo "<div style='text-align: center; padding: 20px;'>Error: " . mysqli_error($conn) . "</div>";
            error_log("Error inserting user: " . mysqli_error($conn));
        }
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

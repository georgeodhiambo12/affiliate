<?php
session_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $two_factor_code = mysqli_real_escape_string($conn, $_POST['two_factor_code']);
    $user_id = $_SESSION['user_id'];

    // Query to check the 2FA code and expiration
    $sql = "SELECT * FROM users_affiliate WHERE id='$user_id' AND two_factor_code='$two_factor_code' AND reset_expiration > NOW()";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // If code is correct and not expired, log in the user
        header('Location: affiliate_index.php');
        exit();
    } else {
        $error_message = "Invalid or expired 2FA code.";

        // Send an email notification about the failed 2FA attempt
        $email = $user['email'];
        $first_name = $user['first_name'];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.smtp2go.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tendersoko';  // Your SMTP2GO username
            $mail->Password   = 'Barcampivorycoast!';  // Your SMTP2GO password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 443;

            $mail->setFrom('no-reply@tendersoko.com', 'TenderSoko');
            $mail->addAddress($email, "$first_name");

            $mail->isHTML(false);
            $mail->Subject = "Failed 2FA Attempt";
            $mail->Body    = "Hello $first_name,\n\nThere was a failed attempt to log in to your account with the wrong 2FA code. If this was not you, please secure your account immediately.";

            if($mail->send()) {
                error_log("Failed 2FA email sent to $email.");
            } else {
                error_log("Failed to send 2FA email to $email. Mailer Error: " . $mail->ErrorInfo);
            }
        } catch (Exception $e) {
            error_log("Failed to send 2FA email to $email. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify 2FA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f8fa;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #218838;
        }

        .link {
            display: block;
            margin-top: 15px;
            color: #007bff;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
        }

        .link:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Two-Factor Authentication</h2>
        <?php if (isset($error_message)) { echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>'; } ?>
        
        <form id="verify-2fa-form" action="verify_2fa.php" method="POST" class="form active">
            <div class="form-group">
                <label for="two_factor_code">Enter 2FA Code</label>
                <input type="text" id="two_factor_code" name="two_factor_code" required>
            </div>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>

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

$supplier_email = $_GET['email'] ?? '';
$referral_code = $_GET['referral_code'] ?? '';

// Check if the email and referral code are valid
if (empty($supplier_email) || empty($referral_code)) {
    die("Invalid referral link.");
}

// Validate the referral code and email combination from the `suppliers_affiliate` table
$supplier_query = "SELECT * FROM suppliers_affiliate WHERE email = '$supplier_email' AND referral_code = '$referral_code' AND status = 'pending'";
$supplier_result = mysqli_query($conn, $supplier_query);

if (mysqli_num_rows($supplier_result) == 0) {
    die("Invalid or expired referral link.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Form validation
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $update_query = "UPDATE suppliers_affiliate 
                         SET name = '$name', phone = '$phone', password = '$hashed_password', status = 'active' 
                         WHERE email = '$supplier_email' AND referral_code = '$referral_code'";

        if (mysqli_query($conn, $update_query)) {
            // Send confirmation email
            $subject = "Registration Successful";
            $message = "Hello $name,\n\nYour registration as a supplier has been successful. Your account is now active.\n\nRegards,\nTenderSoko Team";

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
                $mail->addAddress($supplier_email, $name);

                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
                $success = "Registration successful. Your account is now active, and a confirmation email has been sent.";
            } catch (Exception $e) {
                error_log("Failed to send confirmation email to $supplier_email. Mailer Error: {$mail->ErrorInfo}");
                $error = "Registration successful, but failed to send confirmation email.";
            }
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as a Supplier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            text-align: left;
            color: #666;
            font-weight: bold;
        }
        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
        p {
            color: red;
            font-weight: bold;
        }
        a {
            color: #28a745;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register as a Supplier</h1>

        <?php if (!empty($error)): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php else: ?>
        <form action="" method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" required>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($supplier_email); ?>" readonly required>
            <label for="phone">Phone Number:</label>
            <input type="tel" name="phone" id="phone" required>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <button type="submit">Register</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>

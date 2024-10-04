<?php
session_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

if (!isset($_SESSION['account_closed'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's first and last name from the database
$user_query = "SELECT first_name, last_name FROM users_affiliate WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

$first_name = $user_data['first_name'];
$last_name = $user_data['last_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    // Store the reason in the database
    $insert_reason_query = "INSERT INTO exit_reasons (user_id, reason, created_at) VALUES ('$user_id', '$reason', NOW())";
    if (!mysqli_query($conn, $insert_reason_query)) {
        error_log("Failed to store reason: " . mysqli_error($conn));
    }

    // Send the reason via email
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
        $mail->addAddress('info@tendersoko.com', 'TenderSoko Info');

        $mail->isHTML(true);
        $mail->Subject = 'User Exit Feedback';
        $mail->Body    = "<h2>User ID: $user_id</h2><p><strong>Reason for Leaving:</strong><br>$reason</p>";
        
        $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send exit feedback email. Error: {$mail->ErrorInfo}");
    }

    // Simulate account closure by unsetting session and destroying it
    session_unset();
    session_destroy();

    $submitted = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goodbye</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            text-align: center;
            padding: 50px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-top: 50px;
            max-width: 500px;
        }
        .container h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .container p {
            color: #666;
            margin-bottom: 20px;
        }
        .container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .container textarea {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 100%;
            height: 100px;
            resize: none;
        }
        .container button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .container button:hover {
            background-color: #45a049;
        }
        .container .goodbye-message {
            margin-top: 30px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!isset($submitted)): ?>
            <h1>As TenderSoko, we are sad to see you go, <?php echo htmlspecialchars($first_name); ?>...</h1>
            <p>Please let us know why you are leaving. Your feedback is valuable to us.</p>
            <form method="POST">
                <textarea name="reason" placeholder="I have no reason..." required></textarea>
                <button type="submit">Submit</button>
            </form>
        <?php else: ?>
            <h1>Goodbye, <?php echo htmlspecialchars($first_name); ?>!</h1>
            <p>Thank you for being a part of our platform. Your account has been successfully closed.</p>
            <p class="goodbye-message">We are sad to see you go, <?php echo htmlspecialchars($first_name); ?>. If you ever change your mind, we’ll be here to welcome you back.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_otp = mysqli_real_escape_string($conn, $_POST['otp']);
    $user_id = $_SESSION['user_id'];

    // Retrieve the OTP and expiration from the database
    $query = "SELECT otp, otp_expiration FROM users_affiliate WHERE id='$user_id'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $current_time = date('Y-m-d H:i:s');

        if ($user['otp'] == $input_otp && $current_time <= $user['otp_expiration']) {
            // OTP is correct and not expired, proceed to log the user in
            header('Location: affiliate_index.php');
            exit();
        } else {
            // OTP is incorrect or expired
            $error_message = "Invalid or expired OTP.";
        }
    } else {
        $error_message = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
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
        <h2>OTP Verification</h2>
        <?php if (isset($error_message)) { echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>'; } ?>

        <form action="otp_verification.php" method="POST">
            <div class="form-group">
                <label for="otp">Enter OTP:</label>
                <input type="text" name="otp" id="otp" required>
            </div>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>

<?php
// forgot_password.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the user exists
    $sql = "SELECT * FROM users_affiliate WHERE email='$email'";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Generate a reset token
        $reset_token = bin2hex(random_bytes(50));

        // Store the reset token in the database
        $sql = "UPDATE users_affiliate SET reset_token='$reset_token' WHERE email='$email'";
        if (mysqli_query($conn, $sql)) {
            // Log for debugging
            error_log("Token stored: $reset_token");

            // Send the reset email
            $reset_link = "https://tendersoko.com/affiliate/reset_password.php?token=$reset_token";
            $subject = "Password Reset Request";
            $message = "To reset your password, click the following link: $reset_link";
            $headers = "From: no-reply@tendersoko.com";

            if (mail($email, $subject, $message, $headers)) {
                echo "Password reset link has been sent to your email.";
            } else {
                echo "Failed to send password reset email.";
            }
        } else {
            die("Failed to generate password reset token: " . mysqli_error($conn));
        }
    } else {
        echo "No account found with that email address.";
    }
}
?>

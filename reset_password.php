<?php
// reset_password.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['token'])) {
    $token = $_GET['token'];

    // Log for debugging
    error_log("Token: $token");

    // Check if the token is valid
    $sql = "SELECT * FROM users_affiliate WHERE reset_token='$token'";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Display reset password form
        echo "
        <form method='POST' action='reset_password.php'>
            <input type='hidden' name='token' value='$token'>
            <label for='password'>New Password:</label>
            <input type='password' name='password' required>
            <button type='submit'>Reset Password</button>
        </form>
        ";
    } else {
        echo "Invalid token.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "UPDATE users_affiliate SET password='$new_password', reset_token=NULL WHERE reset_token='$token'";
    if (mysqli_query($conn, $sql)) {
        echo "Password has been reset successfully.";
    } else {
        die("Failed to reset password: " . mysqli_error($conn));
    }
}
?>

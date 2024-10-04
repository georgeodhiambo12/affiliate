<?php
include 'config.php';

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    $query = "SELECT * FROM users_affiliate WHERE verification_token='$token'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        if ($row['verified'] == 0) {
            $update_query = "UPDATE users_affiliate SET verified=1, verification_token='' WHERE id=" . $row['id'];
            if (mysqli_query($conn, $update_query)) {
                echo "
                <div style='text-align: center; margin-top: 50px;'>
                    <p>Your email has been verified successfully.</p>
                    <a href='login.php' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Login</a>
                </div>
                ";
            } else {
                echo "<div style='text-align: center; margin-top: 50px;'>Failed to verify email. Please try again later.</div>";
            }
        } else {
            echo "<div style='text-align: center; margin-top: 50px;'>Your email has already been verified. You can <a href='login.php'>login</a>.</div>";
        }
    } else {
        echo "<div style='text-align: center; margin-top: 50px;'>Invalid token.</div>";
    }
} else {
    echo "<div style='text-align: center; margin-top: 50px;'>No token provided.</div>";
}
?>

<?php
include 'config.php';

// Admin credentials
$admin_first_name = 'Admin';
$admin_last_name = 'User';
$admin_email = 'info@tendersoko.com';
$admin_password = 'Tenderso2014';
$admin_country = 'Kenya';
$admin_phone_number = '0712345678';
$admin_account_type = 'Admin';
$admin_social_media = 'tendersoko.com';
$admin_role = 'admin';
$admin_affiliate_status = 'approved';
$admin_password_hash = password_hash($admin_password, PASSWORD_BCRYPT);

// Insert admin user into the database
$sql = "INSERT INTO users_affiliate 
    (first_name, last_name, email, password, country, phone_number, account_type, social_media, role, verified, affiliate_status) 
    VALUES 
    ('$admin_first_name', '$admin_last_name', '$admin_email', '$admin_password_hash', '$admin_country', '$admin_phone_number', '$admin_account_type', '$admin_social_media', '$admin_role', 1, '$admin_affiliate_status')";

if (mysqli_query($conn, $sql)) {
    echo "Admin user inserted successfully.";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>

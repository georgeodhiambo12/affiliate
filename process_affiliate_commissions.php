<?php
$conn = new mysqli('localhost', 'tenderso_connect', 'tenderso_2014', 'tenderso_tendersoko');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for new approved orders with a referral code and process commission
$sql = "SELECT * FROM orders WHERE referral_code IS NOT NULL AND status = 'approved'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $referral_code = $row['referral_code'];
    $amount = $row['amount']; // This should be the discounted amount

    // Find the corresponding affiliate by referral code in the users table
    $sql_affiliate = "SELECT id FROM users WHERE referral_code = '$referral_code'";
    $result_affiliate = $conn->query($sql_affiliate);

    if ($affiliate_row = $result_affiliate->fetch_assoc()) {
        $affiliate_id = $affiliate_row['id'];

        // Calculate commission (10% of the amount paid)
        $commission = $amount * 0.10;

        // Credit the affiliate in the users table
        $sql_credit = "UPDATE users SET earnings = earnings + $commission WHERE id = $affiliate_id";
        $conn->query($sql_credit);
    }
}

$conn->close();
?>

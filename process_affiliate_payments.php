<?php
// process_affiliate_payments.php
$conn_affiliate = new mysqli('localhost', 'tenderso_all41', 'Q1yZ,RK4gts_', 'tenderso_affiliate');
$conn_tendersoko = new mysqli('localhost', 'tenderso_connect', 'tenderso_2014', 'tenderso_tendersoko');

if ($conn_affiliate->connect_error) {
    die("Affiliate DB connection failed: " . $conn_affiliate->connect_error);
}
if ($conn_tendersoko->connect_error) {
    die("Tendersoko DB connection failed: " . $conn_tendersoko->connect_error);
}

// Check for approved payments with a referral code and credit affiliates
$sql = "SELECT * FROM orders WHERE referral_code IS NOT NULL AND approved = 'yes'";
$result = $conn_tendersoko->query($sql);

while ($row = $result->fetch_assoc()) {
    $referral_code = $row['referral_code'];
    $amount = $row['amount'];

    // Find the corresponding affiliate by referral code in the tenderso_affiliate database
    $sql_affiliate = "SELECT id FROM users_affiliate WHERE referral_code = '$referral_code'";
    $result_affiliate = $conn_affiliate->query($sql_affiliate);

    if ($affiliate_row = $result_affiliate->fetch_assoc()) {
        $affiliate_id = $affiliate_row['id'];

        // Credit the affiliate in the tenderso_affiliate database
        $sql_credit = "UPDATE users_affiliate SET earnings = earnings + $amount WHERE id = $affiliate_id";
        $conn_affiliate->query($sql_credit);
    }
}

$conn_affiliate->close();
$conn_tendersoko->close();
?>

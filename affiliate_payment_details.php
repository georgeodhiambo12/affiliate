<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['affiliate_id'])) {
    echo "No affiliate selected!";
    exit();
}

$affiliate_id = mysqli_real_escape_string($conn, $_GET['affiliate_id']);

// Fetch payment details of the affiliate
$payment_query = "SELECT * FROM users_affiliate WHERE id='$affiliate_id'";
$payment_result = mysqli_query($conn, $payment_query);

if (mysqli_num_rows($payment_result) == 0) {
    echo "Affiliate not found!";
    exit();
}

$affiliate = mysqli_fetch_assoc($payment_result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Payment Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 1rem 0;
        }
        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 0.5rem;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .back-button, .details-link {
            display: inline-block;
            margin-top: 1rem;
            background-color: #4CAF50;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
        }
        .details-link:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <h1>Payment Details for <?php echo htmlspecialchars($affiliate['first_name'] . ' ' . $affiliate['last_name']); ?></h1>
    </header>
    <main>
        <table>
            <tr>
                <th>Payment Method</th>
                <td><?php echo htmlspecialchars($affiliate['payment_method']); ?></td>
            </tr>
            <tr>
                <th>Payment Details</th>
                <td><?php echo htmlspecialchars($affiliate['payment_details']); ?></td>
            </tr>
            <tr>
                <th>Bank Name</th>
                <td><?php echo htmlspecialchars($affiliate['bank_name']); ?></td>
            </tr>
            <tr>
                <th>Account Number</th>
                <td><?php echo htmlspecialchars($affiliate['bank_account_number']); ?></td>
            </tr>
            <tr>
                <th>SWIFT Code</th>
                <td><?php echo htmlspecialchars($affiliate['swift_code']); ?></td>
            </tr>
            <tr>
                <th>IBAN</th>
                <td><?php echo htmlspecialchars($affiliate['iban']); ?></td>
            </tr>
        </table>

        <!-- Show more details button -->
        <a href="affiliate_details.php?affiliate_id=<?php echo $affiliate['id']; ?>" class="details-link">Show More Details</a>

        <a href="admin_index.php" class="back-button">Go Back</a>
    </main>
</body>
</html>

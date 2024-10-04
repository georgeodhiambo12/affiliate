<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['inviter_id'])) {
    header('Location: admin_index.php');
    exit();
}

$inviter_id = mysqli_real_escape_string($conn, $_GET['inviter_id']);

// Fetch affiliates invited by this user from invitations_affiliate table
$invited_affiliates_query = "
SELECT supplier_email, supplier_name, supplier_phone, referral_code, status, created_at 
FROM invitations_affiliate
WHERE affiliate_id = '$inviter_id'";

$invited_affiliates_result = mysqli_query($conn, $invited_affiliates_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invited Affiliates</title>
    <link rel="stylesheet" href="styles.css">
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
            position: relative;
        }
        main {
            max-width: 1000px;
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
        .back-button {
            margin-top: 1rem;
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Invited Affiliates</h1>
    </header>
    <main>
        <section class="affiliates">
            <h2>Affiliates Invited by User ID: <?php echo htmlspecialchars($inviter_id); ?></h2> <a href="admin_index.php" class="back-button">Back to Admin Dashboard</a>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier Email</th>
                        <th>Supplier Name</th>
                        <th>Supplier Phone</th>
                        <th>Referral Code</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while ($invited_affiliate = mysqli_fetch_assoc($invited_affiliates_result)): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($invited_affiliate['supplier_email'] ?? 'No Email'); ?></td>
                        <td><?php echo htmlspecialchars($invited_affiliate['supplier_name'] ?? 'No Name'); ?></td>
                        <td><?php echo htmlspecialchars($invited_affiliate['supplier_phone'] ?? 'No Phone'); ?></td>
                        <td><?php echo htmlspecialchars($invited_affiliate['referral_code'] ?? 'No Code'); ?></td>
                        <td><?php echo htmlspecialchars($invited_affiliate['status'] ?? 'No Status'); ?></td>
                        <td><?php echo htmlspecialchars($invited_affiliate['created_at'] ?? 'No Date'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>

   

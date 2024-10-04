<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

$affiliate_id = isset($_GET['affiliate_id']) ? intval($_GET['affiliate_id']) : 0;

// Fetch sales data for the affiliate
$sales_data_query = "
SELECT * FROM sales_affiliate WHERE affiliate_id = '$affiliate_id'
";
$sales_data_result = mysqli_query($conn, $sales_data_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Sales Data</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Sales Data for Affiliate</h1>
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </header>
    <main>
        <!-- Sales Data Table -->
        <section class="sales-data">
            <h2>Sales Data</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sale Amount</th>
                        <th>Commission Earned</th>
                        <th>Sale Date</th>
                        <th>Approved</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while ($sale = mysqli_fetch_assoc($sales_data_result)): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($sale['amount']); ?></td>
                        <td><?php echo htmlspecialchars($sale['commission_earned']); ?></td>
                        <td><?php echo htmlspecialchars($sale['created_at']); ?></td>
                        <td><a href="see_who_paid.php?sale_id=<?php echo $sale['id']; ?>">See who paid</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="affiliate_invitees.php?affiliate_id=<?php echo $affiliate_id; ?>" class="back-link">Go Back to Invitees</a><br>
            <a href="admin_index.php">Home</a>
            
        </section>
    </main>
</body>
</html>

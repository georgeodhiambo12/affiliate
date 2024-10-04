<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

$affiliate_id = isset($_GET['affiliate_id']) ? mysqli_real_escape_string($conn, $_GET['affiliate_id']) : 0;

// Fetch affiliate sales data
$sales_data_query = "
SELECT s.sale_amount, s.commission_earned, s.sale_date, s.approved, u.first_name, u.last_name
FROM sales_affiliate s
JOIN users_affiliate u ON s.affiliate_id = u.id
WHERE s.affiliate_id = '$affiliate_id'
";
$sales_data_result = mysqli_query($conn, $sales_data_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Data for Affiliate</title>
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
        .details-link {
            color: #4CAF50;
            text-decoration: underline;
            cursor: pointer;
        }
        .go-back {
            background-color: #4CAF50;
            color: white;
            padding: 0.5rem 1rem;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }
        .go-back:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <h1>Sales Data for Affiliate</h1>
    </header>
    <main>
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
                while ($row = mysqli_fetch_assoc($sales_data_result)): ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($row['sale_amount']); ?></td>
                    <td><?php echo htmlspecialchars($row['commission_earned']); ?></td>
                    <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                    <td>
                        <?php if ($row['approved'] == 'YES'): ?>
                            <a href="view_payer_details.php?affiliate_id=<?php echo $affiliate_id; ?>&status=paid" class="details-link">See who paid</a>
                        <?php else: ?>
                            <a href="view_payer_details.php?affiliate_id=<?php echo $affiliate_id; ?>&status=not_paid" class="details-link">See who didn’t pay</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="admin_index.php" class="go-back">Go Back</a>
    </main>
</body>
</html>

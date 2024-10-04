<?php
session_start();
include 'config.php';

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch admin's name
$user_query = "SELECT first_name FROM users_affiliate WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_row = mysqli_fetch_assoc($user_result);
$admin_name = $user_row['first_name'] ?? 'Admin';

// Calculate the total commissions owed to all affiliates
$total_commissions_query = "
    SELECT SUM(commission_earned) AS total_commissions_owed
    FROM sales_affiliate
    WHERE approved = 'YES'
";
$total_commissions_result = mysqli_query($conn, $total_commissions_query);
$total_commissions_data = mysqli_fetch_assoc($total_commissions_result);
$total_commissions_owed = isset($total_commissions_data['total_commissions_owed']) ? number_format((float)$total_commissions_data['total_commissions_owed'], 2) : '0.00';

// Calculate the total sales
$total_sales_query = "
    SELECT SUM(sale_amount) AS total_sales
    FROM sales_affiliate
    WHERE approved = 'YES'
";
$total_sales_result = mysqli_query($conn, $total_sales_query);
$total_sales_data = mysqli_fetch_assoc($total_sales_result);
$total_sales = isset($total_sales_data['total_sales']) ? number_format((float)$total_sales_data['total_sales'], 2) : '0.00';

// Handle search input
$search_query = "";
if (isset($_POST['search'])) {
    $search_input = mysqli_real_escape_string($conn, $_POST['search_input']);
    $search_query = " AND (u.email LIKE '%$search_input%' OR u.first_name LIKE '%$search_input%' OR u.last_name LIKE '%$search_input%' OR CONCAT(u.first_name, ' ', u.last_name) LIKE '%$search_input%')";
}

// Fetch pending affiliates
$pending_affiliates_query = "
SELECT u.id AS affiliate_id, u.first_name, u.last_name, u.email, u.phone_number, u.referral_code, u.country
FROM users_affiliate u
WHERE u.account_type = 'Affiliate' AND u.affiliate_status = 'pending' $search_query
";
$pending_affiliates_result = mysqli_query($conn, $pending_affiliates_query);

// Fetch approved affiliates
$approved_affiliates_query = "
SELECT u.id AS affiliate_id, u.first_name, u.last_name, u.email, u.phone_number, u.referral_code, u.country
FROM users_affiliate u
WHERE u.account_type = 'Affiliate' AND u.affiliate_status = 'approved' $search_query
";
$approved_affiliates_result = mysqli_query($conn, $approved_affiliates_query);

// Handle Approve and Reject actions
if (isset($_POST['approve'])) {
    $affiliate_id = mysqli_real_escape_string($conn, $_POST['affiliate_id']);
    $update_status_query = "UPDATE users_affiliate SET affiliate_status = 'approved' WHERE id = '$affiliate_id'";
    mysqli_query($conn, $update_status_query);
    header('Location: admin_index.php?message=Affiliate Approved');
    exit();
}

if (isset($_POST['reject'])) {
    $affiliate_id = mysqli_real_escape_string($conn, $_POST['affiliate_id']);
    $update_status_query = "UPDATE users_affiliate SET affiliate_status = 'rejected' WHERE id = '$affiliate_id'";
    mysqli_query($conn, $update_status_query);
    header('Location: admin_index.php?message=Affiliate Rejected');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .logout-form {
            position: absolute;
            top: 0.5rem;
            right: 1rem;
        }
        .logout-button {
            background-color: #fff;
            color: #4CAF50;
            border: 1px solid #4CAF50;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        .logout-button:hover {
            background-color: #4CAF50;
            color: #fff;
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
        .approve-button, .reject-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        .reject-button {
            background-color: #f44336;
        }
        .approve-button:hover {
            background-color: #45a049;
        }
        .reject-button:hover {
            background-color: #e53935;
        }
        .details-link {
            color: #4CAF50;
            text-decoration: underline;
            cursor: pointer;
        }
        .approve-button {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            display: inline-block;
            white-space: nowrap;
            font-size: 0.875rem;
            transition: background-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            text-decoration: none;
        }

        .approve-button:hover {
            background-color: #45a049;
        }

        .total-sales, .total-commissions {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($admin_name); ?></h1>
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </header>
    <main>
        <!-- Total Sales and Total Commissions Owed -->
        <section class="total-sales">
            <h2>Total Sales from Affiliates</h2>
            <p>Ksh <?php echo $total_sales; ?></p>
        </section>
        <section class="total-commissions">
            <h2>Total Commissions Owed to Affiliates</h2>
            <p>Ksh <?php echo $total_commissions_owed; ?></p>
        </section>

        <!-- Search Section -->
        <section class="search-section">
            <form method="POST" action="">
                <input type="text" name="search_input" class="search-input" placeholder="Search by Email or Name" required>
                <button type="submit" name="search" class="search-button">Search</button>
            </form>
        </section>

        <!-- Pending Affiliates Section -->
        <section class="affiliates">
            <h2>Pending Affiliates</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Affiliate Name</th>
                        <th>Affiliate Email</th>
                        <th>Affiliate Phone</th>
                        <th>Referral Code</th>
                        <th>Country</th>
                        <th>Approve/Reject</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while ($affiliate = mysqli_fetch_assoc($pending_affiliates_result)): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($affiliate['first_name'] . ' ' . $affiliate['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['email']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['referral_code']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['country']); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="affiliate_id" value="<?php echo $affiliate['affiliate_id']; ?>">
                                <button type="submit" name="approve" class="approve-button">Approve</button>
                                <button type="submit" name="reject" class="reject-button">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Approved Affiliates Section -->
        <section class="affiliates">
            <h2>Approved Affiliates</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Affiliate Name</th>
                        <th>Affiliate Email</th>
                        <th>Affiliate Phone</th>
                        <th>Referral Code</th>
                        <th>Country</th>
                        <th>Details</th>
                        <th>Invited Affiliates</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while ($affiliate = mysqli_fetch_assoc($approved_affiliates_result)): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($affiliate['first_name'] . ' ' . $affiliate['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['email']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['referral_code']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['country']); ?></td>
                        <td>
                            <a href="affiliate_payment_details.php?affiliate_id=<?php echo $affiliate['affiliate_id']; ?>" class="details-link">View More</a>
                        </td>
                        <td>
                            <a href="view_invited_affiliates.php?inviter_id=<?php echo $affiliate['affiliate_id']; ?>" class="approve-button" target="_blank">View Invited Affiliates</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
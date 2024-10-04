<?php
include 'config.php';

$affiliate_id = isset($_GET['affiliate_id']) ? mysqli_real_escape_string($conn, $_GET['affiliate_id']) : 0;
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Query to fetch user details based on status
if ($status == 'paid') {
    $query = "SELECT first_name, last_name, email FROM users_affiliate WHERE id = '$affiliate_id' AND id IN (SELECT affiliate_id FROM sales_affiliate WHERE approved = 'YES')";
} else {
    $query = "SELECT first_name, last_name, email FROM users_affiliate WHERE id = '$affiliate_id' AND id IN (SELECT affiliate_id FROM sales_affiliate WHERE approved = 'NO')";
}

$result = mysqli_query($conn, $query);
$user_details = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payer Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #4CAF50;
            font-size: 24px;
        }
        p {
            font-size: 18px;
            color: #333;
        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-back:hover {
            background-color: #45a049;
        }
        .not-found {
            color: red;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payer Details</h1>
        <?php if ($user_details): ?>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user_details['first_name'] . ' ' . $user_details['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_details['email']); ?></p>
        <?php else: ?>
            <p class="not-found">No user found.</p>
        <?php endif; ?>
        <a href="affiliate_details.php?affiliate_id=<?php echo $affiliate_id; ?>" class="btn-back">Go Back</a>
    </div>
</body>
</html>

<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['message'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = $_SESSION['message'];

// Optionally, fetch more user details if necessary
// Example: Fetch the user’s name or other relevant details to display on the page

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approval</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            text-align: center;
            padding: 50px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-top: 50px;
        }
        .container h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .container p {
            color: #666;
            margin-bottom: 20px;
        }
        .container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .container a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pending Approval</h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p>Access to certain features is restricted until approval is granted.</p>
    </div>
</body>
</html>

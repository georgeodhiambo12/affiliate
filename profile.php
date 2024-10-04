<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users_affiliate WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_row = mysqli_fetch_assoc($user_result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name']);
    $bank_account = mysqli_real_escape_string($conn, $_POST['bank_account']);
    $mpesa_number = mysqli_real_escape_string($conn, $_POST['mpesa_number']);

    $update_query = "UPDATE users_affiliate SET bank_name='$bank_name', bank_account='$bank_account', mpesa_number='$mpesa_number' WHERE id='$user_id'";
    mysqli_query($conn, $update_query);

    $_SESSION['message'] = 'Profile updated successfully.';
    header('Location: profile.php');
    exit();
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear session messages
unset($_SESSION['message'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Profile</h1>
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </header>
    <main>
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST">
            <div>
                <label for="bank_name">Bank Name</label>
                <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($user_row['bank_name']); ?>">
            </div>
            <div>
                <label for="bank_account">Bank Account</label>
                <input type="text" id="bank_account" name="bank_account" value="<?php echo htmlspecialchars($user_row['bank_account']); ?>">
            </div>
            <div>
                <label for="mpesa_number">M-PESA Number</label>
                <input type="text" id="mpesa_number" name="mpesa_number" value="<?php echo htmlspecialchars($user_row['mpesa_number']); ?>">
            </div>
            <button type="submit">Update Profile</button>
        </form>
    </main>
</body>
</html>

<?php
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include 'config.php';
require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;

// Redirect if user is not logged in or doesn't have the right account type
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['account_type'], ['Affiliate', 'Creator/Micro-Influencer', 'Mega-Influencer'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Function to mask sensitive details (phone and email)
function maskPhoneNumber($phone_number) {
    return substr($phone_number, 0, 3) . str_repeat('*', strlen($phone_number) - 6) . substr($phone_number, -3);
}

function maskEmail($email) {
    $parts = explode("@", $email);
    $name = substr($parts[0], 0, 2) . str_repeat('*', strlen($parts[0]) - 4) . substr($parts[0], -2);
    return $name . "@" . $parts[1];
}

// Fetch user details
$user_query = "SELECT first_name, last_name, email, registration_date, referral_code, phone_number, payment_method, payment_details, bank_name, bank_account_number, bank_branch, swift_code, iban FROM users_affiliate WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_row = mysqli_fetch_assoc($user_result);

$affiliate_name = $user_row['first_name'];
$last_name = $user_row['last_name'];
$email = maskEmail($user_row['email']);
$registration_date = $user_row['registration_date'];
$referral_code = $user_row['referral_code'];
$phone_number = maskPhoneNumber($user_row['phone_number']);
$payment_method = $user_row['payment_method'];
$payment_details = $user_row['payment_details'];
$bank_name = $user_row['bank_name'];
$bank_account_number = $user_row['bank_account_number'];
$bank_branch = $user_row['bank_branch'];
$swift_code = $user_row['swift_code'];
$iban = $user_row['iban'];

// Generate a referral code if none exists
function generateReferralCode($conn) {
    $code = strtoupper(bin2hex(random_bytes(3)));
    $query = "SELECT id FROM users_affiliate WHERE referral_code='$code'";
    $result = mysqli_query($conn, $query);
    return (mysqli_num_rows($result) > 0) ? generateReferralCode($conn) : $code;
}

if (empty($referral_code)) {
    $referral_code = generateReferralCode($conn);
    $update_code_query = "UPDATE users_affiliate SET referral_code='$referral_code' WHERE id='$user_id'";
    mysqli_query($conn, $update_code_query);
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    $password_query = "SELECT password FROM users_affiliate WHERE id='$user_id'";
    $password_result = mysqli_query($conn, $password_query);
    $password_row = mysqli_fetch_assoc($password_result);

    if (password_verify($current_password, $password_row['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_password_query = "UPDATE users_affiliate SET password='$hashed_password' WHERE id='$user_id'";
            mysqli_query($conn, $update_password_query);
            $_SESSION['message'] = 'Password updated successfully.';
        } else {
            $_SESSION['error'] = 'New passwords do not match.';
        }
    } else {
        $_SESSION['error'] = 'Current password is incorrect.';
    }

    header('Location: affiliate_index.php');
    exit();
}

// Handle account closure request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close_account'])) {
    $_SESSION['account_closed'] = true;
    header('Location: goodbye.php');
    exit();
}

// Handle payment details update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_method'])) {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $payment_details = mysqli_real_escape_string($conn, $_POST['payment_details']);
    $bank_name = isset($_POST['bank_name']) ? mysqli_real_escape_string($conn, $_POST['bank_name']) : null;
    $bank_account_number = isset($_POST['bank_account_number']) ? mysqli_real_escape_string($conn, $_POST['bank_account_number']) : null;
    $bank_branch = isset($_POST['bank_branch']) ? mysqli_real_escape_string($conn, $_POST['bank_branch']) : null;
    $swift_code = isset($_POST['swift_code']) ? mysqli_real_escape_string($conn, $_POST['swift_code']) : null;
    $iban = isset($_POST['iban']) ? mysqli_real_escape_string($conn, $_POST['iban']) : null;

    $update_payment_query = "
        UPDATE users_affiliate 
        SET 
            payment_method='$payment_method', 
            payment_details='$payment_details', 
            bank_name='$bank_name', 
            bank_account_number='$bank_account_number', 
            bank_branch='$bank_branch', 
            swift_code='$swift_code', 
            iban='$iban' 
        WHERE id='$user_id'
    ";

    if (mysqli_query($conn, $update_payment_query)) {
        $_SESSION['message'] = 'Payment details updated successfully.';
    } else {
        $_SESSION['error'] = 'Error updating payment details.';
    }

    header('Location: affiliate_index.php');
    exit();
}

// Fetch total commissions earned and approved from the sales_affiliate table
$commissions_query = "
    SELECT SUM(commission_earned) AS total_commissions 
    FROM sales_affiliate 
    WHERE affiliate_id='$user_id' AND approved='YES'";
$commissions_result = mysqli_query($conn, $commissions_query);
$commissions_data = mysqli_fetch_assoc($commissions_result);
$total_commissions = isset($commissions_data['total_commissions']) ? (float)$commissions_data['total_commissions'] : 0;

// Fetch total amount withdrawn by the user from the withdrawals table
$withdrawals_query = "
    SELECT SUM(amount) AS total_withdrawn 
    FROM withdrawals 
    WHERE affiliate_id='$user_id'";
$withdrawals_result = mysqli_query($conn, $withdrawals_query);
$withdrawals_data = mysqli_fetch_assoc($withdrawals_result);
$total_withdrawn = isset($withdrawals_data['total_withdrawn']) ? (float)$withdrawals_data['total_withdrawn'] : 0;

// Fetch total sales amount
$sales_query = "
    SELECT SUM(sale_amount) AS total_sales 
    FROM sales_affiliate 
    WHERE affiliate_id='$user_id' AND approved = 'YES'";
$sales_result = mysqli_query($conn, $sales_query);
$sales_data = mysqli_fetch_assoc($sales_result);
$total_sales = isset($sales_data['total_sales']) ? (float)$sales_data['total_sales'] : 0;

// Calculate available balance
$available_balance = $total_commissions - $total_withdrawn;

// Handle withdrawal request with two-week and end-of-month restriction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_amount'])) {
    $withdraw_amount = (float)mysqli_real_escape_string($conn, $_POST['withdraw_amount']);

    // Get current date
    $current_date = new DateTime();
    $day_of_month = (int)$current_date->format('d');
    
    // Get the week number of the current month (1st week = 1, 2nd week = 2, etc.)
    $week_of_month = (int)ceil($day_of_month / 7);

    // Allow withdrawal only on even-numbered weeks of the month or at the end of the month
    $is_withdrawal_period = ($week_of_month % 2 == 0 || $day_of_month > 25);

    if (!$is_withdrawal_period) {
        $_SESSION['error'] = 'Withdrawals are only allowed on even weeks of the month(mid month) or at the end of the month.';
    } else if ($withdraw_amount > $total_commissions) {
        $_SESSION['error'] = 'Insufficient funds to withdraw this amount.';
    } else {
        // Log the withdrawal
        $log_withdrawal_query = "
            INSERT INTO withdrawals (affiliate_id, amount, date) 
            VALUES ('$user_id', '$withdraw_amount', NOW())";
        mysqli_query($conn, $log_withdrawal_query);

        // Notify admin via SMTP2GO email
        $mail = new PHPMailer(true);
        try {
            // SMTP2GO settings
            $mail->isSMTP();
            $mail->Host = 'mail.smtp2go.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tendersoko'; 
            $mail->Password = 'Barcampivorycoast!';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 443;

            // Admin email settings
            $mail->setFrom('no-reply@tendersoko.com', 'TenderSoko');
            $mail->addAddress('info@tendersoko.com'); // Admin email

            // Email content
            $mail->isHTML(true); // Enable HTML if needed
            $mail->Subject = "Withdrawal Request by $affiliate_name";
            $mail->Body = "
                <p>$affiliate_name has requested a withdrawal of Ksh $withdraw_amount.</p>
                <p>Please initiate the payment.</p>";

            // Send email
            $mail->send();
        } catch (Exception $e) {
            // Handle exception if email fails to send
            $_SESSION['error'] = 'Error in sending withdrawal notification email: ' . $mail->ErrorInfo;
        }

        $_SESSION['message'] = 'Withdrawal request successful. Payment will be processed.';
    }

    header('Location: affiliate_index.php');
    exit();
}

// Fetch recruited suppliers from invitations_affiliate with status from sales_affiliate
$suppliers_query = "
    SELECT 
        i.supplier_name, 
        i.supplier_email, 
        i.supplier_phone, 
        IF(s.approved = 'YES', 'Yes', 'No') AS status, 
        i.created_at, 
        i.last_invited_at 
    FROM 
        invitations_affiliate i 
    LEFT JOIN 
        sales_affiliate s 
    ON 
        i.affiliate_id = s.affiliate_id 
    WHERE 
        i.affiliate_id='$user_id'
    GROUP BY 
        i.supplier_name, i.supplier_email, i.supplier_phone, i.created_at, i.last_invited_at";
$suppliers_result = mysqli_query($conn, $suppliers_query);
$suppliers = [];
while ($row = mysqli_fetch_assoc($suppliers_result)) {
    $row['supplier_email'] = maskEmail($row['supplier_email']);
    $row['supplier_phone'] = maskPhoneNumber($row['supplier_phone']);
    $suppliers[] = $row;
}

// Corrected Referral Statistics Query
$referral_stats_query = "
    SELECT 
        i.supplier_name, 
        s.sale_amount, 
        s.approved, 
        s.sale_date 
    FROM 
        invitations_affiliate i 
    LEFT JOIN 
        sales_affiliate s 
    ON 
        i.affiliate_id = s.affiliate_id 
    WHERE 
        i.affiliate_id = '$user_id'
        AND s.approved = 'YES'
    ORDER BY 
        s.sale_date DESC
";

$referral_stats_result = mysqli_query($conn, $referral_stats_query);
$referral_transactions = [];
while ($row = mysqli_fetch_assoc($referral_stats_result)) {
    if ($row['approved'] === 'YES') {
        $sale_amount = $row['sale_amount'] > 0 ? "of Ksh " . htmlspecialchars($row['sale_amount']) : "";
        $message = "Supplier " . htmlspecialchars($row['supplier_name']) . " made a successful payment " . $sale_amount . " on " . htmlspecialchars($row['sale_date']) . ".";
        $referral_transactions[] = $message;
    }
}

// Initialize notifications array
$notifications = [];

// Handle Excel upload and validate columns
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        // Load the Excel file
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $expectedColumns = ['Supplier Email', 'Supplier Name', 'Supplier Phone'];

        // Fetch the first row as header and validate
        $headerRow = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false)[0];

        // Check if the uploaded file has the correct columns
        if ($headerRow !== $expectedColumns) {
            $_SESSION['error'] = 'Invalid column format. Ensure your Excel file has the columns: Supplier Email, Supplier Name, Supplier Phone.';
            header('Location: affiliate_index.php');
            exit();
        }

        // If the format is correct, process the file
        $invitations = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false)[0];
            $supplierEmail = mysqli_real_escape_string($conn, $rowData[0]);
            $supplierName = mysqli_real_escape_string($conn, $rowData[1]);
            $supplierPhone = mysqli_real_escape_string($conn, $rowData[2]);
            $affiliateId = $_SESSION['user_id'];
            $referralCode = $_POST['referral_code']; // Get referral code from the form

            // Insert supplier invitation into the database
            $insertQuery = "
                INSERT INTO invitations_affiliate (affiliate_id, supplier_email, supplier_name, supplier_phone, referral_code, created_at) 
                VALUES ('$affiliateId', '$supplierEmail', '$supplierName', '$supplierPhone', '$referralCode', NOW())
            ";
            if (!mysqli_query($conn, $insertQuery)) {
                $_SESSION['error'] = 'Error inviting suppliers. Please try again.';
                header('Location: affiliate_index.php');
                exit();
            }
            $invitations[] = $rowData;
        }

        $_SESSION['message'] = count($invitations) . ' suppliers invited successfully.';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error processing Excel file: ' . $e->getMessage();
        header('Location: affiliate_index.php');
        exit();
    }

    header('Location: affiliate_index.php');
    exit();
}

// Fetch notifications from the notifications table
$notifications_query = "SELECT message, created_at FROM notifications WHERE user_id='$user_id' ORDER BY created_at DESC";
$notifications_result = mysqli_query($conn, $notifications_query);
while ($row = mysqli_fetch_assoc($notifications_result)) {
    $notifications[] = $row;
}

// Fetch notifications for invited suppliers
$invited_query = "
    SELECT 
        CONCAT('You invited ', supplier_name, ' (', supplier_email, ') on ', created_at) AS message, 
        created_at 
    FROM 
        invitations_affiliate 
    WHERE 
        affiliate_id='$user_id'
    ORDER BY 
        created_at DESC";
$invited_result = mysqli_query($conn, $invited_query);
while ($row = mysqli_fetch_assoc($invited_result)) {
    $row['message'] = maskEmail($row['message']);
    $notifications[] = $row;
}

// Fetch notifications for suppliers who clicked the link
$clicked_query = "
    SELECT 
        CONCAT('Supplier ', supplier_name, ' (', supplier_email, ') clicked your referral link on ', last_invited_at) AS message, 
        last_invited_at AS created_at 
    FROM 
        invitations_affiliate 
    WHERE 
        affiliate_id='$user_id' AND last_invited_at IS NOT NULL
    ORDER BY 
        last_invited_at DESC";
$clicked_result = mysqli_query($conn, $clicked_query);
while ($row = mysqli_fetch_assoc($clicked_result)) {
    $row['message'] = maskEmail($row['message']);
    $notifications[] = $row;
}

// Fetch notifications for suppliers who paid
$paid_query = "
    SELECT 
        CONCAT('Supplier ', supplier_name, ' (', supplier_email, ') made a payment on ', MAX(sales_affiliate.sale_date)) AS message, 
        MAX(sales_affiliate.sale_date) AS sale_date 
    FROM 
        invitations_affiliate 
    JOIN 
        sales_affiliate 
    ON 
        invitations_affiliate.affiliate_id = sales_affiliate.affiliate_id 
    WHERE 
        invitations_affiliate.affiliate_id='$user_id' AND sales_affiliate.approved = 'YES'
    GROUP BY 
        invitations_affiliate.supplier_name, invitations_affiliate.supplier_email
    ORDER BY 
        MAX(sales_affiliate.sale_date) DESC";
$paid_result = mysqli_query($conn, $paid_query);
while ($row = mysqli_fetch_assoc($paid_result)) {
    $row['message'] = maskEmail($row['message']);
    $notifications[] = $row;
}

// Fetch notifications for suppliers who never paid
$unpaid_query = "
    SELECT 
        CONCAT('Supplier ', supplier_name, ' (', supplier_email, ') has not made any payment since ', created_at) AS message, 
        created_at 
    FROM 
        invitations_affiliate 
    WHERE 
        affiliate_id='$user_id' AND supplier_email NOT IN (
            SELECT supplier_email FROM sales_affiliate WHERE approved = 'YES'
        )
    ORDER BY 
        created_at DESC";
$unpaid_result = mysqli_query($conn, $unpaid_query);
while ($row = mysqli_fetch_assoc($unpaid_result)) {
    $row['message'] = maskEmail($row['message']);
    $notifications[] = $row;
}

// Sort all notifications by created_at in descending order
usort($notifications, function($a, $b) {
    $timeA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
    $timeB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
    return $timeB - $timeA;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Styles go here */
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .dashboard-content {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
        }
        .user-profile {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .user-profile h2 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #333;
        }
        .user-profile .details {
            margin-bottom: 1rem;
        }
        .user-profile .details div {
            margin-bottom: 0.5rem;
        }
        .user-profile .details div label {
            font-weight: bold;
            display: block;
            margin-bottom: 0.25rem;
        }
        .user-profile .details div p {
            margin: 0;
        }
        .user-profile .actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .user-profile .actions button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            width: 100%;
        }
        .user-profile .actions button:hover {
            background-color: #45a049;
        }
        .main-dashboard {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }
        .card h2 {
            margin-bottom: 1rem;
        }
        .card p {
            font-size: 1.5rem;
            margin: 0;
        }
        .card:hover::after {
            content: attr(data-description);
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 100%;
            background: rgba(0, 0, 0, 0.75);
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            white-space: nowrap;
            font-size: 0.875rem;
            opacity: 0.9;
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
            padding: 0.75rem;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .withdraw-form, .invite-form {
            margin-top: 2rem;
        }
        .withdraw-form input, .invite-form input {
            padding: 0.75rem;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .withdraw-form button, .invite-form button {
            padding: 0.75rem 1rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 0.5rem;
        }
        .withdraw-form button:hover, .invite-form button:hover {
            background-color: #45a049;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .modal h2 {
            text-align: center;
            margin-bottom: 1rem;
        }
        .modal label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }
        .modal input {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal button {
            width: 100%;
            padding: 0.75rem;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal button:hover {
            background-color: #45a049;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
        .referral-stats, .notifications {
            margin-top: 2rem;
        }
        .notification {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .notification.red-text {
            background-color: #f44336;
        }
        @media screen and (max-width: 768px) {
            main {
                padding: 0 1rem;
            }
            .dashboard-content {
                grid-template-columns: 1fr;
            }
            .user-profile {
                margin-bottom: 2rem;
            }
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($affiliate_name); ?></h1>
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </header>
    <main>
        <div class="dashboard-content">
            <div class="user-profile">
                <h2>User Profile</h2>
                <div class="details">
                    <p>Email: <?php echo htmlspecialchars($email); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($phone_number); ?></p>
                    <p>First name: <?php echo htmlspecialchars($affiliate_name); ?></p>
                    <p>Last name: <?php echo htmlspecialchars($last_name); ?></p>
                    <p>Registration date: <?php echo htmlspecialchars($registration_date); ?></p>
                    <p>Referral Code: <?php echo htmlspecialchars($referral_code); ?></p>
                    <p>Payment Method: <?php echo htmlspecialchars($payment_method); ?></p>
                    <p>Payment Details: <?php echo htmlspecialchars($payment_details); ?></p>
                </div>
                <div class="actions">
                    <button id="changePasswordBtn">Change Password</button>
                    <button id="changePaymentBtn">Change Payment Method</button>
                    <form action="affiliate_index.php" method="POST">
                        <input type="hidden" name="close_account">
                        <button type="submit">Close Account</button>
                    </form>
                </div>
            </div>

            <div class="main-dashboard">
                <!-- Notifications and messages handling -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="notification">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="notification red-text">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Cards displaying relevant data -->
                <div class="cards">
                    <div class="card" data-description="This shows the total number of suppliers you have recruited.">
                        <h2>Total Recruited Suppliers</h2>
                        <p><?php echo count($suppliers); ?></p>
                    </div>
                    <div class="card" data-description="This represents the total sales amount generated by your recruited suppliers.">
                        <h2>Total Sales</h2>
                        <p><?php echo number_format($total_sales, 2); ?></p>
                    </div>
                    <div class="card" data-description="This represents the total commissions you have earned from sales made by your recruited suppliers.">
                        <h2>Total Commissions Earned</h2>
                        <p><?php echo number_format($total_commissions, 2); ?></p>
                    </div>
                    <div class="card" data-description="This shows the total number of notifications you have received.">
                        <h2>Notifications</h2>
                        <p><?php echo count($notifications); ?></p> <!-- Display number of notifications -->
                    </div>
                    <div class="card" data-description="This is the available balance you can withdraw, generated from commissions earned.">
                        <h2>Available Balance</h2>
                        <p><?php echo number_format($available_balance, 2); ?></p>
                    </div>
                </div>

                <!-- Withdrawal form -->
                <section class="withdraw-form">
                    <h2>Withdraw Funds</h2>
                    <form action="affiliate_index.php" method="POST">
                        <input type="number" name="withdraw_amount" placeholder="Enter amount to withdraw" required>
                        <button type="submit">Withdraw</button>
                    </form>
                </section>

                <!-- Supplier recruitment section -->
                <section class="recruited-suppliers">
                    <h2>Recruited Suppliers</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Invitation Date</th>
                                <th>Last Invitation Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $index => $supplier): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['supplier_email']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['supplier_phone']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['status']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars($supplier['last_invited_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Invite supplier -->
                <section class="invite-form">
                    <h2>Invite a Supplier</h2>
                    <form action="invite_supplier.php" method="POST">
                        <input type="email" name="supplier_email" placeholder="Supplier Email" required>
                        <input type="text" name="supplier_name" placeholder="Supplier Name" required>
                        <input type="text" name="supplier_phone" placeholder="Supplier Phone" required>
                        <input type="hidden" name="referral_code" value="<?php echo htmlspecialchars($referral_code); ?>">
                        <button type="submit">Invite Supplier</button>
                        <!-- Optional personalized message -->
                        <textarea name="personalized_message" placeholder="Leave a personalized message (optional)"></textarea>
                        <input type="submit" value="Send Invitation">
                    </form>
                </section>

                <!-- Upload Excel -->
                <section class="invite-form">
                    <h2>Upload Excel to Invite Multiple Suppliers (NB: The system will send 50 invites per batch before proceeding after 10 seconds)</h2>
                    <form id="inviteForm" action="affiliate_index.php" method="POST" enctype="multipart/form-data">
                        <label>
                            <input type="checkbox" id="certify-checkbox">
                            I certify that the above emails in the excel sheet are aware and know me in person and any false information will lead to the suspension of my account.
                        </label>
                        <!-- Warning Message -->
                        <p id="warning-message" style="color: red; display: none;">You must check the box before uploading the file.</p>

                        <input type="file" name="excel_file" id="excel-file" disabled required>
                        
                        <button type="submit" id="submit-btn" disabled>Upload and Invite</button>
                        <div class="help-text">
                            Ensure your Excel file has the following columns: 
                            <br>- Supplier Email
                            <br>- Supplier Name
                            <br>- Supplier Phone
                        </div>
                        <div class="sample-download">
                            <a href="sample.xlsx" download>Download Sample Excel Template</a>
                        </div>
                        <textarea name="personalized_message" placeholder="Leave a personalized message (optional)"></textarea>
                    </form>
                </section>

                <!-- Referral stats -->
                <section class="referral-stats">
                    <h2>Referral Statistics</h2>
                    <ul>
                            <li>No statistics found.</li>
                    </ul>
                </section>

                <!-- Notifications -->
                <section class="notifications">
                    <h2>Notifications</h2>
                    <ul>
                            <li>No notifications found.</li>
                    </ul>
                </section>
            </div>
        </div>
    </main>

    <!-- Modal for changing password -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closePasswordModal">&times;</span>
            <h2>Change Password</h2>
            <form action="affiliate_index.php" method="POST">
                <label for="current_password">Current Password:</label>
                <input type="password" name="current_password" id="current_password" required>
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <input type="hidden" name="update_password">
                <button type="submit">Update Password</button>
            </form>
        </div>
    </div>

    <!-- Modal for changing payment details -->
    <div id="changePaymentModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closePaymentModal">&times;</span>
            <h2>Change Payment Method</h2>
            <form action="affiliate_index.php" method="POST">
                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="Mobile Money" <?php if($payment_method == 'Mobile Money') echo 'selected'; ?>>Mobile Money</option>
                    <option value="Bank Transfer" <?php if($payment_method == 'Bank Transfer') echo 'selected'; ?>>Bank Transfer</option>
                </select>

                <!-- Mobile Money -->
                <div id="mobileMoneyDetails" style="display: none;">
                    <label for="payment_details">Phone Number (Include Country Code):</label>
                    <input type="text" name="payment_details" id="payment_details" value="<?php echo htmlspecialchars($payment_details); ?>" placeholder="+2547XXXXXXXX" required>
                </div>

                <!-- Bank specific fields -->
                <div id="bankDetails" style="display: none;">
                    <label for="bank_name">Bank Name:</label>
                    <input type="text" name="bank_name" id="bank_name" value="<?php echo htmlspecialchars($bank_name); ?>">
                    <label for="bank_account_number">Bank Account Number:</label>
                    <input type="text" name="bank_account_number" id="bank_account_number" value="<?php echo htmlspecialchars($bank_account_number); ?>">
                    <label for="bank_branch">Bank Branch:</label>
                    <input type="text" name="bank_branch" id="bank_branch" value="<?php echo htmlspecialchars($bank_branch); ?>">
                    <label for="swift_code">SWIFT Code:</label>
                    <input type="text" name="swift_code" id="swift_code" value="<?php echo htmlspecialchars($swift_code); ?>">
                    <label for="iban">IBAN:</label>
                    <input type="text" name="iban" id="iban" value="<?php echo htmlspecialchars($iban); ?>">
                </div>

                <input type="hidden" name="update_payment_method">
                <button type="submit">Update Payment Method</button>
            </form>
        </div>
    </div>

    <script>
        var paymentMethod = document.getElementById('payment_method');
        var bankDetails = document.getElementById('bankDetails');
        var mobileMoneyDetails = document.getElementById('mobileMoneyDetails');

        // Toggle visibility of bank or mobile money details based on selected payment method
        function togglePaymentDetails() {
            if (paymentMethod.value === 'Bank Transfer') {
                bankDetails.style.display = 'block';
                mobileMoneyDetails.style.display = 'none';
            } else if (paymentMethod.value === 'Mobile Money') {
                mobileMoneyDetails.style.display = 'block';
                bankDetails.style.display = 'none';
            }
        }

        paymentMethod.addEventListener('change', togglePaymentDetails);

        // Initial call to set correct visibility on page load
        togglePaymentDetails();

        // Modal management
        var changePasswordModal = document.getElementById('changePasswordModal');
        var changePaymentModal = document.getElementById('changePaymentModal');

        var changePasswordBtn = document.getElementById('changePasswordBtn');
        var changePaymentBtn = document.getElementById('changePaymentBtn');

        var closePasswordModal = document.getElementById('closePasswordModal');
        var closePaymentModal = document.getElementById('closePaymentModal');

        // Modal toggling logic
        changePasswordBtn.onclick = function() { changePasswordModal.style.display = 'block'; }
        changePaymentBtn.onclick = function() { changePaymentModal.style.display = 'block'; }

        closePasswordModal.onclick = function() { changePasswordModal.style.display = 'none'; }
        closePaymentModal.onclick = function() { changePaymentModal.style.display = 'none'; }

        window.onclick = function(event) {
            if (event.target == changePasswordModal) { changePasswordModal.style.display = 'none'; }
            if (event.target == changePaymentModal) { changePaymentModal.style.display = 'none'; }
        }

        // Checkbox validation for uploading excel form
        var certifyCheckbox = document.getElementById('certify-checkbox');
        var submitBtn = document.getElementById('submit-btn');
        var excelFile = document.getElementById('excel-file');
        var warningMessage = document.getElementById('warning-message');

        // Prevent form submission if checkbox is not checked
        document.getElementById('inviteForm').addEventListener('submit', function(event) {
            if (!certifyCheckbox.checked) {
                event.preventDefault(); // Stop the form from submitting
                warningMessage.style.display = 'block'; // Show the warning message
            } else {
                warningMessage.style.display = 'none'; // Hide the warning if the checkbox is checked
            }
        });

        // Enable the file input and submit button when the checkbox is checked
        certifyCheckbox.addEventListener('change', function() {
            submitBtn.disabled = !certifyCheckbox.checked;
            excelFile.disabled = !certifyCheckbox.checked;
        });
    </script>
</body>
</html>

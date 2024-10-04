<?php
// Start PHP script for handling login and registration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        // Login process
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        // Query the database for the user
        $sql = "SELECT * FROM users_affiliate WHERE email='$email'";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Query failed: " . mysqli_error($conn));
        }

        $user = mysqli_fetch_assoc($result);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['account_type'] = $user['account_type'];

                // If the user is an admin, bypass 2FA
                if ($user['account_type'] == 'Admin') {
                    header('Location: admin_index.php');
                    exit();
                }

                // Check if the user is an affiliate or other allowed account types
                $allowed_account_types = ['Affiliate', 'Creator/Micro-Influencer', 'Mega-Influencer'];
                if (in_array($user['account_type'], $allowed_account_types)) {
                    if ($user['verified'] == 1 && $user['affiliate_status'] == 'approved') {
                        header('Location: affiliate_index.php');
                        exit();
                    } else {
                        $error_message = "Your account is either not verified or pending approval.";
                    }
                } else {
                    $error_message = "You do not have permission to access this page.";
                }
            } else {
                $error_message = "Incorrect password. Please try again.";
            }
        } else {
            $error_message = "No user found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TenderSoko Affiliate Program</title>
    <style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f7f8fa;
    margin: 0;
    padding-top: 50px; 
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    height: 100vh;
    box-sizing: border-box; 
    overflow-y: auto; /* Allow scrolling if content is too tall */
}

.container {
    width: 100%;
    max-width: 900px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 20px;
}

.card {
    background-color: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
    margin-bottom: 20px;
}

.info-section h3 {
    margin-bottom: 10px;
    font-size: 18px;
    color: #333;
}

.info-section p {
    font-size: 14px;
    color: #555;
    margin-bottom: 10px;
}

.info-section p strong {
    color: #28a745;
}

h2 {
    margin-bottom: 20px;
    font-size: 28px;
    text-align: center;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #555;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    box-sizing: border-box;
}

.form-group input:focus {
    border-color: #28a745;
    outline: none;
    box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
}

.alert-message {
    color: green;
    text-align: center;
    margin-bottom: 15px;
    font-size: 16px;
}

button {
    width: 100%;
    padding: 12px;
    background-color: #28a745;
    color: #ffffff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    margin-top: 10px;
}

button:hover {
    background-color: #218838;
}

.link {
    display: block;
    margin-top: 15px;
    color: #007bff;
    text-align: center;
    text-decoration: none;
    font-size: 14px;
}

.link:hover {
    text-decoration: underline;
}

.error-message {
    color: red;
    text-align: center;
    margin-bottom: 15px;
    font-size: 16px;
}

@media (max-width: 768px) {
    .container {
        flex-direction: column;
        align-items: center;
    }

    .card {
        width: 90%;
        padding: 15px;
    }

    h2 {
        font-size: 24px;
    }

    .form-group input {
        font-size: 14px;
    }

    button {
        font-size: 16px;
        padding: 10px;
    }
}

@media (max-width: 480px) {
    .card {
        width: 100%;
        padding: 10px;
    }

    h2 {
        font-size: 22px;
    }

    .form-group input {
        font-size: 12px;
    }

    button {
        font-size: 14px;
        padding: 10px;
    }
}
    </style>
</head>
<body>
     <br><br><br><br><br><br><br><br><br><br><br>
    <div class="container">
        <!-- Information Section -->
        <div class="card info-section">
           
            <h3>Important Information:</h3>
            <p>1. When you login successfully, minimize your screen to view all details without straining.</p>
            <p>2. Our main website/platform for suppliers to view tenders is <strong>tendersoko.com</strong>.Interact with it to know what we do!</p>
            <p>3. As an affiliate, you help suppliers access tenders and earn <strong>10% commission</strong> for each referral's subscription!</p>
            <p>4. Suppliers get a <strong>10% discount</strong> when they use your link for the <strong>Ksh 799(monthly), Ksh5999(annual)</strong> subscription.</p>
            <p>5. You will get the referral code within your account, once you have verified the registration link and have been successfully approved by the TenderSoko administrator.</p>
            <p>6. For any queries, contact +254 720 375992, +254783600600</p>
        </div>

        <!-- Login Section -->
        <div class="card">
            <h2>Affiliate Login</h2>
            <div class="alert-message">
                <p>Earn a 10% commission for every subscription that you bring!</p>
            </div>
            <?php if (isset($error_message)) { echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>'; } ?>
            
            <form id="login-form" action="login.php" method="POST" class="form active">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login">Sign in</button>
                <a href="forgot_password.html" class="link">Forgot Password? Click here</a>
                <a href="registration.html" class="link">Not Registered? Click here to register</a>
            </form>
        </div>
    </div>
</body>
</html>

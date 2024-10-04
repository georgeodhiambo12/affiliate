<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Sign-Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="signup-form">
        <h2>Register a Supplier</h2>
        <form action="register_supplier.php" method="POST">
            <div class="form-group">
                <label for="supplier-name">Supplier Name</label>
                <input type="text" id="supplier-name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree with the Terms & Conditions</label>
            </div>
            <button type="submit">Register Supplier</button>
        </form>
    </div>
</body>
</html>

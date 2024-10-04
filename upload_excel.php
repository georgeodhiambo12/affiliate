<?php
session_start();
include 'config.php';
require 'vendor/autoload.php'; // Ensure this path is correct based on your setup

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $filePath = $_FILES['excel_file']['tmp_name'];

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $row) {
            // Assuming the Excel columns are in this order: Email, Name, Phone
            $email = mysqli_real_escape_string($conn, trim($row[0]));
            $name = mysqli_real_escape_string($conn, trim($row[1]));
            $phone = mysqli_real_escape_string($conn, trim($row[2]));

            // Assuming your existing invite logic involves inserting into a database or sending an email
            $referral_code = mysqli_real_escape_string($conn, $_SESSION['referral_code']);
            $query = "INSERT INTO suppliers_affiliate (affiliate_id, name, email, phone, status) VALUES ('$user_id', '$name', '$email', '$phone', 'pending')";
            mysqli_query($conn, $query);

            // Optionally send an invite email
            // mail($email, "You're Invited", "You've been invited to join our platform by $affiliate_name. Click here to sign up!");
        }

        $_SESSION['message'] = "Invites sent successfully!";
        header('Location: affiliate_index.php');
        exit();
    } else {
        $_SESSION['error'] = "There was an issue with the file upload.";
        header('Location: affiliate_index.php');
        exit();
    }
}
?>

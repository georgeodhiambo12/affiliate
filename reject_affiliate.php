<?php
session_start();
include 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['affiliate_id'])) {
        $affiliate_id = $_POST['affiliate_id'];

        // Fetch affiliate details
        $affiliate_query = "SELECT email, first_name FROM users_affiliate WHERE id='$affiliate_id'";
        $affiliate_result = mysqli_query($conn, $affiliate_query);
        $affiliate_row = mysqli_fetch_assoc($affiliate_result);

        if ($affiliate_row) {
            $affiliate_email = $affiliate_row['email'];
            $affiliate_name = $affiliate_row['first_name'];

            $update_query = "UPDATE users_affiliate SET affiliate_status='rejected' WHERE id='$affiliate_id'";
            if (mysqli_query($conn, $update_query)) {
                // Send rejection email to the affiliate
                $subject = "Your Affiliate Application Has Been Rejected";
                $message = "Dear $affiliate_name,\n\n";
                $message .= "I hope this message finds you well. First and foremost, we would like to extend our sincere appreciation for your interest in becoming an affiliate with TenderSoko. Your enthusiasm and desire to be a part of our platform are highly valued, and it is always inspiring to see individuals like yourself who are eager to make a difference.\n\n";
                $message .= "After careful consideration of your application, we regret to inform you that, at this time, we are unable to move forward with your request to become an affiliate. Please know that this decision was not made lightly. We thoroughly review each application to ensure it aligns with our current needs and standards, and while your application was strong, we have had to make some difficult choices.\n\n";
                $message .= "However, this is by no means a reflection of your potential. The affiliate marketing landscape is dynamic and ever-changing, and there are always new opportunities on the horizon. We encourage you not to be discouraged by this outcome. Your passion and commitment to affiliate marketing are evident, and these are qualities that will undoubtedly lead to future success.\n\n";
                $message .= "We want to assure you that this is not the end of the road. Our platform continues to grow, and with that growth comes new opportunities. We strongly encourage you to stay connected with us, keep refining your skills, and consider reapplying in the future as our needs and the scope of our affiliate program evolve.\n\n";
                $message .= "In the meantime, here are a few suggestions to help you prepare for future opportunities:\n\n";
                $message .= "1. **Stay Updated**: Keep an eye on our website and social media channels for any updates or changes to our affiliate program. We may announce new opportunities that could be a perfect fit for you.\n";
                $message .= "2. **Continue Building Your Brand**: Use this time to further develop your online presence and audience. Affiliates who have a strong, engaged following often stand out in future selection processes.\n";
                $message .= "3. **Networking**: Engage with other affiliates and marketers to learn from their experiences. Building relationships in the industry can open doors to unexpected opportunities.\n\n";
                $message .= "We truly believe in the potential you have to offer and hope that you consider reapplying when the time feels right. Should you have any questions or need further guidance, please don't hesitate to reach out to our support team at info@tendersoko.com.\n\n";
                $message .= "Once again, thank you for your interest in joining our community. We wish you the very best in all your endeavors and look forward to the possibility of collaborating with you in the future.\n\n";
                $message .= "Warm regards,\n\n";
                $message .= "The TenderSoko Team";

                $headers = "From: no-reply@tendersoko.com\r\n";
                $headers .= "Content-type: text/plain; charset=UTF-8\r\n";

                if (mail($affiliate_email, $subject, $message, $headers)) {
                    header('Location: admin_index.php?message=Affiliate rejected and email sent');
                } else {
                    error_log("Failed to send rejection email to $affiliate_email");
                    header('Location: admin_index.php?error=Affiliate rejected but failed to send email');
                }
            } else {
                error_log("Failed to update affiliate status for ID $affiliate_id: " . mysqli_error($conn));
                header('Location: admin_index.php?error=Failed to reject affiliate');
            }
        } else {
            error_log("Affiliate not found with ID $affiliate_id");
            header('Location: admin_index.php?error=Affiliate not found');
        }
    } else {
        error_log("Affiliate ID is missing in POST data");
        header('Location: admin_index.php?error=Invalid request');
    }
} else {
    error_log("Invalid request method");
    header('Location: admin_index.php?error=Invalid request method');
}
?>

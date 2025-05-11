<?php
session_start(); // Start session to store OTP

if (isset($_POST['email']) && isset($_POST['name'])) {
    $email = $_POST['email'];
    $name = $_POST['name'];

    // Generate 6-digit random OTP
    $otp = rand(100000, 999999);

    $subject = "Your OTP Code";
    $message = "
    Hi $name,<br><br>
    Your OTP for verification is: <b>$otp</b><br><br>
    Thanks,<br>
    Library Management Team
    ";
    $headers = "From: rachanabera6@gmail.com\r\n";
    $headers .= "Reply-To: rachanabera6@gmail.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($email, $subject, $message, $headers)) {
        $_SESSION['otp'] = $otp; // Store OTP in session
        $_SESSION['email'] = $email; // Store email for further steps
        echo "success"; // Return success
    } else {
        echo "failed";
    }
} else {
    echo "invalid";
}
?>

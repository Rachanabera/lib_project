<?php
session_start(); // Start session to access stored OTP

if (isset($_POST['otp'])) {
    $userOtp = $_POST['otp'];

    if (isset($_SESSION['otp']) && $userOtp == $_SESSION['otp']) {
        echo "verified"; // Correct OTP
    } else {
        echo "invalid"; // Wrong OTP
    }
} else {
    echo "no_otp";
}
?>

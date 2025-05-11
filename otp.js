document.getElementById("send-otp").addEventListener("click", function() {
    // Hide the OTP form until the email is successfully sent
    document.getElementById("otp-section").style.display = "block";
});

document.getElementById("verify-otp").addEventListener("click", function() {
    var enteredOtp = document.getElementById("otp").value;
    var sessionOtp = "<?php echo $_SESSION['otp']; ?>"; // Get OTP stored in session

    if (enteredOtp === sessionOtp) {
        alert("OTP Verified!");
        document.getElementById("proceed-btn").style.display = "inline-block"; // Show the "Proceed" button
    } else {
        alert("Invalid OTP, please try again.");
    }
});

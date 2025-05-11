<?php
$to = 'rac.ber.rt23@dypatil.edu'; // Replace with your email address
$subject = 'Test Email';
$message = 'This is a test email.';
$headers = 'From: rachanabera6@gmail.com' . "\r\n" .
           'Reply-To: rachanabera6@gmail.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully.";
} else {
    echo "Failed to send email.";
}
?> 
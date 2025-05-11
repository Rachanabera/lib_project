<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$dbname = "library"; // Replace with your actual DB name
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$name = $_POST['name'];
$sdrn = $_POST['sdrn'];
$department = $_POST['department'];
$phone = $_POST['phone_number'];
$email = $_POST['email'];
$password_raw = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Check if passwords match
if ($password_raw !== $confirm_password) {
    echo "<script>
        alert('Passwords do not match!');
        window.location.href = 'staff_signup.html';  // Redirect back to the signup page
    </script>";
    exit();
}

// Hash the password
$hashed_password = password_hash($password_raw, PASSWORD_DEFAULT);

// Check for duplicate email
$check = $conn->prepare("SELECT * FROM staff_users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "<script>
        alert('Email already exists!');
        window.location.href = 'staff_signup.html';  // Redirect back to the signup page
    </script>";
    $check->close();
    $conn->close();
    exit();
}
$check->close();

// Insert into database
$stmt = $conn->prepare("INSERT INTO staff_users (name, sdrn, department, phone_number, email, password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $name, $sdrn, $department, $phone, $email, $hashed_password);

if ($stmt->execute()) {
    // Successful signup - redirect to login page
    echo "<script>
        alert('Signup successful! Redirecting to login...');
        window.location.href = 'login.html';  // Redirect to login page
    </script>";
} else {
    // Error occurred - show error message
    echo "<script>
        alert('Error: " . $stmt->error . "');
        window.location.href = 'staff_signup.html';  // Redirect back to the signup page
    </script>";
}

$stmt->close();
$conn->close();
?>

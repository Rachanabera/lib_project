<?php
// Connect to your database
$host = "localhost";       // or your host name
$dbname = "admin_users";   // replace with your DB name
$username = "root";        // default for XAMPP
$password = "";            // default for XAMPP

$conn = new mysqli($host, $username, $password, $dbname);

// Check DB connections
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data and sanitize
$name = mysqli_real_escape_string($conn, $_POST['name']);
$sdrn = mysqli_real_escape_string($conn, $_POST['sdrn']);
$department = mysqli_real_escape_string($conn, $_POST['department']);
$phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password_raw = mysqli_real_escape_string($conn, $_POST['password']);

// Hash the password
$password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

// Prepare and execute the SQL insert statement
$sql = "INSERT INTO admin_users (name, sdrn, department, phone_number, email, password)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $name, $sdrn, $department, $phone_number, $email, $password_hashed);

if ($stmt->execute()) {
    echo "Signup successful";  // Ensure this message is returned
} else {
    echo "Error: " . $stmt->error;
}

// Close connections
$stmt->close();
$conn->close();
?>

<?php
// Database connection settings
$host = 'localhost'; // Your database host
$username = 'root';  // Your database username
$password = '';      // Your database password
$dbname = 'library'; // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // echo "Database connected successfully.<br>"; // For debugging
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = $_POST['name'];
    $ssn = $_POST['ssn'];
    $department = $_POST['department'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Password from the form

    // Check if the passwords match
    if ($password !== $_POST['confirm_password']) {
        die("Passwords do not match!");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query to insert teacher/admin data
    $sql = "INSERT INTO teachers (name, ssn, department, phone_number, email, password) 
            VALUES ('$name', '$ssn', '$department', '$phone_number', '$email', '$hashed_password')";

    // Execute the query and check if it was successful
    if ($conn->query($sql) === TRUE) {
        echo "Signup successful! The following data has been inserted:<br>";
        echo "Name: $name<br>";
        echo "SSN: $ssn<br>";
        echo "Department: $department<br>";
        echo "Phone Number: $phone_number<br>";
        echo "Email: $email<br>";
        echo "Password: $password (hashed)<br>";

        // Optionally, redirect to login page
        header("Location: login.html");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>

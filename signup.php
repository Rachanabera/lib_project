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
    // echo "Database connected successfully.<br>";
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = $_POST['name'];
    $roll_number = $_POST['roll_number'];
    $year = $_POST['year'];
    $division = $_POST['division'];
    $branch = $_POST['branch'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password']; // Password from the form

    // Check if the passwords match
    if ($password !== $_POST['confirm_password']) {
        die("Passwords do not match!");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query to insert user data
    $sql = "INSERT INTO users (name, roll_number, year, division, branch, phone_number, password) 
            VALUES ('$name', '$roll_number', '$year', '$division', '$branch', '$phone_number', '$hashed_password')";

    // Execute the query and check if it was successful
    if ($conn->query($sql) === TRUE) {
        // Redirect to login page after successful signup
        header("Location: login.html");
        exit(); // Ensure no further code is executed
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>

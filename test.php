<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "library";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test data to insert
$student_id = 1; // Example student ID
$book_id = 101;  // Example book ID
$return_date = '2025-04-26'; // Example return date

// Prepare and execute insert query
$stmt = $conn->prepare("INSERT INTO returned_books (student_id, book_id, return_date, returned_by_admin, created_at) VALUES (?, ?, ?, 1, NOW())");

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("iis", $student_id, $book_id, $return_date);

// Execute the query
if ($stmt->execute()) {
    echo "✅ Data successfully inserted into the returned_books table!";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

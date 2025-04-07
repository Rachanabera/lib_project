<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['student_id'];
    $book_id = $_POST['return_book_id'];

    // Delete from issued_books
    $delete = $conn->prepare("DELETE FROM issued_books WHERE student_id = ? AND book_id = ?");
    $delete->bind_param("ii", $student_id, $book_id);

    if ($delete->execute()) {
        // Increment available_copies AND returned_copies
        $update = $conn->prepare("UPDATE books SET available_copies = available_copies + 1, returned_copies = returned_copies + 1 WHERE id = ?");
        $update->bind_param("i", $book_id);
        if ($update->execute()) {
            echo "Student ID: $student_id <br>";
            echo "Book ID: $book_id <br>";

            header("Location: student.php");
            exit();
        } else {
            echo "❌ Failed to update book stats.";
        }
    } else {
        echo "❌ Failed to remove issued book.";
    }
}
$conn->close();
?>

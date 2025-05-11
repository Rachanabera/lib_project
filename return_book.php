<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_book_id'])) {
    $student_id = $_SESSION['student_id'];
    $book_id = $_POST['return_book_id'];

    // Update the 'return_date' for the returned book
    $stmt = $conn->prepare("UPDATE issued_books SET return_date = NOW() WHERE student_id = ? AND book_id = ? AND return_date IS NULL");
    $stmt->bind_param("ii", $student_id, $book_id);

    if ($stmt->execute()) {
        // Optionally, you can also update the number of available copies in the 'books' table
        $update_books_stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
        $update_books_stmt->bind_param("i", $book_id);
        $update_books_stmt->execute();
        $update_books_stmt->close();

        header("Location: student_dashboard.php?return=success");
        exit();
    } else {
        echo "Error returning the book.";
    }

    $stmt->close();
}
?>

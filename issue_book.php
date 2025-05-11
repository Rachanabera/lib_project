<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];

    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("❌ Connection failed: " . $conn->connect_error);
    }

    $check = $conn->prepare("SELECT available_copies FROM books WHERE id = ?");
    $check->bind_param("i", $book_id);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows == 1) {
        $row = $check_result->fetch_assoc();
        $available = $row['available_copies'];

        if ($available > 0) {
            $issue_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime('+14 days'));

            $insert = $conn->prepare("INSERT INTO issued_books (student_id, book_id, issue_date, due_date) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiss", $student_id, $book_id, $issue_date, $due_date);

            if ($insert->execute()) {
                $update = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
                $update->bind_param("i", $book_id);
                $update->execute();

                // Redirect to student page with success message
                header("Location: student.php?msg=Book issued successfully");
                exit();
            } else {
                header("Location: student.php?msg=Failed to issue book");
                exit();
            }
        } else {
            header("Location: student.php?msg=No available copies left");
            exit();
        }
    } else {
        header("Location: student.php?msg=Book not found");
        exit();
    }

    $conn->close();
} else {
    header("Location: student.php?msg=Invalid request");
    exit();
}

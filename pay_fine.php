<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id']) && isset($_POST['book_ids'])) {
    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $student_id = $_POST['student_id'];
    $book_ids = $_POST['book_ids'];

    foreach ($book_ids as $book_id) {
        $update = $conn->prepare("UPDATE issued_books SET fine_paid = 1 WHERE id = ? AND student_id = ?");
        $update->bind_param("ii", $book_id, $student_id);
        $update->execute();
        $update->close();
    }

    $conn->close();
    header("Location: student.php?payment=success");
    exit();
} else {
    header("Location: student.php?payment=failed");
    exit();
}
?>

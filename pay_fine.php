<?php
session_start();
$conn = new mysqli("localhost", "root", "", "library");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $update = $conn->prepare("UPDATE issued_books SET due_date = CURDATE() WHERE student_id = ? AND return_date IS NULL");
    $update->bind_param("i", $student_id);
    $update->execute();
}
$conn->close();
header("Location: student.php?payment=success");
exit();
?>

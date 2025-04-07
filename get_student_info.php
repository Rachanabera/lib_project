<?php
session_start();
include 'db.php';

$student_id = $_SESSION['student_id'];
$result = $conn->query("SELECT * FROM students WHERE id = $student_id");
echo json_encode($result->fetch_assoc());

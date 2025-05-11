<?php
// mark_fine_paid.php
include 'db_connection.php'; // change if your connection file has different name

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issued_book_id'])) {
    $issued_book_id = intval($_POST['issued_book_id']);

    $update_query = $conn->prepare("UPDATE issued_books SET fine_paid = 1 WHERE id = ?");
    $update_query->bind_param("i", $issued_book_id);

    if ($update_query->execute()) {
        // success
        header("Location: admin.php?fine_updated=success");
        exit();
    } else {
        // error
        echo "Error updating fine status.";
    }
}
?>

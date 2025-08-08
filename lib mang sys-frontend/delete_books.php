<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

if (!isset($_GET['book_id']) || !is_numeric($_GET['book_id'])) {
    die("Invalid book ID.");
}

$book_id = intval($_GET['book_id']);

$stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $book_id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_books.php?msg=Book+deleted+successfully");
    exit();
} else {
    $error = $stmt->error;
    $stmt->close();
    die("Error deleting book: " . $error);
}
?>

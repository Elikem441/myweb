<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = intval($_POST['borrow_id'] ?? 0);
    if ($borrow_id <= 0) {
        die("Invalid borrow ID.");
    }

    // Check if the borrow record exists and belongs to this user, and is still borrowed
    $stmt = $conn->prepare("SELECT book_id, status FROM borrowed_books WHERE borrow_id = ? AND user_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $borrow_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($book_id, $status);
    if (!$stmt->fetch()) {
        $stmt->close();
        die("Borrow record not found or access denied.");
    }
    $stmt->close();

    if ($status !== 'borrowed') {
        die("This book has already been returned.");
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update borrowed_books to mark as returned and set return_date
        $stmt = $conn->prepare("UPDATE borrowed_books SET status = 'returned', return_date = CURDATE() WHERE borrow_id = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed (update borrowed_books): " . $conn->error);
        }
        $stmt->bind_param("i", $borrow_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed (update borrowed_books): " . $stmt->error);
        }
        $stmt->close();

        // Increase available copies in books table
        $stmt = $conn->prepare("UPDATE books SET available = available + 1 WHERE book_id = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed (update books): " . $conn->error);
        }
        $stmt->bind_param("i", $book_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed (update books): " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo "Book returned successfully.";

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid request method.");
}

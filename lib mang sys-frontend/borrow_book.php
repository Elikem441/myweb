<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate POST input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = intval($_POST['book_id'] ?? 0);
    if ($book_id <= 0) {
        die("Invalid book ID.");
    }

    // Check if the book exists and has available copies
    $stmt = $conn->prepare("SELECT available FROM books WHERE book_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->bind_result($available);
    if (!$stmt->fetch()) {
        $stmt->close();
        die("Book not found.");
    }
    $stmt->close();

    if ($available <= 0) {
        die("No copies available to borrow.");
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert borrow record
        $stmt = $conn->prepare("INSERT INTO borrowed_books (user_id, book_id, borrow_date, status) VALUES (?, ?, CURDATE(), 'borrowed')");
        if ($stmt === false) {
            throw new Exception("Prepare failed (insert borrow): " . $conn->error);
        }
        $stmt->bind_param("ii", $user_id, $book_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed (insert borrow): " . $stmt->error);
        }
        $stmt->close();

        // Decrease available copies
        $stmt = $conn->prepare("UPDATE books SET available = available - 1 WHERE book_id = ?");
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

        echo "Book borrowed successfully.";

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid request method.");
}

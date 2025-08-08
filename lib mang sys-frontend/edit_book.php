<?php
session_start();
include 'db.php';

// Only admin can edit books
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

$message = "";
$categories = ['Fiction', 'Non-Fiction', 'Science', 'Biography', 'History', 'Children', 'Fantasy'];

if (!isset($_GET['book_id']) || !is_numeric($_GET['book_id'])) {
    die("Invalid book ID.");
}

$book_id = intval($_GET['book_id']);

// Fetch current book data
$stmt = $conn->prepare("SELECT title, author, category, quantity, available FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Book not found.");
}

$book = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);

    if ($title === '' || $author === '') {
        $message = "Title and author are required.";
    } elseif ($quantity <= 0) {
        $message = "Quantity must be at least 1.";
    } elseif (!in_array($category, $categories)) {
        $message = "Please select a valid category.";
    } else {
        // Calculate new available copies based on difference between old and new quantity
        $quantity_diff = $quantity - $book['quantity'];
        $new_available = $book['available'] + $quantity_diff;
        if ($new_available < 0) {
            $message = "Cannot reduce quantity below borrowed books.";
        } else {
            $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, category = ?, quantity = ?, available = ? WHERE book_id = ?");
            if ($stmt === false) {
                $message = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param("sssiii", $title, $author, $category, $quantity, $new_available, $book_id);
                if ($stmt->execute()) {
                    $message = "✅ Book updated successfully.";
                    // Refresh book info
                    $book['title'] = $title;
                    $book['author'] = $author;
                    $book['category'] = $category;
                    $book['quantity'] = $quantity;
                    $book['available'] = $new_available;
                } else {
                    $message = "Error updating book: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Book</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: auto; background: #f9f9f9; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-top: 4px; }
        button { margin-top: 15px; padding: 10px 15px; background: #3498db; border: none; color: white; cursor: pointer; }
        .message { margin-top: 15px; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Edit Book</h1>

    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="title">Title</label>
        <input id="title" name="title" type="text" required value="<?php echo htmlspecialchars($book['title']); ?>">

        <label for="author">Author</label>
        <input id="author" name="author" type="text" required value="<?php echo htmlspecialchars($book['author']); ?>">

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($book['category'] === $cat) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="quantity">Quantity</label>
        <input id="quantity" name="quantity" type="number" min="1" value="<?php echo intval($book['quantity']); ?>" required>

        <p><strong>Available Copies:</strong> <?php echo intval($book['available']); ?></p>

        <button type="submit">Update Book</button>
    </form>

    <p><a href="manage_books.php">Back to Manage Books</a></p>
</body>
</html>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

// Only admin can add books
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

$message = "";

// Predefined categories
$categories = ['Fiction', 'Non-Fiction', 'Science', 'Biography', 'History', 'Children', 'Fantasy'];

// Handle add book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_book') {
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
        // Prepare insert with category as string
        $stmt = $conn->prepare("
            INSERT INTO books (title, author, category, quantity, available, added_date)
            VALUES (?, ?, ?, ?, ?, CURDATE())
        ");

        if ($stmt === false) {
            $message = "Prepare failed: " . $conn->error;
        } else {
            $available = $quantity;
            $stmt->bind_param("sssii", $title, $author, $category, $quantity, $available);

            if ($stmt->execute()) {
                $message = "✅ Book added successfully.";
            } else {
                $message = "Error adding book: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Book</title>
    <link rel="stylesheet" href="add.css">
    <script src="notify.js"></script>
</head>
<body>
<div class="form-container">
    <h1>Add New Book</h1>

    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message,'Error')===false)?'success':'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="action" value="add_book">

        <label for="title">Title</label>
        <input id="title" name="title" type="text" required>

        <label for="author">Author</label>
        <input id="author" name="author" type="text" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="">-- Select category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="quantity">Quantity</label>
        <input id="quantity" name="quantity" type="number" min="1" value="1" required>

        <button class="btn btn-primary" type="submit">Add Book</button>
        <a class="btn btn-secondary" href="manage_books.php">Back</a>
    </form>
</div>
</body>
</html>

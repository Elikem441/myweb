<?php
session_start();
include 'db.php';

// Restrict to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

// Fetch all books
$query = "SELECT book_id, title, author, category, available, quantity FROM books";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Manage Books</title>
    <link rel="stylesheet" href="admin.css" />
    <style>
      /* Simple notification styles */
      #notification {
        position: fixed;
        top: 20px; right: 20px;
        padding: 15px 25px;
        border-radius: 4px;
        font-weight: bold;
        color: white;
        display: none;
        z-index: 1000;
      }
      #notification.success { background-color: #28a745; }
      #notification.error { background-color: #dc3545; }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Library</h2>
    <ul>
        <li class="active"><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="manage_members.php">Manage Members</a></li>
        <li><a href="manage_books.php">Manage Books</a></li>
        <li><a href="report.php">Reports</a></li>
        <li><a href="admin_profile.php">My Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="container">
        <div class="top-bar">
            <h1>Manage Books</h1>
            <a href="add_book.php" class="btn btn-add">+ Add Book</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Title</th><th>Author</th><th>Category</th><th>Actions</th>
                </tr>
            </thead>
            <tbody id="books-table-body">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr id="book-row-<?= $row['book_id'] ?>">
                            <td><?= $row['book_id'] ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['author']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td>
                                <a href="edit_book.php?book_id=<?= $row['book_id'] ?>" class="btn btn-edit">Edit</a>
                                <button class="btn btn-delete" data-book-id="<?= $row['book_id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No books found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="notification"></div>

<script>
    // Show notification popup
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = '';
        notification.classList.add(type);
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    // Handle Delete button clicks
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', () => {
            if (!confirm('Are you sure you want to delete this book?')) return;
            
            const bookId = button.getAttribute('data-book-id');
            fetch('delete_book_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'book_id=' + encodeURIComponent(bookId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    const row = document.getElementById('book-row-' + bookId);
                    if (row) row.remove();
                    showNotification('Book deleted successfully.', 'success');
                } else {
                    showNotification(data.message || 'Failed to delete book.', 'error');
                }
            })
            .catch(() => {
                showNotification('An error occurred.', 'error');
            });
        });
    });
</script>
</body>
</html>

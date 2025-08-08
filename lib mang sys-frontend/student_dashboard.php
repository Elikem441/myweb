<?php
// Start session
session_start();

// Include database connection
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// After session_start() and before HTML output
$notify_message = $_SESSION['notify_message'] ?? '';
$notify_type = $_SESSION['notify_type'] ?? 'info';

// Clear messages after getting them so they don't repeat
unset($_SESSION['notify_message'], $_SESSION['notify_type']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Library - Student Dashboard</title>
    <link rel="stylesheet" href="student.css" />
    <script src="notify.js"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Library</h2>
        <ul>
            <li class="active"><a href="#" onclick="switchTab('browse', this)">Dashboard</a></li>
            <li><a href="#" onclick="switchTab('borrowed', this)">My Books</a></li>
            <li><a href="history.php">History</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Student Dashboard</h1>
        </div>

        <div class="tabs">
            <div class="tab active" data-tab="browse">Browse Books</div>
            <div class="tab" data-tab="borrowed">Borrowed Books</div>
        </div>

        <div id="browse" class="tab-content active">
            <div class="search-bar">
                <input type="text" placeholder="Search for books..." />
                <button>Search</button>
            </div>

            <div class="card">
                <h3>Available Books</h3>
                <?php
                $sql = "SELECT * FROM books WHERE available > 0";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    echo '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse;">';
                    echo '<thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Available Copies</th><th>Action</th></tr></thead><tbody>';

                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['author']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                        echo '<td>' . intval($row['available']) . '</td>';
                        echo '<td>
                                <form method="post" action="borrow_book.php" style="margin:0;">
                                    <input type="hidden" name="book_id" value="' . intval($row['book_id']) . '" />
                                    <button type="submit" class="btn btn-borrow">Borrow</button>
                                </form>
                              </td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo '<p>No books available.</p>';
                }
                ?>
            </div>
        </div>

        <div id="borrowed" class="tab-content">
            <div class="card">
                <h3>Books You\'ve Borrowed</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT bb.borrow_id, bb.borrow_date, bb.return_date, bb.status, b.title, b.author 
                                FROM borrowed_books bb 
                                JOIN books b ON bb.book_id = b.book_id 
                                WHERE bb.user_id = $user_id 
                                ORDER BY bb.borrow_date DESC";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['author']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['borrow_date']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['return_date']) . '</td>';
                                echo '<td>' . htmlspecialchars(ucfirst($row['status'])) . '</td>';
                                echo '<td>';
                                if ($row['status'] === 'borrowed') {
                                    echo '<form method="post" action="return_book.php" style="margin:0;">';
                                    echo '<input type="hidden" name="borrow_id" value="' . intval($row['borrow_id']) . '" />';
                                    echo '<button class="btn btn-return" type="submit">Return</button>';
                                    echo '</form>';
                                } else {
                                    echo '<button class="btn" disabled>Returned</button>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6">No borrowed books.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
                document.querySelectorAll('.sidebar li').forEach(li => li.classList.remove('active'));
                if(tabId === 'browse') {
                    document.querySelector('.sidebar li:nth-child(1)').classList.add('active');
                } else if(tabId === 'borrowed') {
                    document.querySelector('.sidebar li:nth-child(2)').classList.add('active');
                }
            });
        });

        function switchTab(tabId, el) {
            document.querySelectorAll('.sidebar li').forEach(li => li.classList.remove('active'));
            el.parentElement.classList.add('active');
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }
    </script>
    <?php if ($notify_message): ?>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    showNotification(<?php echo json_encode($notify_message); ?>, <?php echo json_encode($notify_type); ?>);
  });
</script>
<?php endif; ?>

</body>
</html>

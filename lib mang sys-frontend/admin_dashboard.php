<?php
session_start();
include 'db.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

// Fetch total members (users with role 'user')
$membersResult = $conn->query("SELECT COUNT(*) AS total_members FROM users WHERE role='user'");
$total_members = $membersResult->fetch_assoc()['total_members'] ?? 0;

// Fetch total books
$booksResult = $conn->query("SELECT COUNT(*) AS total_books FROM books");
$total_books = $booksResult->fetch_assoc()['total_books'] ?? 0;

// Fetch borrowed books
$borrowedResult = $conn->query("SELECT COUNT(*) AS borrowed_books FROM borrowed_books");
$borrowed_books = $borrowedResult->fetch_assoc()['borrowed_books'] ?? 0;

// Fetch recent members
$recentMembers = $conn->query("SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css" />
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

        <div class="cards">
            <div class="card">
                <h3>Total Members</h3>
                <p><?php echo $total_members; ?></p>
            </div>
            <div class="card">
                <h3>Total Books</h3>
                <p><?php echo $total_books; ?></p>
            </div>
            <div class="card">
                <h3>Borrowed Books</h3>
                <p><?php echo $borrowed_books; ?></p>
            </div>
        </div>

        <div class="card">
            <h2>Recent Members</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($member = $recentMembers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td><?php echo htmlspecialchars($member['role']); ?></td>
                            <td><?php echo date("F j, Y", strtotime($member['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

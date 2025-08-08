<?php
session_start();
include 'db.php';

// Restrict to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

// Fetch all members
$query = "SELECT user_id, name, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Manage Members</title>
    <link rel="stylesheet" href="admin.css" />
    <style>
      /* Notification styles same as before */
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
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li class="active"><a href="manage_members.php">Manage Members</a></li>
        <li><a href="manage_books.php">Manage Books</a></li>
        <li><a href="report.php">Reports</a></li>
        <li><a href="admin_profile.php">My Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>Manage Members</h1>
    </div>

    <div class="container">
        <div class="top-bar">
            <a href="add_member.php" class="btn btn-add">+ Add Member</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th>
                </tr>
            </thead>
            <tbody id="members-table-body">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr id="member-row-<?= $row['user_id'] ?>">
                            <td><?= $row['user_id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td><?= date("F j, Y", strtotime($row['created_at'])) ?></td>
                            <td>
                                <a href="edit_members.php?user_id=<?= $row['user_id'] ?>" class="btn btn-edit">Edit</a>
                                <button class="btn btn-delete" data-user-id="<?= $row['user_id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No members found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="notification"></div>

<script>
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = '';
        notification.classList.add(type);
        notification.style.display = 'block';
        setTimeout(() => { notification.style.display = 'none'; }, 3000);
    }

    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', () => {
            if (!confirm('Are you sure you want to delete this member?')) return;

            const userId = button.getAttribute('data-user-id');
            fetch('delete_member_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user_id=' + encodeURIComponent(userId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.getElementById('member-row-' + userId);
                    if (row) row.remove();
                    showNotification('Member deleted successfully.', 'success');
                } else {
                    showNotification(data.message || 'Failed to delete member.', 'error');
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

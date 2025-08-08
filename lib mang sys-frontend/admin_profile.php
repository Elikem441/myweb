<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $updated_name = trim($_POST['name']);
        $updated_email = trim($_POST['email']);

        $updateQuery = "UPDATE users SET name = ?, email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssi", $updated_name, $updated_email, $user_id);
        if ($stmt->execute()) {
            $_SESSION['name'] = $updated_name;
            $message = "✅ Profile updated successfully.";
        } else {
            $message = "❌ Failed to update profile.";
        }
        $stmt->close();
    }

    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if ($new !== $confirm) {
            $message = "❌ New passwords do not match.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();

            if (password_verify($current, $hashed_password)) {
                $new_hashed = password_hash($new, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $update->bind_param("si", $new_hashed, $user_id);
                if ($update->execute()) {
                    $message = "✅ Password changed successfully.";
                } else {
                    $message = "❌ Failed to change password.";
                }
                $update->close();
            } else {
                $message = "❌ Current password is incorrect.";
            }
        }
    }
}

// Fetch updated user info
$stmt = $conn->prepare("SELECT name, email, role, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $role, $created_at);
$stmt->fetch();
$stmt->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - My Profile</title>
    <style>
        /* styles as provided earlier */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            padding: 20px;
            box-sizing: border-box;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            padding: 10px;
            margin-bottom: 5px;
            cursor: pointer;
            border-radius: 4px;
        }
        .sidebar li:hover, .sidebar li.active {
            background-color: #34495e;
        }
        .sidebar li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .profile-header {
            margin-bottom: 20px;
        }
        .profile-info h2 {
            margin: 0;
            color: #2c3e50;
        }
        .profile-info p {
            margin: 5px 0;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge.admin {
            background-color: #3498db;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        .tab.active {
            border-bottom: 3px solid #3498db;
            color: #3498db;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
    </style>
    <script src="notify.js"></script>
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
            <h1>My Profile</h1>
        </div>

        <div class="card">
            <div class="profile-header">
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($name); ?></h2>
                    <p><?php echo htmlspecialchars($email); ?></p>
                    <p>Member since: <?php echo htmlspecialchars(date("F j, Y", strtotime($created_at))); ?></p>
                    <span class="badge <?php echo $role === 'admin' ? 'admin' : 'student'; ?>"><?php echo strtoupper($role); ?></span>
                </div>
            </div>

            <div class="tabs">
                <div class="tab active" data-tab="personal">Personal Info</div>
                <div class="tab" data-tab="security">Security</div>
            </div>

            <div id="personal" class="tab-content active">
                <form method="POST">
    <div class="form-group">
        <label for="fullname">Full Name</label>
        <input type="text" name="name" id="fullname" value="<?php echo htmlspecialchars($name); ?>">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
    </div>
    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
</form>

            </div>

            <div id="security" class="tab-content">
<form method="POST">
    <div class="form-group">
        <label for="current-password">Current Password</label>
        <input type="password" name="current_password" id="current-password" required>
    </div>
    <div class="form-group">
        <label for="new-password">New Password</label>
        <input type="password" name="new_password" id="new-password" required>
    </div>
    <div class="form-group">
        <label for="confirm-password">Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm-password" required>
    </div>
    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
</form>

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
            });
        });
    </script>
</body>
</html>

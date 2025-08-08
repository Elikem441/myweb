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
    <link rel="stylesheet" href="profile.css">
    <script src="notify.js"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Library</h2>
        <ul>
            <li><a href="student_dashboard.php">Dashboard</a></li>
            <li><a href="student_dashboard.php#borrowed">My Books</a></li>
            <li><a href="history.php">History</a></li>
            <li class="active"><a href="profile.php">Profile</a></li>
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

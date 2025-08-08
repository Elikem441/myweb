<?php
session_start();
include 'db.php';

// Restrict to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

$message = "";

// Allowed roles to prevent invalid input
$allowed_roles = ['admin', 'user'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = trim($_POST['role'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    // Basic validations
    if ($name === '' || $email === '' || $role === '' || $password_raw === '') {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
    } elseif (!in_array($role, $allowed_roles)) {
        $message = "Invalid role selected.";
    } else {
        // Hash the password
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt === false) {
            $message = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("ssss", $name, $email, $password, $role);

            if ($stmt->execute()) {
                $message = "✅ Member added successfully!";
            } else {
                if ($conn->errno == 1062) {
                    $message = "Error: Email already exists.";
                } else {
                    $message = "Error: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Member</title>
    <link rel="stylesheet" href="add.css">
    <script src="notify.js"></script>
</head>
<body>
    <div class="form-container">
        <h1>Add New Member</h1>
        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required><br><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="role">Role:</label><br>
            <select id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select><br><br>

            <button type="submit" class="btn btn-primary">Add Member</button>
            <a href="manage_members.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</body>
</html>

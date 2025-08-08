<?php
session_start();
include 'db.php';

// Only admin can edit members
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

$message = "";
$allowed_roles = ['admin', 'librarian', 'student'];

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET['user_id']);

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $role === '') {
        $message = "Name, email, and role are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
    } elseif (!in_array($role, $allowed_roles)) {
        $message = "Invalid role selected.";
    } else {
        // Check if email belongs to another user (unique constraint)
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Email is already used by another user.";
            $stmt->close();
        } else {
            $stmt->close();
            if ($password_raw !== '') {
                $password = password_hash($password_raw, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE user_id = ?");
                $stmt->bind_param("ssssi", $name, $email, $role, $password, $user_id);
            } else {
                // Password not changed
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE user_id = ?");
                $stmt->bind_param("sssi", $name, $email, $role, $user_id);
            }
            if ($stmt->execute()) {
                $message = "✅ Member updated successfully.";
                $user['name'] = $name;
                $user['email'] = $email;
                $user['role'] = $role;
            } else {
                $message = "Error updating member: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Member</title>
     <style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h1 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.message {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-weight: bold;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

form {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

label {
    display: block;
    margin: 15px 0 5px;
    font-weight: 600;
    color: #2c3e50;
}

input[type="text"],
input[type="number"],
select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    box-sizing: border-box;
    transition: border-color 0.3s;
}

input[type="text"]:focus,
input[type="number"]:focus,
select:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

button[type="submit"] {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 20px;
    transition: background-color 0.3s;
    width: 100%;
    font-weight: 600;
}

button[type="submit"]:hover {
    background-color: #2980b9;
}

p {
    margin: 15px 0;
}

p strong {
    color: #2c3e50;
}

a {
    color: #3498db;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
    transition: color 0.3s;
}

a:hover {
    color: #2980b9;
    text-decoration: underline;
}

@media (max-width: 600px) {
    body {
        padding: 15px;
    }
    
    form {
        padding: 15px;
    }
}
</style>

    
</head>
<body>
    <h1>Edit Member</h1>

    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" required value="<?php echo htmlspecialchars($user['name']); ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($user['email']); ?>">

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="">-- Select Role --</option>
            <?php foreach ($allowed_roles as $r): ?>
                <option value="<?php echo htmlspecialchars($r); ?>" <?php echo ($user['role'] === $r) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(ucfirst($r)); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="password">Password (leave blank to keep current)</label>
        <input id="password" name="password" type="password" placeholder="New password">

        <button type="submit">Update Member</button>
    </form>

    <p><a href="manage_members.php">Back to Manage Members</a></p>
</body>
</html>

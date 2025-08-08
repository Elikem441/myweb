<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "library_sys");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$new_name = trim($_POST['name']);
$new_email = trim($_POST['email']);

$sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $new_name, $new_email, $user_id);

if ($stmt->execute()) {
    $_SESSION['name'] = $new_name; // update session name
    echo "✅ Profile updated successfully.";
} else {
    echo "❌ Error updating profile.";
}

$conn->close();
?>

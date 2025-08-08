<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_members.php?msg=Member+deleted+successfully");
    exit();
} else {
    $error = $stmt->error;
    $stmt->close();
    die("Error deleting member: " . $error);
}
?>

<?php
session_start();

// Enable detailed error reporting during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Connect to the database
    $connection = mysqli_connect("localhost", "root", "", "library_sys");

    if (!$connection) {
        die("❌ Database connection failed: " . mysqli_connect_error());
    }

    // Check if user exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Compare password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role']; // 'admin' or 'user'

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php"); 
            }
            exit();
        } else {
            echo "❌ Incorrect password.";
        }
    } else {
        echo "❌ No account found with that email.";
    }

    mysqli_close($connection);
} else {
    echo "❌ Invalid request.";
}
?>

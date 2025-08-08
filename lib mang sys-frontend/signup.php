<?php
// Enable full error reporting (development mode only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Database configuration
$host = "localhost";
$dbname = "library_sys";       // Use your actual DB name
$username = "root";            // Your DB username
$password = "";                // Your DB password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm-password"] ?? '';
    $role = $_POST["role"] ?? '';

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        echo "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        echo "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo "Email is already registered.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                if ($insert) {
                    $insert->bind_param("ssss", $name, $email, $hashed_password, $role);
                    if ($insert->execute()) {
                        echo "Signup successful. You can now <a href='signin.html'>Sign In</a>.";
                    } else {
                        echo "Insert Error: " . $insert->error;
                    }
                    $insert->close();
                } else {
                    echo "Insert Prepare Error: " . $conn->error;
                }
            }
            $stmt->close();
        } else {
            echo "Select Prepare Error: " . $conn->error;
        }
    }
}

// Close connection
$conn->close();
?>

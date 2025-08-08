<?php
session_start();
require_once "db.php";

// Redirect if user not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: signin.html");
    exit();
}

$user_id = $_SESSION["user_id"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - My History</title>
    <style>
        /* Use the same styles as student_dashboard.css for consistency */
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
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-returned {
            color: #28a745;
        }
        .status-overdue {
            color: #dc3545;
        }
        .no-history {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
    <script src="notify.js"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Library</h2>
        <ul>
            <li><a href="student_dashboard.php">Dashboard</a></li>
            <li><a href="student_dashboard.php#borrowed">My Books</a></li>
            <li class="active"><a href="history.php">History</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Borrowing History</h1>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT b.title, b.author, bb.borrow_date, bb.return_date, bb.status
                          FROM borrowed_books bb
                          JOIN books b ON bb.book_id = b.book_id
                          WHERE bb.user_id = ?
                          ORDER BY bb.borrow_date DESC";

                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_class = ($row["status"] === "Returned Late") ? "status-overdue" : "status-returned";
                        echo "<tr>
                                <td>" . htmlspecialchars($row["title"]) . "</td>
                                <td>" . htmlspecialchars($row["author"]) . "</td>
                                <td>" . htmlspecialchars($row["borrow_date"]) . "</td>
                                <td>" . htmlspecialchars($row["return_date"]) . "</td>
                                <td class='" . $status_class . "'>" . htmlspecialchars($row["status"]) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'><div class='no-history'>
                            <h3>No borrowing history yet</h3>
                            <p>Your borrowed books will appear here after you return them.</p>
                          </div></td></tr>";
                }

                $stmt->close();
                ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
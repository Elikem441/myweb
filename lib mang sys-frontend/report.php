<?php
session_start();
include 'db.php';

// Restrict access to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.html");
    exit();
}

// Fetch total books
$totalBooksResult = $conn->query("SELECT COUNT(*) AS total_books FROM books");
$total_books = $totalBooksResult && $totalBooksResult->num_rows > 0 
    ? (int)$totalBooksResult->fetch_assoc()['total_books'] 
    : 0;

// Fetch borrowed books
$borrowedBooksResult = $conn->query("SELECT COUNT(*) AS borrowed_books FROM borrowed_books");
$borrowed_books = $borrowedBooksResult && $borrowedBooksResult->num_rows > 0 
    ? (int)$borrowedBooksResult->fetch_assoc()['borrowed_books'] 
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Report</title>
    <link rel="stylesheet" href="report.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="notify.js"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Library</h2>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_members.php">Manage Members</a></li>
            <li><a href="manage_books.php">Manage Books</a></li>
            <li class="active"><a href="report.php">Reports</a></li>
            <li><a href="admin_profile.php">My Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Library Report</h1>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <h3>Total Books</h3>
                <p><?php echo $total_books; ?></p>
            </div>
            <div class="summary-card">
                <h3>Borrowed Books</h3>
                <p><?php echo $borrowed_books; ?></p>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="booksChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('booksChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total Books', 'Borrowed Books'],
                datasets: [{
                    label: 'Number of Books',
                    data: [<?php echo $total_books; ?>, <?php echo $borrowed_books; ?>],
                    backgroundColor: ['#4CAF50', '#FF9800']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>

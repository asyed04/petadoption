<?php
session_start();
require_once '../includes/db_connect.php';  // Include the DB connection

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch total metrics
$total_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets"))['total'];
$total_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_applications"))['total'];

// Fetch application statuses
$pending_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_applications WHERE status = 'pending'"))['total'];
$approved_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_applications WHERE status = 'approved'"))['total'];
$rejected_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_applications WHERE status = 'rejected'"))['total'];

// Fetch pet statuses
$available_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets WHERE status_id = (SELECT id FROM statuses WHERE status_name = 'available')"))['total'];
$adopted_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets WHERE status_id = (SELECT id FROM statuses WHERE status_name = 'adopted')"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <div>
        <h2>Summary</h2>
        <ul>
            <li>Total Pets: <?php echo $total_pets; ?></li>
            <li>Available Pets: <?php echo $available_pets; ?></li>
            <li>Adopted Pets: <?php echo $adopted_pets; ?></li>
            <li>Total Applications: <?php echo $total_applications; ?></li>
            <li>Pending Applications: <?php echo $pending_applications; ?></li>
            <li>Approved Applications: <?php echo $approved_applications; ?></li>
            <li>Rejected Applications: <?php echo $rejected_applications; ?></li>
        </ul>
    </div>

    <div>
        <h2>Quick Links</h2>
        <ul>
            <li><a href="list_pets.php">Manage Pets</a></li>
            <li><a href="admin_view_applications.php">Manage Applications</a></li> <!-- Link added here -->
            <li><a href="../logout.php">Logout</a></li>
            <li><a href="admin_comments.php">Moderate Comments</a></li>
        </ul>
    </div>
</body>
</html>

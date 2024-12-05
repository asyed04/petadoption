<?php
session_start();
require_once '../includes/db_connect.php';  // Include the DB connection

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Query to get all pets and their statuses from the database
$sql = "SELECT pets.*, statuses.status_name FROM pets
        JOIN statuses ON pets.status_id = statuses.id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error fetching pets: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - List of Pets</title>
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Link to your CSS -->
</head>
<body>
    <div class="admin-dashboard">
        <h2>Admin Dashboard - List of Pets</h2>
        <a href="admin_add_pet.php" class="btn">Add New Pet</a>  <!-- Link to add a new pet -->

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pet Name</th>
                    <th>Species</th> <!-- Changed "Category" to "Species" -->
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($pet = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>{$pet['id']}</td>
                                <td>{$pet['name']}</td>
                                <td>{$pet['species']}</td> <!-- Assuming 'species' is the correct column -->
                                <td>{$pet['status_name']}</td>
                                <td>
                                    <a href='admin_edit_pet.php?id={$pet['id']}' class='btn'>Edit</a>
                                    <a href='admin_delete_pet.php?id={$pet['id']}' class='btn'>Delete</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No pets available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

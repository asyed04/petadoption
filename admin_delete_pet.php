<?php
session_start();
require_once '../includes/db_connect.php';  // Include the DB connection

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if pet ID is provided in the URL
if (!isset($_GET['id'])) {
    header("Location: list_pets.php");
    exit();
}

$pet_id = $_GET['id'];

// Fetch the pet details for confirmation before deletion
$sql_pet = "SELECT * FROM pets WHERE id = '$pet_id'";
$result_pet = mysqli_query($conn, $sql_pet);

// Check if the pet exists
if (mysqli_num_rows($result_pet) == 0) {
    header("Location: list_pets.php");
    exit();
}

$pet = mysqli_fetch_assoc($result_pet);

// Handle pet deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete the pet from the database
    $sql_delete = "DELETE FROM pets WHERE id = '$pet_id'";

    if (mysqli_query($conn, $sql_delete)) {
        // If the pet has an image, delete the image from the server
        if ($pet['image'] && file_exists($pet['image'])) {
            unlink($pet['image']);
        }
        
        // Redirect to the pets list page after successful deletion
        header("Location: list_pets.php");
        exit();
    } else {
        $error_message = 'Error: Could not delete the pet from the database.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Pet</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-dashboard">
        <h2>Delete Pet</h2>

        <!-- Display error message if deletion fails -->
        <?php if (isset($error_message)) : ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <p>Are you sure you want to delete the following pet?</p>

        <div class="pet-details">
            <p><strong>Pet Name:</strong> <?php echo $pet['name']; ?></p>
            <p><strong>Category:</strong> <?php echo $pet['category_id']; ?></p>
            <p><strong>Status:</strong> <?php echo $pet['status_id']; ?></p>
            <p><strong>Image:</strong> <img src="../<?php echo $pet['image']; ?>" alt="Pet Image" width="150"></p>
        </div>

        <!-- Confirmation form -->
        <form action="admin_delete_pet.php?id=<?php echo $pet_id; ?>" method="post">
            <button type="submit" class="btn-danger">Delete Pet</button>
        </form>

        <a href="list_pets.php" class="btn">Back to Pets List</a>
    </div>
</body>
</html>

<?php
session_start();
require_once '../includes/db_connect.php';  // Include the DB connection

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch the pet to edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM pets WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $pet = mysqli_fetch_assoc($result);

    if (!$pet) {
        die('Pet not found.');
    }
} else {
    die('Invalid pet ID.');
}

// Initialize variables
$name = $pet['name'];
$species = $pet['species'];
$category_id = $pet['category_id'];
$status_id = $pet['status_id'];
$image = $pet['image'];
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $species = mysqli_real_escape_string($conn, $_POST['species']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $status_id = mysqli_real_escape_string($conn, $_POST['status_id']);

    // Handle image upload if a new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $upload_dir = '../uploads/pet_images/';
        $image_path = $upload_dir . $image_name;

        // Move the uploaded file
        if (move_uploaded_file($image_tmp, $image_path)) {
            $image = $image_path;
        } else {
            $errors[] = 'Failed to upload the image.';
        }
    }

    // Update the pet in the database
    if (empty($errors)) {
        $sql_update = "UPDATE pets SET 
                       name = '$name', 
                       species = '$species',
                       category_id = '$category_id', 
                       status_id = '$status_id', 
                       image = '$image'
                       WHERE id = $id";

        if (mysqli_query($conn, $sql_update)) {
            header('Location: list_pets.php'); // Redirect on success
            exit();
        } else {
            $errors[] = 'Error: Could not update the pet.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pet</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Edit Pet</h2>
    <form action="admin_edit_pet.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
        <label for="name">Pet Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="species">Species:</label>
        <input type="text" id="species" name="species" value="<?php echo htmlspecialchars($species); ?>" required>

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <option value="">Select Category</option>
            <?php
            $categories = mysqli_query($conn, "SELECT * FROM categories");
            while ($category = mysqli_fetch_assoc($categories)) {
                $selected = ($category_id == $category['id']) ? 'selected' : '';
                echo "<option value='{$category['id']}' $selected>{$category['category_name']}</option>";
            }
            ?>
        </select>

        <label for="status_id">Status:</label>
        <select name="status_id" id="status_id" required>
            <option value="">Select Status</option>
            <?php
            $statuses = mysqli_query($conn, "SELECT * FROM statuses");
            while ($status = mysqli_fetch_assoc($statuses)) {
                $selected = ($status_id == $status['id']) ? 'selected' : '';
                echo "<option value='{$status['id']}' $selected>{$status['status_name']}</option>";
            }
            ?>
        </select>

        <label for="image">Pet Image:</label>
        <input type="file" name="image" id="image" accept="image/*">
        <?php if ($image): ?>
            <p>Current Image: <img src="<?php echo $image; ?>" alt="Pet Image" width="100"></p>
        <?php endif; ?>

        <button type="submit">Update Pet</button>
    </form>
    <a href="list_pets.php">Back to Pets List</a>
</body>
</html>

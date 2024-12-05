<?php
session_start();
require_once '../includes/db_connect.php';  // Include the DB connection

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch categories and statuses to populate the select fields
$sql_categories = "SELECT * FROM categories";
$result_categories = mysqli_query($conn, $sql_categories);

$sql_statuses = "SELECT * FROM statuses";
$result_statuses = mysqli_query($conn, $sql_statuses);

// Initialize variables
$name = $species = $category_id = $status_id = $image = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form Validation
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $species = mysqli_real_escape_string($conn, $_POST['species']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $status_id = mysqli_real_escape_string($conn, $_POST['status_id']);

    // Image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = uniqid() . "_" . basename($_FILES['image']['name']); // Use a unique filename
        $upload_dir = '../uploads/pet_images/';
        $image_relative_path = 'uploads/pet_images/' . $image_name; // Store relative path
        $image_full_path = $upload_dir . $image_name;

        // Ensure the upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
        }

        // Move the uploaded file to the correct location
        if (!move_uploaded_file($image_tmp, $image_full_path)) {
            $errors[] = 'Failed to upload the image.';
        }
    } else {
        // Assign a placeholder image if no image was uploaded
        $image_relative_path = 'assets/images/default_pet.jpg'; // Replace with your actual placeholder image path
    }

    // Insert into database
    if (empty($errors)) {
        $sql_insert = "INSERT INTO pets (name, species, category_id, status_id, image) 
                       VALUES ('$name', '$species', '$category_id', '$status_id', '$image_relative_path')";

        if (mysqli_query($conn, $sql_insert)) {
            header('Location: list_pets.php'); // Redirect on success
            exit();
        } else {
            $errors[] = 'Error: Could not add the pet.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Pet</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Add New Pet</h2>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="admin_add_pet.php" method="post" enctype="multipart/form-data">
        <label for="name">Pet Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="species">Species:</label>
        <input type="text" id="species" name="species" required>

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <option value="">Select Category</option>
            <?php while ($category = mysqli_fetch_assoc($result_categories)) : ?>
                <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="status_id">Status:</label>
        <select name="status_id" id="status_id" required>
            <option value="">Select Status</option>
            <?php while ($status = mysqli_fetch_assoc($result_statuses)) : ?>
                <option value="<?php echo $status['id']; ?>"><?php echo $status['status_name']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="image">Pet Image:</label>
        <input type="file" name="image" id="image" accept="image/*">

        <button type="submit">Add Pet</button>
    </form>
    <a href="list_pets.php">Back to Pets List</a>
</body>
</html>

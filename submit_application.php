<?php
require_once '../includes/db_connect.php';  // Include the DB connection

// Initialize variables
$pet_id = $_GET['pet_id'] ?? null;
$name = $email = $phone = '';
$errors = [];
$success_message = '';

// Fetch pet details
if ($pet_id) {
    $sql = "SELECT * FROM pets WHERE id = $pet_id";
    $result = mysqli_query($conn, $sql);
    $pet = mysqli_fetch_assoc($result);

    if (!$pet) {
        die('Pet not found.');
    }
} else {
    die('No pet selected for adoption.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Validate inputs
    if (empty($name)) $errors[] = 'Your name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (empty($phone) || !preg_match('/^\+?\d{10,15}$/', $phone)) {
        $errors[] = 'A valid phone number is required (e.g., +1234567890).';
    }

    if (empty($errors)) {
        // Check if adopter already exists
        $sql_check_adopter = "SELECT id FROM adopters WHERE email = '$email'";
        $result_check = mysqli_query($conn, $sql_check_adopter);

        if (mysqli_num_rows($result_check) > 0) {
            $adopter = mysqli_fetch_assoc($result_check);
            $adopter_id = $adopter['id'];
        } else {
            // Insert into the `adopters` table if not exists
            $sql_insert_adopter = "INSERT INTO adopters (name, email, phone) VALUES ('$name', '$email', '$phone')";
            if (mysqli_query($conn, $sql_insert_adopter)) {
                $adopter_id = mysqli_insert_id($conn); // Get the ID of the newly inserted adopter
            } else {
                $errors[] = 'Error saving adopter details: ' . mysqli_error($conn);
            }
        }

        // Insert into the `adoption_applications` table
        if (empty($errors)) {
            $sql_insert_application = "INSERT INTO adoption_applications (pet_id, adopter_id, application_date, status) 
                                        VALUES ('$pet_id', '$adopter_id', NOW(), 'pending')";
            if (mysqli_query($conn, $sql_insert_application)) {
                // Redirect to the comments page
                header("Location: comments.php?pet_id=$pet_id");
                exit();
            } else {
                $errors[] = 'Error submitting your application: ' . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoption Application</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fdf0d5;
            color: #003049;
        }

        header {
            background-color: #780000;
            color: #fdf0d5;
            padding: 20px;
            text-align: center;
        }

        main {
            padding: 20px;
            text-align: center;
        }

        footer {
            background-color: #003049;
            color: #fdf0d5;
            padding: 10px;
            text-align: center;
        }

        form {
            max-width: 500px;
            margin: 0 auto;
            text-align: left;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn {
            background-color: #780000;
            color: #fdf0d5;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #c1121f;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .pet-image {
            max-width: 300px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Adopt <?php echo htmlspecialchars($pet['name']); ?></h1>
    </header>

    <main>
        <!-- Pet Image -->
        <div>
            <?php
            if (!empty($pet['image'])): ?>
                <img src="../<?php echo htmlspecialchars($pet['image']); ?>" 
                     alt="<?php echo htmlspecialchars($pet['name']); ?>" 
                     class="pet-image">
            <?php else: ?>
                <p>No image available for this pet.</p>
            <?php endif; ?>
        </div>
        <p><strong>Species:</strong> <?php echo htmlspecialchars($pet['species']); ?></p>
        <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?></p>
        <p><strong>Age:</strong> <?php echo htmlspecialchars($pet['age']); ?> years</p>

        <!-- Display Errors -->
        <?php if (!empty($errors)) : ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Application Form -->
        <?php if (empty($success_message)) : ?>
            <form action="submit_application.php?pet_id=<?php echo $pet_id; ?>" method="post">
                <label for="name">Your Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                
                <label for="email">Your Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                
                <label for="phone">Your Phone:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" 
                       required pattern="\+?\d{10,15}" title="Please enter a valid phone number (e.g., +1234567890)">
                
                <button type="submit" class="btn">Submit Application</button>
            </form>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Pet Adoption CMS</p>
    </footer>
</body>
</html>

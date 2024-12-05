<?php
require_once '../includes/db_connect.php';  // Include the DB connection

// Fetch the pet details
$pet_id = $_GET['pet_id'] ?? null;
if (!$pet_id) {
    die('Invalid pet ID.');
}

$sql = "SELECT * FROM pets WHERE id = $pet_id";
$result = mysqli_query($conn, $sql);
$pet = mysqli_fetch_assoc($result);

if (!$pet) {
    die('Pet not found.');
}

// Initialize variables
$name = $email = $comment = $captcha_input = '';
$errors = [];
$success_message = '';

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $captcha_input = $_POST['captcha'] ?? '';

    // Validate CAPTCHA
    session_start();
    if ($captcha_input !== ($_SESSION['captcha'] ?? '')) {
        $errors[] = 'Incorrect CAPTCHA. Please try again.';
    }

    // Validate other inputs
    if (empty($name)) $errors[] = 'Your name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (empty($comment)) $errors[] = 'Your comment cannot be empty.';

    // Insert the comment if there are no errors
    if (empty($errors)) {
        $sql_insert_comment = "INSERT INTO comments (pet_id, name, email, comment, created_at) 
                               VALUES ('$pet_id', '$name', '$email', '$comment', NOW())";
        if (mysqli_query($conn, $sql_insert_comment)) {
            $success_message = "Your comment has been posted.";
        } else {
            $errors[] = 'Error submitting your comment: ' . mysqli_error($conn);
        }
    }
}

// Fetch comments for the pet
$sql_comments = "SELECT * FROM comments WHERE pet_id = $pet_id ORDER BY created_at DESC";
$result_comments = mysqli_query($conn, $sql_comments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments for <?php echo htmlspecialchars($pet['name']); ?></title>
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
            max-width: 800px;
            margin: 0 auto;
        }

        footer {
            background-color: #003049;
            color: #fdf0d5;
            padding: 10px;
            text-align: center;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #780000;
            color: #fdf0d5;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #c1121f;
        }

        .comment {
            background-color: #fdf0d5;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .comment .author {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .success {
            color: green;
            font-weight: bold;
            margin: 20px 0;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <header>
        <h1>Comments for <?php echo htmlspecialchars($pet['name']); ?></h1>
    </header>

    <main>
        <!-- Success Message -->
        <?php if (!empty($success_message)) : ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>

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

        <!-- Comment Form -->
        <form action="comments.php?pet_id=<?php echo $pet_id; ?>" method="post">
            <label for="name">Your Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

            <label for="email">Your Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="comment">Your Comment:</label>
            <textarea id="comment" name="comment" rows="4" required><?php echo htmlspecialchars($comment); ?></textarea>

            <!-- CAPTCHA -->
            <label for="captcha">Enter the CAPTCHA:</label>
            <img src="captcha_image.php" alt="CAPTCHA">
            <input type="text" id="captcha" name="captcha" required>

            <button type="submit">Submit Comment</button>
        </form>

        <!-- Display Comments -->
        <?php if (mysqli_num_rows($result_comments) > 0) : ?>
            <?php while ($comment = mysqli_fetch_assoc($result_comments)) : ?>
                <div class="comment">
                    <p class="author"><?php echo htmlspecialchars($comment['name']); ?>:</p>
                    <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <p>No comments yet. Be the first to comment!</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Pet Adoption CMS</p>
    </footer>
</body>
</html>

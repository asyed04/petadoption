<?php
session_start();
require_once 'includes/db_connect.php';  // Include the DB connection

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // First, check if the user is an admin
    $sql_admin = "SELECT * FROM users WHERE username = ? AND role = 'admin'";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("s", $username);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin && $result_admin->num_rows > 0) {
        $admin = $result_admin->fetch_assoc();
        
        // Verify hashed password for admin
        if (password_verify($password, $admin['password'])) {
            // Set session variables for admin login
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Redirect to admin dashboard
            header("Location: admin/admin_dashboard.php");
            exit();
        } else {
            $error_message = "Invalid admin password!";
        }
    } else {
        // If not admin, check if the user is an adopter
        $sql_user = "SELECT * FROM adopters WHERE email = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("s", $username);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();

        if ($result_user && $result_user->num_rows > 0) {
            $adopter = $result_user->fetch_assoc();
            
            // Verify plaintext password for adopters
            if ($password === $adopter['password']) {
                // Set session variables for adopter login
                $_SESSION['adopter_id'] = $adopter['id'];
                $_SESSION['adopter_name'] = $adopter['name'];
                
                // Redirect to Browse Pets directly
                header("Location: user/browse_pets.php");
                exit();            
            } else {
                $error_message = "Invalid adopter password!";
            }
        } else {
            $error_message = "Invalid username or account does not exist!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error_message)) : ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label for="username">Username (Admin) / Email (User):</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>

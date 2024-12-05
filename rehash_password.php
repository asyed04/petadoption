<?php
require_once 'includes/db_connect.php';

// Replace these values with the correct email and new password
$email = 'user_email@example.com';
$new_password = password_hash('your_new_password', PASSWORD_DEFAULT);

$sql = "UPDATE adopters SET password = ? WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $new_password, $email);
if ($stmt->execute()) {
    echo "Password updated successfully.";
} else {
    echo "Error updating password: " . $conn->error;
}
?>

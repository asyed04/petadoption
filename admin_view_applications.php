<?php
session_start();
require_once '../includes/db_connect.php';  // Include the DB connection

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch all adoption applications
$sql = "SELECT 
            adoption_applications.id AS application_id, 
            adoption_applications.application_date, 
            adoption_applications.status AS application_status, 
            adopters.name AS adopter_name, 
            adopters.email AS adopter_email, 
            adopters.phone AS adopter_phone, 
            pets.name AS pet_name, 
            pets.species, 
            pets.breed 
        FROM adoption_applications
        JOIN adopters ON adoption_applications.adopter_id = adopters.id
        JOIN pets ON adoption_applications.pet_id = pets.id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error fetching applications: " . mysqli_error($conn));
}

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'];
    $new_status = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    // Update the application status
    $sql_update = "UPDATE adoption_applications SET status = '$new_status' WHERE id = $application_id";
    if (mysqli_query($conn, $sql_update)) {
        // If approved, update the pet's status to "adopted"
        if ($new_status === 'approved') {
            $pet_id_query = "SELECT pet_id FROM adoption_applications WHERE id = $application_id";
            $pet_result = mysqli_query($conn, $pet_id_query);
            $pet = mysqli_fetch_assoc($pet_result);
            $pet_id = $pet['pet_id'];

            $sql_update_pet = "UPDATE pets SET status_id = (SELECT id FROM statuses WHERE status_name = 'adopted') WHERE id = $pet_id";
            mysqli_query($conn, $sql_update_pet);
        }
        header("Location: admin_view_applications.php");  // Refresh the page
        exit();
    } else {
        echo "Error updating application: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Manage Adoption Applications</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Adopter Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Pet Name</th>
                <th>Species</th>
                <th>Breed</th>
                <th>Application Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($application = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$application['application_id']}</td>
                            <td>{$application['adopter_name']}</td>
                            <td>{$application['adopter_email']}</td>
                            <td>{$application['adopter_phone']}</td>
                            <td>{$application['pet_name']}</td>
                            <td>{$application['species']}</td>
                            <td>{$application['breed']}</td>
                            <td>{$application['application_date']}</td>
                            <td>{$application['application_status']}</td>
                            <td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='application_id' value='{$application['application_id']}'>
                                    <button type='submit' name='action' value='approve'>Approve</button>
                                    <button type='submit' name='action' value='reject'>Reject</button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No applications found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

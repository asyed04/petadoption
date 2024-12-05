<?php
session_start();
require_once '../includes/db_connect.php';  // Include the DB connection

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle moderation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $sql = "UPDATE comments SET status = 'approved' WHERE id = ?";
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM comments WHERE id = ?";
    } elseif ($action === 'mark_inappropriate') {
        $sql = "UPDATE comments SET status = 'inappropriate' WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all comments
$sql = "SELECT comments.id, comments.name, comments.comment, comments.status, pets.name AS pet_name 
        FROM comments 
        JOIN pets ON comments.pet_id = pets.id
        ORDER BY comments.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Moderation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fdf0d5;
            color: #003049;
        }
        header {
            background-color: #780000;
            color: #fdf0d5;
            padding: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #780000;
            color: #fdf0d5;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-approve {
            background-color: #28a745;
            color: #fff;
        }
        .btn-delete {
            background-color: #dc3545;
            color: #fff;
        }
        .btn-mark {
            background-color: #ffc107;
            color: #000;
        }
    </style>
</head>
<body>
    <header>
        <h1>Comment Moderation</h1>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pet</th>
                    <th>User</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($comment = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $comment['id']; ?></td>
                        <td><?php echo htmlspecialchars($comment['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($comment['name']); ?></td>
                        <td><?php echo htmlspecialchars($comment['comment']); ?></td>
                        <td><?php echo htmlspecialchars($comment['status']); ?></td>
                        <td>
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                                <button type="submit" name="action" value="delete" class="btn btn-delete">Delete</button>
                                <button type="submit" name="action" value="mark_inappropriate" class="btn btn-mark">Mark Inappropriate</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>

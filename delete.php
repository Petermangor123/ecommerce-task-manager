<?php
require 'db.php';
// Validate ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = (int) $_GET['id'];
// Delete the task
$stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
// Redirect back to task list
header('Location: index.php');
exit;
?>

<?php
require 'db.php';
// Validate ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id    = (int) $_GET['id'];
$error = '';
// Fetch existing task
$stmt = mysqli_prepare($conn, "SELECT * FROM tasks WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$task   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$task) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status      = $_POST['status'];
    if ($title === '') {
        $error = 'Title is required.';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'sssi', $title, $description, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Error updating task: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    // Keep posted values on error
    $task['title']       = $title;
    $task['description'] = $description;
    $task['status']      = $status;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - E-Commerce Lab</title>
    <style>
        body       { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
        h1         { color: #333; }
        .form-box  { background: #fff; padding: 24px; border-radius: 6px; max-width: 500px; }
        label      { display: block; margin-top: 14px; font-weight: bold; }
        input[type="text"], textarea, select {
            width: 100%; padding: 8px; margin-top: 4px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        textarea   { height: 100px; resize: vertical; }
        input[type="submit"] {
            margin-top: 18px; padding: 9px 20px;
            background: #2196F3; color: #fff; border: none;
            border-radius: 4px; cursor: pointer; font-size: 15px;
        }
        input[type="submit"]:hover { background: #1976D2; }
        .error { color: red; margin-bottom: 10px; }
        a      { color: #4CAF50; }
    </style>
</head>
<body>
<h1>Edit Task</h1>
<a href="index.php">&larr; Back to Task List</a>
<div class="form-box">
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="edit.php?id=<?php echo $id; ?>">
        <label for="title">Title <span style="color:red">*</span></label>
        <input type="text" id="title" name="title"
               value="<?php echo htmlspecialchars($task['title']); ?>" required>
        <label for="description">Description</label>
        <textarea id="description" name="description"><?php
            echo htmlspecialchars($task['description']);
        ?></textarea>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="pending"     <?php echo $task['status'] === 'pending'     ? 'selected' : ''; ?>>Pending</option>
            <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="done"        <?php echo $task['status'] === 'done'        ? 'selected' : ''; ?>>Done</option>
        </select>
        <input type="submit" value="Update Task">
    </form>
</div>
</body>
</html>

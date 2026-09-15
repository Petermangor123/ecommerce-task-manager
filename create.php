<?php
require 'db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status      = $_POST['status'];
    if ($title === '') {
        $error = 'Title is required.';
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $title, $description, $status);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Error creating task: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task - E-Commerce Lab</title>
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
            background: #4CAF50; color: #fff; border: none;
            border-radius: 4px; cursor: pointer; font-size: 15px;
        }
        input[type="submit"]:hover { background: #45a049; }
        .error  { color: red; margin-bottom: 10px; }
        a       { color: #4CAF50; }
    </style>
</head>
<body>

<h1>Add New Task</h1>
<a href="index.php">&larr; Back to Task List</a>

<div class="form-box">
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="create.php">
        <label for="title">Title <span style="color:red">*</span></label>
        <input type="text" id="title" name="title"
               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
               required>
        <label for="description">Description</label>
        <textarea id="description" name="description"><?php
            echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
        ?></textarea>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="pending"     <?php echo (isset($_POST['status']) && $_POST['status'] === 'pending')     ? 'selected' : ''; ?>>Pending</option>
            <option value="in_progress" <?php echo (isset($_POST['status']) && $_POST['status'] === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
            <option value="done"        <?php echo (isset($_POST['status']) && $_POST['status'] === 'done')        ? 'selected' : ''; ?>>Done</option>
        </select>
        <input type="submit" value="Add Task">
    </form>
</div>
</body>
</html>

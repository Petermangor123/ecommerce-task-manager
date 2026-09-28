<?php
require 'db.php';

// Fetch all tasks ordered by newest first
$result = mysqli_query($conn, "SELECT * FROM tasks ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List - E-Commerce Lab</title>
    <style>
        body        { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
        h1          { color: #333; }
        table       { width: 100%; border-collapse: collapse; background: #fff; }
        th, td      { padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
        th          { background: #4CAF50; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        a           { color: #4CAF50; text-decoration: none; }
        a:hover     { text-decoration: underline; }
        .btn        { padding: 5px 10px; border-radius: 4px; font-size: 13px; }
        .btn-edit   { background: #2196F3; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
        .btn-delete { background: #f44336; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
        .add-link   { display: inline-block; margin-bottom: 16px; background: #4CAF50; color: #fff; padding: 8px 16px; border-radius: 4px; }
    </style>
</head>
<body>

<h1>E-Commerce Lab – Task Manager</h1>

<a class="add-link" href="create.php">+ Add New Task</a>

<?php if (mysqli_num_rows($result) === 0): ?>
    <p>No tasks found. <a href="create.php">Create the first one!</a></p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td>
                <a class="btn-edit"   href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                &nbsp;
                <a class="btn-delete" href="delete.php?id=<?php echo $row['id']; ?>"
                   onclick="return confirm('Delete this task?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php endif; ?>

</body>
</html>

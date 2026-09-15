<?php
require 'db.php';
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    status      ENUM('pending', 'in_progress', 'done') DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
    echo "Table 'tasks' created successfully (or already exists).<br>";
} else {
    die('Error creating table: ' . mysqli_error($conn));
}
echo "<br>Setup complete! <a href='index.php'>Go to Task List</a>";
?>

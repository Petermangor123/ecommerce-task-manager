# Lab 1 – PHP CRUD Task Manager

A simple task management web application built with PHP and MySQL as part of the E-Commerce Labs course.

## Location

This project lives in the `tasks-app/` folder of the repository:

```
ecommerce-task-manager/
└── tasks-app/
    ├── .gitignore
    ├── create.php
    ├── db.php           ← create this locally from db.php.example (not committed)
    ├── db.php.example
    ├── delete.php
    ├── edit.php
    ├── index.php
    ├── setup.php
    └── README.md
```

## Features

- View all tasks in a sortable table
- Create new tasks with title, description, and status
- Edit existing tasks
- Delete tasks with confirmation prompt
- Status options: Pending, In Progress, Done

## Requirements

- PHP 7.4+
- MySQL / MariaDB
- A local server (XAMPP, WAMP, or similar)

## Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/Petermangor123/ecommerce-task-manager.git
   ```

2. Navigate into the project folder:
   ```bash
   cd ecommerce-task-manager/tasks-app
   ```

3. Copy the example credentials file and fill in your database details:
   ```bash
   cp db.php.example db.php
   ```
   Then edit `db.php` with your actual host, username, password, and database name.

4. Place the `tasks-app/` folder under your web server's document root (e.g. `htdocs/` for XAMPP).

5. Run the setup script once to create the `tasks` table:
   ```
   http://localhost/tasks-app/setup.php
   ```

6. Open the app:
   ```
   http://localhost/tasks-app/index.php
   ```

## Security Notes

- `db.php` is excluded from version control via `.gitignore` — never commit real credentials.
- All database queries use prepared statements to prevent SQL injection.
- User input is escaped with `htmlspecialchars()` before display.

## File Overview

| File             | Purpose                              |
|------------------|--------------------------------------|
| `index.php`      | Lists all tasks                      |
| `create.php`     | Form to add a new task               |
| `edit.php`       | Form to update an existing task      |
| `delete.php`     | Deletes a task by ID                 |
| `setup.php`      | Creates the `tasks` database table   |
| `db.php`         | Database connection (local only)     |
| `db.php.example` | Template for database credentials    |

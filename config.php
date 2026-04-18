<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dailyexpense');

// Create connection
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($con, "utf8");

// Base URL for redirects and links
define('BASE_URL', 'http://localhost/Personal-Expense-Tracker/');
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// Create uploads directory if not exists
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
?>
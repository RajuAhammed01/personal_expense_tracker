<?php
require_once 'config.php';

if ($con) {
    echo "Connected successfully to database!<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST;
} else {
    echo "Connection failed!";
}

// Test query to check tables
$result = mysqli_query($con, "SHOW TABLES");
echo "<h3>Tables in database:</h3>";
echo "<ul>";
while ($row = mysqli_fetch_array($result)) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
?>
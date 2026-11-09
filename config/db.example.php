
<?php

// Hostel Management System
// Database Configuration Template

$host = "localhost";
$username = "your_username";
$password = "your_password";
$database = "your_database";
$port = 3306;

$conn = mysqli_connect(
    $host,
    $username,
    $password,
    $database,
    $port
);

if (!$conn) {
    die("Database connection failed.");
}

?>

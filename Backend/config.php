<?php
$host = "127.0.0.1";   // or "localhost"
$port = 3306;          // Workbench MySQL default port
$user = "root";        // MySQL username
$pass = ""; // replace with your MySQL root password
$db   = "pgconnects";   // your database

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
}
// echo "✅ Connected successfully";  // for debugging
?>

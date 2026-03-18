<?php
$servername = "127.0.0.1"; // Update with your server address
$username = "root"; // Update with your username
$password = ""; // Update with your password
$dbname = "users"; // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
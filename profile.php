<?php
session_start();
header('Content-Type: application/json');

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not authenticated"]);
    exit();
}

// Connect to MySQL
$conn = new mysqli("127.0.0.1", "root", "", "users");

// Check connection
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Fetch user info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, reg_date FROM userinfo WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Return user data as JSON
echo json_encode($user);
?>
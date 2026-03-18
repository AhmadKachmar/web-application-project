<?php

session_start();

$userId = $_SESSION['user_id']; // Assuming user_id is set in the session
// Database connection
$conn = new mysqli("127.0.0.1", "root", "", "users");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch grades for the user
$sql = "SELECT grade FROM user_grades WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId); // for security purposes
$stmt->execute();
$result = $stmt->get_result();

$grades = [];
while ($row = $result->fetch_assoc()) {
    $grades[] = $row['grade'];
}

$stmt->close();
$conn->close();

// Send grades as JSON
header('Content-Type: application/json');
echo json_encode($grades);
?>
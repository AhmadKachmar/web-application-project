<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Grab POSTed data
$name     = $_POST['name']     ?? '';
$email    = $_POST['email']    ?? '';
$comments = $_POST['comments'] ?? '';

// Validate
if (!$name || !$email || !$comments) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

// Connect & insert
$conn = new mysqli("127.0.0.1", "root", "", "users");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare(
    "INSERT INTO feedback (name, email, comments) VALUES (?, ?, ?)"
);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => $conn->error]);
    exit;
}
$stmt->bind_param("sss", $name, $email, $comments);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>

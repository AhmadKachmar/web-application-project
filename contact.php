<?php


// Turn on errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// 1) Grab POSTed data
$name    = $_POST['name']    ?? '';
$email   = $_POST['email']   ?? '';
$message = $_POST['message'] ?? '';

// 2) Validate
if (!$name || !$email || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

$conn = new mysqli("127.0.0.1", "root", "", "users");
if ($conn->connect_error) {
    throw new Exception('Database connection failed: ' . $conn->connect_error);
}

// Prepare the statement
$stmt = $conn->prepare(
    "INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)"
);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => $conn->error]);
    exit;
}

// Bind and execute
$stmt->bind_param("sss", $name, $email, $message);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>

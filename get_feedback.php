<?php
header('Content-Type: application/json');
$conn = new mysqli("127.0.0.1", "root", "", "users");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 

$sql  = "SELECT name, comments, created_at
         FROM feedback
         ORDER BY created_at DESC";
$res  = $conn->query($sql);
if (!$res) {
    http_response_code(500);
    echo json_encode(['error' => $conn->error]);
    exit;
}

$all = [];
while ($row = $res->fetch_assoc()) {
    $all[] = $row;
}

echo json_encode($all);
$conn->close();

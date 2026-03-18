<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate required data exists
    if (!isset($data['grade']) || !isset($_SESSION['user_id'])) {
        http_response_code(400); // Bad request
        die(json_encode(['error' => 'Missing grade or user not logged in']));
    }

    // Sanitize inputs
    $grade = floatval($data['grade']);
    $user_id = $_SESSION['user_id'];

    // Database connection
    $conn = new mysqli("127.0.0.1", "root", "", "users");
    
    if ($conn->connect_error) {
        http_response_code(500); // Server error
        die(json_encode(['error' => 'Database connection failed']));
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 1. Insert into user_grades table
        $stmt = $conn->prepare("INSERT INTO user_grades (user_id, grade) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("id", $user_id, $grade);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        $stmt->close();
        
        // 2. Update leaderboard table (increment score)
        // First check if user exists in leaderboard
        $check = $conn->prepare("SELECT COUNT(*) FROM leaderboard WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
        
        if ($count > 0) {
            // User exists - update their score
            $update = $conn->prepare("UPDATE leaderboard SET score = score + ? WHERE user_id = ?");
            $update->bind_param("di", $grade, $user_id);
        } else {
            // User doesn't exist - insert new record
            $update = $conn->prepare("INSERT INTO leaderboard (user_id, score) VALUES (?, ?)");
            $update->bind_param("id", $user_id, $grade);
        }
        
        if (!$update->execute()) {
            throw new Exception('Leaderboard update failed: ' . $update->error);
        }
        $update->close();
        
        // Commit transaction
        $conn->commit();
        
        // Success - return JSON response
        echo json_encode([
            'success' => true,
            'grade' => $grade,
            'leaderboard_updated' => true
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    
    $conn->close();
    exit();
}

// If not a POST request, redirect to quiz page
header("Location: quiz.html");
exit();
?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

try {
    // Check if user is logged in
    $loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Database connection
    $conn = new mysqli("127.0.0.1", "root", "", "users");
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Query to get leaderboard data - UPDATED TO MATCH YOUR SCHEMA
    $sql = "SELECT u.user_id, u.username AS name, l.score 
            FROM leaderboard l
            JOIN userinfo u ON l.user_id = u.user_id
            ORDER BY l.score DESC
            LIMIT 20";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception('Database query failed: ' . $conn->error);
    }

    $leaderboard = [];
    $currentUserPosition = null;
    $rank = 0;
    $previousScore = null;
    $actualRank = 0;

    while ($row = $result->fetch_assoc()) {
        if ($previousScore !== $row['score']) {
            $actualRank = $rank + 1;
        }
        $previousScore = $row['score'];
        
        $userEntry = [
            'rank' => $actualRank,
            'name' => $row['name'],
            'score' => $row['score'],
            'isCurrentUser' => ($loggedInUserId && $row['user_id'] == $loggedInUserId)
        ];
        
        if ($userEntry['isCurrentUser']) {
            $currentUserPosition = $userEntry;
        }
        
        $leaderboard[] = $userEntry;
        $rank++;
    }

    // If current user isn't in top 20
    if ($loggedInUserId && !$currentUserPosition) {
        // Simplified rank calculation
        $stmt = $conn->prepare("SELECT score FROM leaderboard WHERE user_id = ?");
        $stmt->bind_param("i", $loggedInUserId);
        $stmt->execute();
        $stmt->bind_result($userScore);
        $stmt->fetch();
        $stmt->close();

        if ($userScore !== null) {
            $stmt = $conn->prepare("
                SELECT username
                FROM userinfo
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $loggedInUserId);
            $stmt->execute();
            $stmt->bind_result($userName);
            $stmt->fetch();
            $stmt->close();

            // Count users with higher scores for rank
            $stmt = $conn->prepare("
                SELECT COUNT(*) + 1 as rank 
                FROM leaderboard 
                WHERE score > ?
            ");
            $stmt->bind_param("d", $userScore);
            $stmt->execute();
            $stmt->bind_result($userRank);
            $stmt->fetch();
            $stmt->close();

            $currentUserPosition = [
                'rank' => $userRank,
                'name' => $userName,
                'score' => $userScore,
                'isCurrentUser' => true
            ];
        }
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'leaderboard' => $leaderboard,
        'currentUser' => $currentUserPosition
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
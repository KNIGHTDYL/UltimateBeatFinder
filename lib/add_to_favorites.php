<?php
session_start();

// Validate that the request is a POST request and the user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Get the user ID and video ID
    $userId = $_SESSION['user_id'];
    $videoId = $_POST['videoId'];

    // TODO: Perform the database insert into `user_favorites` table

    // Example using PDO
    $pdo = new PDO('mysql:host=localhost;dbname=your_database_name', 'your_username', 'your_password');
    $sql = "INSERT INTO `user_favorites` (`user_id`, `video_id`) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $videoId]);

    // TODO: Handle success or failure
    echo 'Video added to favorites successfully';
} else {
    // Handle unauthorized access
    http_response_code(401);
    echo 'Unauthorized access';
}
?>

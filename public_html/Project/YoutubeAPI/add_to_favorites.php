<?php
require_once(__DIR__ . "/../../../partials/nav.php"); 

// Start or resume the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if the user is logged in
    if (isset($_SESSION['user'])) {
        // Get the user ID from the session
        $userId = $_SESSION['user']['id'];

        // Get the video ID from the POST data
        $videoId = se($_POST, 'videoId', '', false);

        // Validate the video ID
        if (!empty($videoId)) {
            // Insert the favorite into the USER_FAVORITES table
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO USER_FAVORITES (user_id, video_id) VALUES (:userId, :videoId)");
            try {
                $stmt->execute([
                    ':userId' => $userId,
                    ':videoId' => $videoId,
                ]);

                // Return a success message
                echo json_encode(['success' => true, 'message' => 'Video added to favorites successfully']);
                exit();
            } catch (PDOException $e) {
                // Return an error message
                echo json_encode(['success' => false, 'message' => 'Error adding video to favorites']);
                exit();
            }
        } else {
            // Return an error message if the video ID is empty
            echo json_encode(['success' => false, 'message' => 'Invalid video ID']);
            exit();
        }
    } else {
        // Return an error message if the user is not logged in
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }
} else {
    // Return an error message for non-POST requests
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

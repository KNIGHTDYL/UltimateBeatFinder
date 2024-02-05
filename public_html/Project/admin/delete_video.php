<?php
require(__DIR__ . "/../../../partials/nav.php");

// Check if the user has the "Admin" role
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the video ID from the POST data
    $videoId = se($_POST, "video_id", "", false);

    // Validate the video ID (you can add more validation if needed)
    if (!empty($videoId)) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM `VIDEO_DATA` WHERE id = :videoId");

        try {
            // Bind the parameter and execute the query
            $stmt->bindParam(":videoId", $videoId, PDO::PARAM_INT);
            $stmt->execute();

            // Provide a success message
            echo "Video deleted successfully!";
        } catch (PDOException $e) {
            // Provide an error message
            echo "Error deleting video: " . $e->getMessage();
        }
    } else {
        // Handle invalid video ID
        echo "Invalid video ID";
    }
} else {
    // Handle non-POST requests
    echo "Invalid request method";
}
?>

<?php
require(__DIR__ . "/../../../partials/nav.php");

// Check if the user has the "Admin" role
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

// Initialize variables
$errorMessage = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $videoId = se($_GET, "video_id", 0, false);
    $title = se($_POST, "title", "", false);
    $channelName = se($_POST, "channel_name", "", false);
    $channelId = se($_POST, "channel_id", "", false);
    $lengthText = se($_POST, "length_text", "", false);
    $publishedTimeText = se($_POST, "published_time_text", "", false);
    $videoIdField = se($_POST, "video_id", "", false);
    $viewCountText = se($_POST, "view_count_text", "", false);
    $thumbnailHeight = se($_POST, "thumbnail_height", 0, false);
    $thumbnailUrl = se($_POST, "thumbnail_url", "", false);
    $thumbnailWidth = se($_POST, "thumbnail_width", 0, false);

    // Validate required fields
    if (empty($title) || empty($channelName) || empty($videoIdField)) {
        $errorMessage = "Title, Channel Name, and Video ID are required";
    } else {
        // Update data in the VIDEO_DATA table
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE `VIDEO_DATA`
            SET title = :title,
                channel_name = :channelName,
                channel_id = :channelId,
                length_text = :lengthText,
                published_time_text = :publishedTimeText,
                video_id = :videoIdField,
                view_count_text = :viewCountText,
                thumbnail_height = :thumbnailHeight,
                thumbnail_url = :thumbnailUrl,
                thumbnail_width = :thumbnailWidth
            WHERE id = :videoId
        ");
        try {
            $stmt->execute([
                ":title" => $title,
                ":channelName" => $channelName,
                ":channelId" => $channelId,
                ":lengthText" => $lengthText,
                ":publishedTimeText" => $publishedTimeText,
                ":videoIdField" => $videoIdField,
                ":viewCountText" => $viewCountText,
                ":thumbnailHeight" => $thumbnailHeight,
                ":thumbnailUrl" => $thumbnailUrl,
                ":thumbnailWidth" => $thumbnailWidth,
                ":videoId" => $videoId,
            ]);

            flash("Successfully updated video data!", "success");
        } catch (PDOException $e) {
            $errorMessage = "Error updating video data";
            error_log(var_export($e->errorInfo, true));
        }
    }
}

// Redirect back to the edit page or list_videos page based on success or failure
if (!empty($errorMessage)) {
    flash($errorMessage, "danger");
    header("Location: " . get_url("admin/edit_video_page.php?video_id=" . $videoId));
} else {
    header("Location: " . get_url("admin/list_videos.php"));
    exit(); // Ensure that no further output is sent
}
?>

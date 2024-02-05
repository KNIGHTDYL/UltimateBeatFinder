<?php
require(__DIR__ . "/../../../partials/nav.php");

// Check if the user has the "Admin" role
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $title = se($_POST, "title", "", false);
    $channelName = se($_POST, "channel_name", "", false);
    $channelId = se($_POST, "channel_id", "", false);
    $lengthText = se($_POST, "length_text", "", false);
    $publishedTimeText = se($_POST, "published_time_text", "", false);
    $videoId = se($_POST, "video_id", "", false);
    $viewCountText = se($_POST, "view_count_text", "", false);
    $thumbnailHeight = se($_POST, "thumbnail_height", 0, false);
    $thumbnailUrl = se($_POST, "thumbnail_url", "", false);
    $thumbnailWidth = se($_POST, "thumbnail_width", 0, false);

    // Validate required fields
    if (empty($title) || empty($channelName) || empty($videoId)) {
        flash("Title, Channel Name, and Video ID are required", "warning");
    } else {
        // Insert data into the VIDEO_DATA table
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO `VIDEO_DATA` (
                title, channel_name, channel_id, length_text, published_time_text,
                video_id, view_count_text, thumbnail_height, thumbnail_url,
                thumbnail_width
            ) VALUES (
                :title, :channelName, :channelId, :lengthText, :publishedTimeText,
                :videoId, :viewCountText, :thumbnailHeight, :thumbnailUrl,
                :thumbnailWidth
            )
        ");
        try {
            $stmt->execute([
                ":title" => $title,
                ":channelName" => $channelName,
                ":channelId" => $channelId,
                ":lengthText" => $lengthText,
                ":publishedTimeText" => $publishedTimeText,
                ":videoId" => $videoId,
                ":viewCountText" => $viewCountText,
                ":thumbnailHeight" => $thumbnailHeight,
                ":thumbnailUrl" => $thumbnailUrl,
                ":thumbnailWidth" => $thumbnailWidth,
            ]);

            flash("Successfully inserted video data!", "success");
        } catch (PDOException $e) {
            // Check for duplicate key error
            users_check_duplicate($e->errorInfo, "video");

            // Handle other errors
            flash("Error inserting video data (likely datatype error)", "danger");
            // Log the detailed error information
            error_log(var_export($e->errorInfo, true));
        }
    }
}
?>

<!-- HTML code for the form -->
<h1>Insert Video Data</h1>
<form method="POST">
    <div>
        <label for="title">Title</label>
        <input id="title" name="title" required placeholder="e.g., Adele - Hello" />
    </div>
    <div>
        <label for="channel_name">Channel Name</label>
        <input id="channel_name" name="channel_name" required placeholder="e.g., Adele" />
    </div>
    <div>
        <label for="channel_id">Channel ID</label>
        <input id="channel_id" name="channel_id" placeholder="e.g., UCM3iG_kXUM_9HHVIDI7vEtg" />
    </div>
    <div>
        <label for="length_text">Length Text</label>
        <input id="length_text" name="length_text" placeholder="e.g., 5:27" />
    </div>
    <div>
        <label for="published_time_text">Published Time Text</label>
        <input id="published_time_text" name="published_time_text" placeholder="e.g., 10 months ago" />
    </div>
    <div>
        <label for="video_id">Video ID</label>
        <input id="video_id" name="video_id" required placeholder="e.g., mHONNcZbwDY" />
    </div>
    <div>
        <label for="view_count_text">View Count Text</label>
        <input id="view_count_text" name="view_count_text" placeholder="e.g., 30,207,207 views" />
    </div>
    <div>
        <label for="thumbnail_height">Thumbnail Height</label>
        <input id="thumbnail_height" name="thumbnail_height" type="number" placeholder="e.g., 202" />
    </div>
    <div>
        <label for="thumbnail_url">Thumbnail URL</label>
        <input id="thumbnail_url" name="thumbnail_url" placeholder="e.g., https://i.ytimg.com/vi/mHONNcZbwDY/hq720.jpg" />
    </div>
    <div>
        <label for="thumbnail_width">Thumbnail Width</label>
        <input id="thumbnail_width" name="thumbnail_width" type="number" placeholder="e.g., 360" />
    </div>
    <input type="submit" value="Insert Video Data" />
    <a href="<?= get_url("home.php") ?>" class="btn btn-primary">Back to Home (without inserting)</a>
</form>


<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>

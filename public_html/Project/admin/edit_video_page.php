<?php
require(__DIR__ . "/../../../partials/nav.php");

// Check if the user has the "Admin" role
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

// Retrieve video data from the database based on the video ID
$db = getDB();
$videoId = se($_GET, "video_id", 0, false);
$stmt = $db->prepare("SELECT * FROM `VIDEO_DATA` WHERE id = :videoId");
$stmt->execute([":videoId" => $videoId]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the video exists
if (!$video) {
    flash("Video not found", "danger");
    die(header("Location: " . get_url("admin/list_videos.php")));
}
?>

<h1>Edit Video</h1>

<!-- HTML code for the form -->
<form method="POST" action="edit_video.php?video_id=<?= safer_echo($video['id']) ?>">
    <input type="hidden" name="video_id" value="<?= safer_echo($video['id']) ?>" />

    <div>
        <label for="title">Title</label>
        <input id="title" name="title" value="<?= safer_echo($video['title']) ?>" required />
    </div>
    <div>
        <label for="channel_name">Channel Name</label>
        <input id="channel_name" name="channel_name" value="<?= safer_echo($video['channel_name']) ?>" required />
    </div>
    <div>
        <label for="channel_id">Channel ID</label>
        <input id="channel_id" name="channel_id" value="<?= safer_echo($video['channel_id']) ?>" />
    </div>
    <div>
        <label for="length_text">Length Text</label>
        <input id="length_text" name="length_text" value="<?= safer_echo($video['length_text']) ?>" />
    </div>
    <div>
        <label for="published_time_text">Published Time Text</label>
        <input id="published_time_text" name="published_time_text" value="<?= safer_echo($video['published_time_text']) ?>" />
    </div>
    <div>
        <label for="video_id">Video ID</label>
        <input id="video_id" name="video_id" value="<?= safer_echo($video['video_id']) ?>" required />
    </div>
    <div>
        <label for="view_count_text">View Count Text</label>
        <input id="view_count_text" name="view_count_text" value="<?= safer_echo($video['view_count_text']) ?>" />
    </div>
    <div>
        <label for="thumbnail_height">Thumbnail Height</label>
        <input id="thumbnail_height" name="thumbnail_height" type="number" value="<?= safer_echo($video['thumbnail_height']) ?>" />
    </div>
    <div>
        <label for="thumbnail_url">Thumbnail URL</label>
        <input id="thumbnail_url" name="thumbnail_url" value="<?= safer_echo($video['thumbnail_url']) ?>" />
    </div>
    <div>
        <label for="thumbnail_width">Thumbnail Width</label>
        <input id="thumbnail_width" name="thumbnail_width" type="number" value="<?= safer_echo($video['thumbnail_width']) ?>" />
    </div>
    <input type="submit" value="Update Video Data" />
    <div>
    <a href="<?= get_url("admin/list_videos.php") ?>" class="btn btn-primary">Back to videos (without updating)</a>

</form>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>

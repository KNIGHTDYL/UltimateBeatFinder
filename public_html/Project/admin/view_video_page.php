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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="path/to/your/styles.css">
    <title>View Video</title>
</head>

<body>

    <div class="container">
        <h1>View Video</h1>

        <!-- Video card -->
        <div class="card">
            <img src="<?= safer_echo($video['thumbnail_url']); ?>" class="card-img-top" alt="<?= safer_echo($video['title']); ?>">
            <div class="card-body">
                <h5 class="card-title"><?= safer_echo($video['title']); ?></h5>
                <p class="card-text"><strong>Channel:</strong> <?= safer_echo($video['channel_name']); ?></p>
                <p class="card-text"><strong>Video Length:</strong> <?= safer_echo($video['length_text']); ?></p>
                <p class="card-text"><strong>View Count:</strong> <?= safer_echo($video['view_count_text']); ?></p>
                <p class="card-text"><strong>Posted:</strong> <?= safer_echo($video['published_time_text']); ?></p>
                <!-- Add more details as needed -->
            </div>
        </div>

        <!-- Full details section -->
        <div class="full-details">
            <h2>Full Details</h2>
            <table>
                <tr>
                    <th>Attribute</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Title</td>
                    <td><?= safer_echo($video['title']); ?></td>
                </tr>
                <tr>
                    <td>Channel Name</td>
                    <td><?= safer_echo($video['channel_name']); ?></td>
                </tr>
                <tr>
                    <td>Channel ID</td>
                    <td><?= safer_echo($video['channel_id']); ?></td>
                </tr>
                <tr>
                    <td>Length Text</td>
                    <td><?= safer_echo($video['length_text']); ?></td>
                </tr>
                <tr>
                    <td>Published Time Text</td>
                    <td><?= safer_echo($video['published_time_text']); ?></td>
                </tr>
                <tr>
                    <td>Video ID</td>
                    <td><?= safer_echo($video['video_id']); ?></td>
                </tr>
                <tr>
                    <td>View Count Text</td>
                    <td><?= safer_echo($video['view_count_text']); ?></td>
                </tr>
                <tr>
                    <td>Thumbnail Height</td>
                    <td><?= safer_echo($video['thumbnail_height']); ?></td>
                </tr>
                <tr>
                    <td>Thumbnail URL</td>
                    <td><?= safer_echo($video['thumbnail_url']); ?></td>
                </tr>
                <tr>
                    <td>Thumbnail Width</td>
                    <td><?= safer_echo($video['thumbnail_width']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Additional information or back button can be added here -->

        <a href="<?= get_url("admin/list_videos.php") ?>" style="font-size: 1.5em;" class="btn btn-primary">Back to Videos</a>
    </div>

    <?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>

</body>



</html>

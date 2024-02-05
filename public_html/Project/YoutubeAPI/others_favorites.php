<?php
require_once(__DIR__ . "/../../../partials/nav.php");

// Start or resume the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    flash("You must be logged in to view other favorites.", "warning");
    die(header("Location: $BASE_PATH" . "/home.php")); // Redirect to the home page or login page
}

// Fetch favorited videos of other users from the USER_FAVORITES table
$db = getDB();
$user_id = $_SESSION['user']['id']; // Get the user ID from the session
$stmt = $db->prepare("SELECT vf.id, vf.user_id, vf.video_id, vd.video_id AS videoId, vd.title, vd.channel_name AS channelName, vd.view_count_text AS viewCountText, vd.length_text AS lengthText, vd.published_time_text AS publishedTimeText, u.username, u.email
                      FROM USER_FAVORITES vf
                      JOIN VIDEO_DATA vd ON vf.video_id = vd.video_id
                      JOIN Users u ON vf.user_id = u.id
                      WHERE vf.user_id <> :user_id");
try {
    $stmt->execute([":user_id" => $user_id]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}
?>

<div class="container-fluid">
    <h1>Other Favorites</h1>

    <form method="POST">
        <input type="search" name="video" placeholder="Video Filter" />
        <input type="submit" value="Search" />
    </form>

    <?php if (empty($favorites)) : ?>
        <p>No other favorites</p>
    <?php else : ?>
        <div class="row">
            <?php foreach ($favorites as $favorite) : ?>
                <div class="col">
                    <div class="card" style="width: 15em; margin-top: 20px">
                        <div class="video-container">
                            <iframe class="video-item" src="https://www.youtube.com/embed/<?php echo $favorite['video_id']; ?>" allowfullscreen></iframe>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($favorite['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($favorite['channelName']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($favorite['viewCountText']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($favorite['lengthText']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($favorite['publishedTimeText']); ?></p>
                            <p class="card-text">Added by: <?php echo htmlspecialchars($favorite['username'] ?? $favorite['email']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>

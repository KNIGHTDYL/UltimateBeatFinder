<?php
require_once(__DIR__ . "/../../../partials/nav.php");

// Start or resume the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    flash("You must be logged in to view your favorites.", "warning");
    die(header("Location: $BASE_PATH" . "/home.php")); // Redirect to the home page or login page
}

// Fetch all favorite videos from the USER_FAVORITES table
$db = getDB();
$stmt = $db->prepare("SELECT id, user_id, video_id
                      FROM USER_FAVORITES");

try {
    $stmt->execute();
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}
?>

<div class="container-fluid">
    <h1>All Favorites</h1>

    <form method="POST">
        <input type="search" name="video" placeholder="Video Filter" />
        <input type="submit" value="Search" />
    </form>

    <?php if (empty($favorites)) : ?>
        <p>No favorites</p>
    <?php else : ?>
        <div class="row">
            <?php foreach ($favorites as $favorite) : ?>
                <div class="col">
                    <div class="card" style="width: 15em; margin-top: 20px">
                        <div class="video-container">
                            <iframe class="video-item" src="https://www.youtube.com/embed/<?php echo $favorite['videoId']; ?>" allowfullscreen></iframe>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($favorite['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($favorite['channelName']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($favorite['viewCountText']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($favorite['lengthText']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($favorite['publishedTimeText']); ?></p>
                            <p class="card-text">Added by: <?php echo htmlspecialchars($favorite['username'] ?? $favorite['email']); ?></p>

                            <!-- Favorites button container with Flexbox styling -->
                            <div style="display: flex; justify-content: center; align-items: center; height: 50px;">
                                <form method="POST">
                                    <input type="hidden" name="video_id" value="<?php echo $favorite['video_id']; ?>" />
                                    <input type="submit" value="Remove from Favorites" />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>

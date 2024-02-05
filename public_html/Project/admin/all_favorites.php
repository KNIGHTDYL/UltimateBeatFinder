<?php
require_once(__DIR__ . "/../../../partials/nav.php");

// Start or resume the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    die(header("Location: $BASE_PATH" . "/home.php")); // Redirect to the home page or login page
}

// Fetch all favorite videos from the USER_FAVORITES table
$db = getDB();
$stmt = $db->prepare("SELECT uf.id, uf.user_id, uf.video_id, 
                             COALESCE(vd.title, 'N/A') AS title, 
                             COALESCE(vd.channel_name, 'N/A') AS channel_name, 
                             COALESCE(vd.view_count_text, 'N/A') AS view_count_text, 
                             COALESCE(vd.length_text, 'N/A') AS length_text, 
                             COALESCE(vd.published_time_text, 'N/A') AS published_time_text, 
                             COALESCE(u.email, 'N/A') AS email,
                             COALESCE(vd.thumbnail_url, '') AS thumbnail_url,
                             u.email AS favorited_by
                      FROM USER_FAVORITES uf
                      LEFT JOIN VIDEO_DATA vd 
                      ON uf.video_id = vd.video_id
                      LEFT JOIN Users u 
                      ON uf.user_id = u.id");

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

    <div class="row">
        <?php if (empty($favorites)) : ?>
            <div class="col">
                <p>No favorites</p>
            </div>
        <?php else : ?>
            <?php foreach ($favorites as $favorite) : ?>
                <!-- <p><?php echo var_export($favorite); ?></p> -->
                <div class="col">
                    <div class="card" style="width: 15em; margin-top: 20px;">
                        <img src="<?= safer_echo($favorite['thumbnail_url']); ?>" class="card-img-top" alt="<?= safer_echo($favorite['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($favorite['title']); ?></h5>
                            <p class="card-text"><strong>Channel Name:</strong> <?php echo htmlspecialchars($favorite['channel_name']); ?></p>
                            <p class="card-text"><strong>View Count:</strong> <?php echo htmlspecialchars($favorite['view_count_text']); ?></p>
                            <p class="card-text"><strong>Length:</strong> <?php echo htmlspecialchars($favorite['length_text']); ?></p>
                            <p class="card-text"><strong>Published:</strong> <?php echo htmlspecialchars($favorite['published_time_text']); ?></p>
                            <p class="card-text"><strong>Video ID:</strong> <?php echo $favorite['video_id']; ?></p>
                            <p class="card-text highlight"><strong>Favorited By:</strong> <?php echo $favorite['favorited_by']; ?><em>(User ID: <?php echo $favorite['user_id']; ?>)</em></p>


                            <!-- View button (NOT WORKING) -->
                            <!-- <button data-video-id="<?= htmlspecialchars($favorite['video_id']) ?>" onclick="redirectToView('<?= get_url("admin/view_video_page.php?video_id=" . $favorite['video_id']) ?>')">View</button> -->
                            <!-- Delete button -->
                            <button data-favorite-id="<?= $favorite['id'] ?>" class="delete-btn">Remove from Favorites</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>

<!-- Include jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function() {
    // Handle the delete operation with AJAX
    $(".delete-btn").click(function() {
        if (confirm("Are you sure you want to remove this video from favorites?")) {
            var favoriteId = $(this).data("favorite-id");
            $.ajax({
                type: "POST",
                url: "remove_from_favorites.php",
                data: { favorite_id: favoriteId },
                success: function(response) {
                    alert("Removed from Favorites Successfully.");
                    location.reload();
                },
                error: function() {
                    alert("Error removing from favorites");
                }
            });
        }
    });
});

function redirectToView(url) {
    window.location.href = url;
}
</script>

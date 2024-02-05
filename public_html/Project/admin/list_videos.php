<?php
require(__DIR__ . "/../../../partials/nav.php");

// Check if the user has the "Admin" role
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

// Retrieve video data from the database
$db = getDB();
$stmt = $db->prepare("SELECT id, title, channel_name FROM `VIDEO_DATA`");
$stmt->execute();
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Previously Searched Video Log</h1>

<!-- Add a filter search bar -->
<form id="filterForm">
    <label for="filter">Filter by Title:</label>
    <input type="text" id="filter" name="filter" placeholder="Enter title to filter">
</form>

<table id="videoTable">
    <thead>
        <tr>
            <th>Title</th>
            <th>Channel Name</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($videos as $video): ?>
    <tr>
        <td><?= safer_echo($video['title']) ?></td>
        <td><?= safer_echo($video['channel_name']) ?></td>
        <td>
            <!-- Convert form to button for View -->
            <button data-video-id="<?= $video['id'] ?>" onclick="redirectToView('<?= get_url("admin/view_video_page.php?video_id=" . $video['id']) ?>')">View</button>

            <!-- Convert form to button for Edit -->
            <button data-video-id="<?= $video['id'] ?>" onclick="redirectToEdit('<?= get_url("admin/edit_video_page.php?video_id=" . $video['id']) ?>')">Edit</button>

            <!-- Delete button remains unchanged -->
            <button class="delete-btn" data-video-id="<?= $video['id'] ?>">Delete</button>
        </td>
    </tr>
<?php endforeach; ?>

<script>
    function redirectToView(url) {
        window.location.href = url;
    }

    function redirectToEdit(url) {
        window.location.href = url;
    }
</script>
    </tbody>
</table>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>

<!-- Include jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function() {
    // Detect changes in the filter input
    $("#filter").on("input", function() {
        var filterValue = $(this).val().toLowerCase();
        $("#videoTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(filterValue) > -1);
        });
    });
});
</script>

<script>
// Use jQuery to handle the delete operation with AJAX
$(document).ready(function() {
    $(".delete-btn").click(function() {
        if (confirm("Are you sure you want to delete this video?")) {
            var videoId = $(this).data("video-id");
            $.ajax({
                type: "POST",
                url: "delete_video.php",
                data: { video_id: videoId },
                success: function(response) {
                    alert("Deleted Successfully.");
                    location.reload();
                },
                error: function() {
                    alert("Error deleting video");
                }
            });
        }
    });
});
</script>

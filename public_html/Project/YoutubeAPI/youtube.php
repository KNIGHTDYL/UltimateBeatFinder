<?php
require(__DIR__ . "/../../../partials/nav.php");

// Start or resume the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not, redirect to login page
if (!is_logged_in()) {
    flash("You must be logged in", "warning");
    header("Location: /Project/login.php");
    exit();
}
// Start or resume the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function insert_videos_into_db($db, $videos, $mappings)
{
    // Prepare SQL query
    $query = "INSERT INTO `VIDEO_DATA` ";
    if (count($videos) > 0) {
        $cols = array_keys($videos[0]);
        $query .= "(" . implode(",", array_map(function ($col) {
            return "`$col`";
        }, $cols)) . ") VALUES ";

        // Generate the VALUES clause for each video
        $values = [];
        foreach ($videos as $i => $video) {
            $videoPlaceholders = array_map(function ($v) use ($i) {
                return ":" . $v . $i;  // Append the index to make each placeholder unique
            }, $cols);
            $values[] = "(" . implode(",", $videoPlaceholders) . ")";
        }

        $query .= implode(",", $values);

        // Generate the ON DUPLICATE KEY UPDATE clause
        $updates = array_reduce($cols, function ($carry, $col) {
            $carry[] = "`$col` = VALUES(`$col`)";
            return $carry;
        }, []);

        $query .= " ON DUPLICATE KEY UPDATE " . implode(",", $updates);

        // Prepare the statement
        $stmt = $db->prepare($query);

        // Bind the parameters for each video
        foreach ($videos as $i => $video) {
            foreach ($cols as $col) {
                $placeholder = ":$col$i";
                $val = isset($video[$col]) ? $video[$col] : "";
                $param = PDO::PARAM_STR;
                if (str_contains($mappings[$col], "int")) {
                    $param = PDO::PARAM_INT;
                }
                $stmt->bindValue($placeholder, $val, $param);
            }
        }

        // Execute the statement
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            error_log(var_export($e, true));
        }
    }
}

function process_videos($result)
{
    $status = se($result, "status", 400, false);
    if ($status != 200) {
        return;
    }

    // Extract data from result
    $data_string = html_entity_decode(se($result, "response", "{}", false));
    $wrapper = "{\"data\":$data_string}";
    $data = json_decode($wrapper, true);
    if (!isset($data["data"])) {
        return;
    }
    $data = $data["data"];
    error_log("data: " . var_export($data, true));
    // echo ("data: " . var_export($data));
    // Get columns from CA_teams table
    $db = getDB();
    $stmt = $db->prepare("SHOW COLUMNS FROM VIDEO_DATA");
    $stmt->execute();
    $columnsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare columns and mappings
    $columns = array_column($columnsData, 'Field');
    $mappings = [];
    foreach ($columnsData as $column) {
        $mappings[$column['Field']] = $column['Type'];
    }
    $ignored = ["id", "created", "modified"];
    $columns = array_diff($columns, $ignored);
    $data = $data["contents"];
    
    // Process each video
    $videos = [];
    foreach ($data as $video) {
        $mappedVideo["channel_id"] = isset($video["video"]["channelId"]) ? $video["video"]["channelId"] : "Channel ID Not Found";
        $mappedVideo["title"] = isset($video["video"]["title"]) ? $video["video"]["title"] : "Untitled";
        $mappedVideo["channel_name"] = isset($video["video"]["channelName"]) ? $video["video"]["channelName"] : "Channel Name Not Found";
        $mappedVideo["length_text"] = isset($video["video"]["lengthText"]) ? $video["video"]["lengthText"] : "Length Not Found";
        $mappedVideo["published_time_text"] = isset($video["video"]["publishedTimeText"]) ? $video["video"]["publishedTimeText"] : "";
        $mappedVideo["video_id"] = isset($video["video"]["videoId"]) ? $video["video"]["videoId"] : "Video ID Not Found";
        $mappedVideo["view_count_text"] = isset($video["video"]["viewCountText"]) ? $video["video"]["viewCountText"] : "View Count Not Found";
        $mappedVideo["thumbnail_height"] = (int) (isset($video["video"]["thumbnails"]["0"]["height"]) ? $video["video"]["thumbnails"]["0"]["height"] : 0);
        $mappedVideo["thumbnail_url"] = isset($video["video"]["thumbnails"]["0"]["url"]) ? $video["video"]["thumbnails"]["0"]["url"] : "Thumbnail URL Not Found";
        $mappedVideo["thumbnail_width"] = (int) (isset($video["video"]["thumbnails"]["0"]["width"]) ? $video["video"]["thumbnails"]["0"]["width"] : 0);
    
        array_push($videos, $mappedVideo);
    }

    // Insert videos into database
    insert_videos_into_db($db, $videos, $mappings);
}

/**
 * Send a GET request to the specified URL.
 * 
 * @param string $url The URL to send the request to.
 * @param string $key The API key to use for the request.
 * @param array $data The data to send with the request.
 * @param bool $isRapidAPI Whether the request is for RapidAPI.
 * @param string $rapidAPIHost The host value for the RapidAPI Header
 * 
 * @return array The response status and body.
 *function get($url, $key, $data = [], $isRapidAPI = true, $rapidAPIHost = "")
 */
// Check if the form is submitted or if there's a previous search query
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["search_query"])) {
    // Get the search query from the form
    $searchQuery = $_GET["search_query"];

    // Store the search query in the session variable
    $_SESSION['search_query'] = $searchQuery;

    // Make the API request with the user's search query
    $result = get("https://youtube-search-and-download.p.rapidapi.com/search", "YOUTUBE_API", ["query" => $searchQuery . "type beat", "hl" => "en", "gl" => "US", "type" => "v", "sort" => "r"], true, "youtube-search-and-download.p.rapidapi.com");
    process_videos($result);
    // Process the API response
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $responseData = json_decode($result["response"], true);
    } else {
        $responseData = [];
    }
} else {
    // Set the search query from the session variable or handle the case when the form is not submitted
    $searchQuery = isset($_SESSION['search_query']) ? $_SESSION['search_query'] : "";
    $responseData = [];
}

?>

<!-- HTML code with the search bar -->
<div class="container-fluid">
    <h1>Type Beat Finder</h1>

    <!-- Search form -->
    <form method="get" action="">
        <label for="search_query">Artist Name:</label>
        <input type="text" id="search_query" name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit">Search</button>
    </form>

        <!-- Add jQuery (if not already included) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- JavaScript code for handling "Add to Favorites" button -->
    <script>
        $(document).ready(function () {
            $('.favorite-btn').on('click', function () {
                // Get the video ID from the button's data attribute
                var videoId = $(this).data('video-id');

                // Perform an AJAX request to add the video to favorites
                $.ajax({
                    type: 'POST',
                    url: 'add_to_favorites.php',  // Replace with the actual path
                    data: { videoId: videoId },
                    dataType: 'json',  // Specify JSON as the expected data type
                    success: function (response) {
                        // Handle the success response
                        if (response.success) {
                            console.log(response.message);
                            // Optionally, update the UI to reflect the addition to favorites
                        } else {
                            console.error(response.message);
                            // Handle the case where adding to favorites was not successful
                        }
                    },
                    error: function (error) {
                        // Handle the AJAX error
                        console.error('Error adding video to favorites:', error);
                    }
                });
            });
        });
    </script>


    <?php if (!empty($responseData['contents'])) : ?>
        <div class="row">
            <?php foreach ($responseData['contents'] as $video) : ?>
                <?php $videoData = $video['video']; ?>
                <div class="col">
                    <div class="card" style="width: 15em; margin-top: 20px">
                        <div class="video-container">
                            <iframe class="video-item" src="https://www.youtube.com/embed/<?php echo $videoData['videoId']; ?>" allowfullscreen></iframe>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo isset($videoData['title']) ? $videoData['title'] : "Untitled"; ?></h5>
                            <p class="card-text"><?php echo isset($videoData['channelName']) ? $videoData['channelName'] : "Channel Name Not Found"; ?></p>
                            <p class="card-text"><?php echo isset($videoData['viewCountText']) ? $videoData['viewCountText'] : "View Count Not Found"; ?></p>
                            <p class="card-text"><?php echo isset($videoData['lengthText']) ? $videoData['lengthText'] : "Length Not Found"; ?></p>
                            <p class="card-text"><?php echo isset($videoData['publishedTimeText']) ? $videoData['publishedTimeText'] : ""; ?></p>
                            
                            <!-- Favorites button container with Flexbox styling -->
                            <div style="display: flex; justify-content: center; align-items: center; height: 50px;">
                                <button class="favorite-btn" data-video-id="<?php echo $videoData['videoId']; ?>">Add to Favorites</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <?php if ($searchQuery) : ?>
            <p>Please enter an artist name to find beats.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

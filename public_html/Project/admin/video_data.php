<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}
//TODO need to update insert_videos... to use the $mappings array and not go based on is_int for value
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
    // echo var_export($data);
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

$action = se($_POST, "action", "", false);
if ($action) {
    switch ($action) {
        case "videos":
            // Get the search query from the session variable
            $searchQuery = isset($_SESSION['search_query']) ? $_SESSION['search_query'] : "lebron";

            $result = get("https://youtube-search-and-download.p.rapidapi.com/search", "YOUTUBE_API", ["query" => $searchQuery . "type beat", "hl" => "en", "gl" => "US", "type" => "v", "sort" => "r"], true, "youtube-search-and-download.p.rapidapi.com");
            process_videos($result);
            break;
    }
}
?>

<div class="container-fluid">
    <h1>Video Data Management</h1>
    <div class="row">
        <div class="col">
            <!-- Video Store button with search query -->
            <form method="POST">
                <input type="hidden" name="action" value="videos" />
                <input type="submit" class="btn btn-primary" value="Store New Videos" />
            </form>
        </div>
    </div>
</div>


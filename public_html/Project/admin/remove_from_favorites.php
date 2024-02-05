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

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the favorite ID from the POST data
    $favoriteId = se($_POST, "favorite_id", "", false);

    // Validate the favorite ID (you can add more validation if needed)
    if (!empty($favoriteId)) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM `USER_FAVORITES` WHERE id = :favoriteId");

        try {
            // Bind the parameter and execute the query
            $stmt->bindParam(":favoriteId", $favoriteId, PDO::PARAM_INT);
            $stmt->execute();

            // Provide a success message
            echo "Removed from favorites successfully!";
        } catch (PDOException $e) {
            // Provide an error message
            echo "Error removing from favorites: " . $e->getMessage();
        }
    } else {
        // Handle invalid favorite ID
        echo "Invalid favorite ID";
    }
} else {
    // Handle non-POST requests
    echo "Invalid request method";
}
?>

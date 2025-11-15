<?php
require_once "config.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating'])) {
    $recipe_id = (int)$_POST['recipe_id'];
    $user_id = $_SESSION['id'];
    $rating_value = (int)$_POST['rating_value'];

    // Validate rating value
    if ($rating_value >= 1 && $rating_value <= 5) {
        
        // Check if the user has already rated this recipe
        $check_sql = "SELECT rating_id FROM Rating WHERE user_id = ? AND recipe_id = ?";
        $rating_id = 0;
        if ($stmt = $conn->prepare($check_sql)) {
            $stmt->bind_param("ii", $user_id, $recipe_id);
            $stmt->execute();
            $stmt->bind_result($rating_id);
            $stmt->fetch();
            $stmt->close();
        }

        if ($rating_id > 0) {
            // UPDATE existing rating
            $sql = "UPDATE Rating SET rating_stars = ? WHERE rating_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ii", $rating_value, $rating_id);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            // INSERT new rating
            // Note: The db.sql uses 'rating_stars' not 'rating_value'
            $sql = "INSERT INTO Rating (recipe_id, user_id, rating_stars) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("iii", $recipe_id, $user_id, $rating_value);
                $stmt->execute();
                $stmt->close();
            }
        }
    } 

    // Redirect the user back to the recipe details page
    header("Location: recipe_details.php?id=" . $recipe_id);
    exit;

} else {
    header("Location: index.php");
    exit;
}
?>
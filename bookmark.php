<?php
require_once "config.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['recipe_id']) && !empty($_GET['recipe_id'])) {
    $recipe_id = (int)$_GET['recipe_id'];
    $user_id = $_SESSION['id'];
    $action = $_GET['action'] ?? 'toggle'; 

    // Determine if the recipe is already bookmarked
    $is_bookmarked = false;
    $check_sql = "SELECT COUNT(*) FROM Book_Mark WHERE user_id = ? AND recipe_id = ?";
    if ($stmt = $conn->prepare($check_sql)) {
        $stmt->bind_param("ii", $user_id, $recipe_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            $is_bookmarked = true;
        }
    }

    if ($action == 'add' || ($action == 'toggle' && !$is_bookmarked)) {
        // ADD bookmark
        if (!$is_bookmarked) {
            $insert_sql = "INSERT INTO Book_Mark (user_id, recipe_id) VALUES (?, ?)";
            if ($stmt = $conn->prepare($insert_sql)) {
                $stmt->bind_param("ii", $user_id, $recipe_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    } elseif ($action == 'remove' || ($action == 'toggle' && $is_bookmarked)) {
        // REMOVE bookmark
        if ($is_bookmarked) {
            $delete_sql = "DELETE FROM Book_Mark WHERE user_id = ? AND recipe_id = ?";
            if ($stmt = $conn->prepare($delete_sql)) {
                $stmt->bind_param("ii", $user_id, $recipe_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Redirect the user back to the recipe details page
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: account.php"); // Default redirect
    }
    exit;

} else {
    header("Location: account.php");
    exit;
}
?>
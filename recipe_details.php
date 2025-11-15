<?php
include_once 'header.php';

// Check if recipe ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: recipes.php");
    exit;
}

$recipe_id = intval($_GET['id']);
$conn->set_charset("utf8");

// Fetch recipe details with user information
$sql = "SELECT 
            R.recipe_id, R.recipe_name, R.description, R.image_url, 
            R.prep_time, R.cook_time, R.created_at,
            CD.difficulty_level, CT.cuisine_name, DR.dietary_name,
            U.user_id, U.user_name, U.email,
            AVG(RT.rating_stars) as avg_rating,
            COUNT(RT.rating_id) as rating_count
        FROM Recipe R
        LEFT JOIN Cooking_Difficulty CD ON R.cooking_difficulty = CD.difficulty_id
        LEFT JOIN Cuisine_type CT ON R.cuisine_type = CT.cuisine_id
        LEFT JOIN Dietary_Reference DR ON R.dietary_reference = DR.dietary_id
        LEFT JOIN User U ON R.user_id = U.user_id
        LEFT JOIN Rating RT ON R.recipe_id = RT.recipe_id
        WHERE R.recipe_id = ?
        GROUP BY R.recipe_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Recipe not found
    header("location: recipes.php");
    exit;
}

$recipe = $result->fetch_assoc();
$stmt->close();

// Fetch ingredients
$ingredients_sql = "SELECT I.name, RI.quantity 
                   FROM Recipe_Ingredient RI
                   JOIN Ingredient I ON RI.ingredient_id = I.ingredient_id
                   WHERE RI.recipe_id = ?";
$ingredients_stmt = $conn->prepare($ingredients_sql);
$ingredients_stmt->bind_param("i", $recipe_id);
$ingredients_stmt->execute();
$ingredients_result = $ingredients_stmt->get_result();
$ingredients = [];
while ($row = $ingredients_result->fetch_assoc()) {
    $ingredients[] = $row;
}
$ingredients_stmt->close();

// Fetch user's rating if logged in
$user_rating = null;
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $rating_sql = "SELECT rating_stars FROM Rating WHERE recipe_id = ? AND user_id = ?";
    $rating_stmt = $conn->prepare($rating_sql);
    $rating_stmt->bind_param("ii", $recipe_id, $_SESSION["user_id"]);
    $rating_stmt->execute();
    $rating_result = $rating_stmt->get_result();
    if ($rating_result->num_rows > 0) {
        $user_rating = $rating_result->fetch_assoc()['rating_stars'];
    }
    $rating_stmt->close();
}

// Handle rating submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["rating"])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
    
    $rating = intval($_POST["rating"]);
    if ($rating >= 1 && $rating <= 5) {
        // Check if user already rated
        $check_sql = "SELECT rating_id FROM Rating WHERE recipe_id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $recipe_id, $_SESSION["user_id"]);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing rating
            $update_sql = "UPDATE Rating SET rating_stars = ? WHERE recipe_id = ? AND user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iii", $rating, $recipe_id, $_SESSION["user_id"]);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert new rating
            $insert_sql = "INSERT INTO Rating (recipe_id, user_id, rating_stars) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iii", $recipe_id, $_SESSION["user_id"], $rating);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();
        
        // Refresh page to show updated rating
        header("location: recipe_details.php?id=" . $recipe_id);
        exit;
    }
}

// Handle bookmark
if (isset($_GET['bookmark'])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
    
    $bookmark_action = $_GET['bookmark'];
    if ($bookmark_action === 'add') {
        $bookmark_sql = "INSERT IGNORE INTO book_mark (user_id, recipe_id) VALUES (?, ?)";
        $bookmark_stmt = $conn->prepare($bookmark_sql);
        $bookmark_stmt->bind_param("ii", $_SESSION["user_id"], $recipe_id);
        $bookmark_stmt->execute();
        $bookmark_stmt->close();
    } elseif ($bookmark_action === 'remove') {
        $bookmark_sql = "DELETE FROM book_mark WHERE user_id = ? AND recipe_id = ?";
        $bookmark_stmt = $conn->prepare($bookmark_sql);
        $bookmark_stmt->bind_param("ii", $_SESSION["user_id"], $recipe_id);
        $bookmark_stmt->execute();
        $bookmark_stmt->close();
    }
    
    header("location: recipe_details.php?id=" . $recipe_id);
    exit;
}

// Check if current user has bookmarked this recipe
$is_bookmarked = false;
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $bookmark_check_sql = "SELECT 1 FROM book_mark WHERE user_id = ? AND recipe_id = ?";
    $bookmark_check_stmt = $conn->prepare($bookmark_check_sql);
    $bookmark_check_stmt->bind_param("ii", $_SESSION["user_id"], $recipe_id);
    $bookmark_check_stmt->execute();
    $bookmark_check_result = $bookmark_check_stmt->get_result();
    $is_bookmarked = $bookmark_check_result->num_rows > 0;
    $bookmark_check_stmt->close();
}

// Fetch more recipes from the same user
$user_recipes_sql = "SELECT recipe_id, recipe_name, image_url 
                     FROM Recipe 
                     WHERE user_id = ? AND recipe_id != ? 
                     ORDER BY created_at DESC 
                     LIMIT 3";
$user_recipes_stmt = $conn->prepare($user_recipes_sql);
$user_recipes_stmt->bind_param("ii", $recipe['user_id'], $recipe_id);
$user_recipes_stmt->execute();
$user_recipes_result = $user_recipes_stmt->get_result();
$user_recipes = [];
while ($row = $user_recipes_result->fetch_assoc()) {
    $user_recipes[] = $row;
}
$user_recipes_stmt->close();
?>

<section id="recipe-details">
    <div class="container">
        <!-- Recipe Header -->
        <div class="recipe-header">
            <div class="recipe-meta" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <a href="recipes.php" class="btn btn-ghost" style="margin-bottom: 1rem;">← Back to Recipes</a>
                    <h1><?php echo htmlspecialchars($recipe['recipe_name']); ?></h1>
                    <p class="muted">By <a href="user_profile.php?id=<?php echo $recipe['user_id']; ?>" style="color: var(--brand);"><?php echo htmlspecialchars($recipe['user_name']); ?></a> • Created on <?php echo date('F j, Y', strtotime($recipe['created_at'])); ?></p>
                </div>
                <div class="recipe-actions">
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <?php if ($is_bookmarked): ?>
                            <a href="recipe_details.php?id=<?php echo $recipe_id; ?>&bookmark=remove" class="btn btn-secondary" title="Remove from bookmarks">★ Bookmarked</a>
                        <?php else: ?>
                            <a href="recipe_details.php?id=<?php echo $recipe_id; ?>&bookmark=add" class="btn btn-ghost" title="Add to bookmarks">☆ Bookmark</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <img src="<?php echo htmlspecialchars($recipe['image_url'] ?? 'images/default_recipe.jpg'); ?>" 
                 alt="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" 
                 onerror="this.src='images/default_recipe.jpg'">
        </div>

        <div class="recipe-details-grid">
            <!-- Left Column: Recipe Info -->
            <div class="recipe-info">
                <!-- Rating Section -->
                <div class="rating-section" style="background: var(--card); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,.08);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <div class="rating-stars">
                                <?php
                                $avg_rating = $recipe['avg_rating'] ? round($recipe['avg_rating'], 1) : 0;
                                $full_stars = floor($avg_rating);
                                $has_half_star = ($avg_rating - $full_stars) >= 0.5;
                                
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $full_stars) {
                                        echo '★';
                                    } elseif ($i == $full_stars + 1 && $has_half_star) {
                                        echo '½';
                                    } else {
                                        echo '☆';
                                    }
                                }
                                ?>
                                <span style="margin-left: 0.5rem; color: var(--text);"><?php echo $avg_rating; ?> (<?php echo $recipe['rating_count']; ?> ratings)</span>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                            <form method="post" class="rating-form" style="display: flex; align-items: center; gap: 0.5rem;">
                                <select name="rating" onchange="this.form.submit()" style="width: auto; padding: 0.5rem;">
                                    <option value="">Rate this recipe</option>
                                    <option value="5" <?php echo ($user_rating == 5) ? 'selected' : ''; ?>>★ ★ ★ ★ ★</option>
                                    <option value="4" <?php echo ($user_rating == 4) ? 'selected' : ''; ?>>★ ★ ★ ★ ☆</option>
                                    <option value="3" <?php echo ($user_rating == 3) ? 'selected' : ''; ?>>★ ★ ★ ☆ ☆</option>
                                    <option value="2" <?php echo ($user_rating == 2) ? 'selected' : ''; ?>>★ ★ ☆ ☆ ☆</option>
                                    <option value="1" <?php echo ($user_rating == 1) ? 'selected' : ''; ?>>★ ☆ ☆ ☆ ☆</option>
                                </select>
                                <?php if ($user_rating): ?>
                                    <span class="muted">Your rating: <?php echo $user_rating; ?>★</span>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recipe Description -->
                <div class="recipe-description" style="background: var(--card); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,.08);">
                    <h3>About This Recipe</h3>
                    <p><?php echo nl2br(htmlspecialchars($recipe['description'] ?? 'No description provided.')); ?></p>
                </div>

                <!-- Recipe Metadata -->
                <div class="recipe-meta-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="background: var(--card); padding: 1rem; border-radius: var(--radius); text-align: center; border: 1px solid rgba(255,255,255,.08);">
                        <div style="font-size: 0.8rem; color: var(--muted);">Prep Time</div>
                        <div style="font-weight: 700; color: var(--brand);"><?php echo $recipe['prep_time'] ? $recipe['prep_time'] . ' min' : 'N/A'; ?></div>
                    </div>
                    <div style="background: var(--card); padding: 1rem; border-radius: var(--radius); text-align: center; border: 1px solid rgba(255,255,255,.08);">
                        <div style="font-size: 0.8rem; color: var(--muted);">Cook Time</div>
                        <div style="font-weight: 700; color: var(--brand);"><?php echo $recipe['cook_time'] ? $recipe['cook_time'] . ' min' : 'N/A'; ?></div>
                    </div>
                    <div style="background: var(--card); padding: 1rem; border-radius: var(--radius); text-align: center; border: 1px solid rgba(255,255,255,.08);">
                        <div style="font-size: 0.8rem; color: var(--muted);">Difficulty</div>
                        <div style="font-weight: 700; color: var(--brand-2);"><?php echo htmlspecialchars($recipe['difficulty_level'] ?? 'N/A'); ?></div>
                    </div>
                    <div style="background: var(--card); padding: 1rem; border-radius: var(--radius); text-align: center; border: 1px solid rgba(255,255,255,.08);">
                        <div style="font-size: 0.8rem; color: var(--muted);">Cuisine</div>
                        <div style="font-weight: 700; color: var(--text);"><?php echo htmlspecialchars($recipe['cuisine_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>

                <!-- Dietary Information -->
                <?php if ($recipe['dietary_name']): ?>
                <div class="dietary-info" style="background: var(--card); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,.08);">
                    <strong>Dietary: </strong>
                    <span class="tag"><?php echo htmlspecialchars($recipe['dietary_name']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Ingredients and Instructions -->
            <div class="recipe-content">
                <!-- Ingredients -->
                <div class="ingredients-section" style="background: var(--card); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,.08);">
                    <h3>Ingredients</h3>
                    <?php if (!empty($ingredients)): ?>
                        <ul class="ingredient-list">
                            <?php foreach ($ingredients as $ingredient): ?>
                                <li>
                                    <span style="color: var(--brand);">•</span>
                                    <?php 
                                    $display_text = $ingredient['name'];
                                    if (!empty($ingredient['quantity'])) {
                                        $display_text = $ingredient['quantity'] . ' ' . $display_text;
                                    }
                                    echo htmlspecialchars($display_text);
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="muted">No ingredients listed.</p>
                    <?php endif; ?>
                </div>

                <!-- Instructions -->
                <div class="instructions-section" style="background: var(--card); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,.08);">
                    <h3>Instructions</h3>
                    <p class="muted">Instructions are not stored in the current database structure. Consider adding an instructions table to your database.</p>
                    <div class="step-list">
                        <div class="step-item" style="background: rgba(255,255,255,.05); padding: 1rem; border-radius: 8px; margin-bottom: 0.5rem; border-left: 3px solid var(--brand);">
                            <strong>Step 1:</strong> Prepare your ingredients according to the list above.
                        </div>
                        <div class="step-item" style="background: rgba(255,255,255,.05); padding: 1rem; border-radius: 8px; margin-bottom: 0.5rem; border-left: 3px solid var(--brand);">
                            <strong>Step 2:</strong> Follow your preferred cooking method for this type of dish.
                        </div>
                        <div class="step-item" style="background: rgba(255,255,255,.05); padding: 1rem; border-radius: 8px; border-left: 3px solid var(--brand);">
                            <strong>Step 3:</strong> Serve and enjoy your delicious meal!
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- More from this Chef -->
        <?php if (!empty($user_recipes)): ?>
        <section class="chef-recipes" style="margin-top: 3rem;">
            <div class="section-head">
                <h3>More from <?php echo htmlspecialchars($recipe['user_name']); ?></h3>
                <a href="user_profile.php?id=<?php echo $recipe['user_id']; ?>" class="btn btn-ghost">View All Recipes</a>
            </div>
            <div class="menu-grid">
                <?php foreach ($user_recipes as $user_recipe): ?>
                <article class="card">
                    <div class="thumb">
                        <img src="<?php echo htmlspecialchars($user_recipe['image_url'] ?? 'images/default_recipe.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($user_recipe['recipe_name']); ?>"
                             onerror="this.src='images/default_recipe.jpg'">
                    </div>
                    <div class="body">
                        <h3><?php echo htmlspecialchars($user_recipe['recipe_name']); ?></h3>
                        <div class="row" style="margin-top:auto;">
                            <a class="btn btn-ghost" href="recipe_details.php?id=<?php echo $user_recipe['recipe_id']; ?>">View Recipe</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</section>

<?php include_once 'footer.php'; ?>
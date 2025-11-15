<?php
include_once 'header.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch filter options for the form
$cuisines = $difficulties = $dietaries = [];
$conn->set_charset("utf8");

// Fetch Cuisines
$result = $conn->query("SELECT cuisine_id, cuisine_name FROM Cuisine_type");
while ($row = $result->fetch_assoc()) { $cuisines[$row['cuisine_id']] = $row['cuisine_name']; }

// Fetch Difficulties
$result = $conn->query("SELECT difficulty_id, difficulty_level FROM Cooking_Difficulty");
while ($row = $result->fetch_assoc()) { $difficulties[$row['difficulty_id']] = $row['difficulty_level']; }

// Fetch Dietaries
$result = $conn->query("SELECT dietary_id, dietary_name FROM Dietary_Reference");
while ($row = $result->fetch_assoc()) { $dietaries[$row['dietary_id']] = $row['dietary_name']; }

// Initialize variables
$recipe_name = $description = $prep_time = $cook_time = $image_url = "";
$cuisine_type = $cooking_difficulty = $dietary_reference = "";
$ingredients = [""]; // Start with one empty ingredient
$instructions = [""]; // Start with one empty instruction
$error = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get basic recipe data
    $recipe_name = trim($_POST["recipe_name"]);
    $description = trim($_POST["description"]);
    $prep_time = trim($_POST["prep_time"]);
    $cook_time = trim($_POST["cook_time"]);
    $image_url = trim($_POST["image_url"]);
    $cuisine_type = $_POST["cuisine_type"];
    $cooking_difficulty = $_POST["cooking_difficulty"];
    $dietary_reference = $_POST["dietary_reference"];
    
    // Get ingredients and instructions arrays
    $ingredients = array_filter($_POST["ingredients"], function($ingredient) {
        return !empty(trim($ingredient));
    });
    $instructions = array_filter($_POST["instructions"], function($instruction) {
        return !empty(trim($instruction));
    });
    
    // Validation
    if (empty($recipe_name)) {
        $error = "Recipe name is required.";
    } elseif (empty($ingredients)) {
        $error = "At least one ingredient is required.";
    } elseif (empty($instructions)) {
        $error = "At least one instruction step is required.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert recipe
            $sql = "INSERT INTO Recipe (user_id, recipe_name, description, cuisine_type, prep_time, cook_time, image_url, cooking_difficulty, dietary_reference) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issiiisii", $_SESSION["user_id"], $recipe_name, $description, $cuisine_type, $prep_time, $cook_time, $image_url, $cooking_difficulty, $dietary_reference);
            $stmt->execute();
            $recipe_id = $conn->insert_id;
            $stmt->close();
            
            // Insert ingredients
            foreach ($ingredients as $ingredient_text) {
                // Check if ingredient exists, if not insert it
                $ingredient_sql = "SELECT ingredient_id FROM Ingredient WHERE name = ?";
                $ingredient_stmt = $conn->prepare($ingredient_sql);
                $ingredient_stmt->bind_param("s", $ingredient_text);
                $ingredient_stmt->execute();
                $ingredient_result = $ingredient_stmt->get_result();
                
                if ($ingredient_result->num_rows > 0) {
                    $ingredient_row = $ingredient_result->fetch_assoc();
                    $ingredient_id = $ingredient_row['ingredient_id'];
                } else {
                    $insert_ingredient_sql = "INSERT INTO Ingredient (name) VALUES (?)";
                    $insert_ingredient_stmt = $conn->prepare($insert_ingredient_sql);
                    $insert_ingredient_stmt->bind_param("s", $ingredient_text);
                    $insert_ingredient_stmt->execute();
                    $ingredient_id = $conn->insert_id;
                    $insert_ingredient_stmt->close();
                }
                $ingredient_stmt->close();
                
                // Link ingredient to recipe
                $recipe_ingredient_sql = "INSERT INTO Recipe_Ingredient (recipe_id, ingredient_id, quantity) VALUES (?, ?, '')";
                $recipe_ingredient_stmt = $conn->prepare($recipe_ingredient_sql);
                $recipe_ingredient_stmt->bind_param("ii", $recipe_id, $ingredient_id);
                $recipe_ingredient_stmt->execute();
                $recipe_ingredient_stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to recipe details page
            header("location: recipe_details.php?id=" . $recipe_id);
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = "Error adding recipe: " . $e->getMessage();
        }
    }
}
?>

<section id="add-recipe">
    <div class="container">
        <div class="page-header">
            <h1>Share Your Recipe</h1>
            <p class="muted">Inspire others with your culinary creations</p>
        </div>

        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="recipe_name">Recipe Name *</label>
                            <input type="text" id="recipe_name" name="recipe_name" value="<?php echo htmlspecialchars($recipe_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>" placeholder="https://example.com/image.jpg">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="Describe your recipe..."><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                </div>

                <!-- Recipe Details -->
                <div class="form-section">
                    <h3>Recipe Details</h3>
                    <div class="grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="prep_time">Prep Time (minutes)</label>
                            <input type="number" id="prep_time" name="prep_time" value="<?php echo htmlspecialchars($prep_time); ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label for="cook_time">Cook Time (minutes)</label>
                            <input type="number" id="cook_time" name="cook_time" value="<?php echo htmlspecialchars($cook_time); ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label for="cooking_difficulty">Difficulty</label>
                            <select id="cooking_difficulty" name="cooking_difficulty">
                                <option value="">Select Difficulty</option>
                                <?php foreach ($difficulties as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo ($cooking_difficulty == $id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="cuisine_type">Cuisine Type</label>
                            <select id="cuisine_type" name="cuisine_type">
                                <option value="">Select Cuisine</option>
                                <?php foreach ($cuisines as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo ($cuisine_type == $id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dietary_reference">Dietary Reference</label>
                            <select id="dietary_reference" name="dietary_reference">
                                <option value="">Select Dietary</option>
                                <?php foreach ($dietaries as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo ($dietary_reference == $id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Ingredients -->
                <div class="form-section">
                    <h3>Ingredients *</h3>
                    <div id="ingredients-container">
                        <?php foreach ($ingredients as $index => $ingredient): ?>
                            <div class="ingredient-group">
                                <div class="form-group">
                                    <label>Ingredient <?php echo $index + 1; ?></label>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <input type="text" name="ingredients[]" value="<?php echo htmlspecialchars($ingredient); ?>" 
                                               placeholder="e.g., 2 cups flour" style="flex: 1;">
                                        <?php if ($index > 0): ?>
                                            <button type="button" class="btn btn-ghost remove-ingredient" style="padding: 8px 12px;">×</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-ingredient" class="btn btn-secondary">+ Add Another Ingredient</button>
                </div>

                <!-- Instructions -->
                <div class="form-section">
                    <h3>Instructions *</h3>
                    <div id="instructions-container">
                        <?php foreach ($instructions as $index => $instruction): ?>
                            <div class="instruction-group">
                                <div class="form-group">
                                    <label>Step <?php echo $index + 1; ?></label>
                                    <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                                        <textarea name="instructions[]" rows="2" placeholder="Describe this step..." style="flex: 1;"><?php echo htmlspecialchars($instruction); ?></textarea>
                                        <?php if ($index > 0): ?>
                                            <button type="button" class="btn btn-ghost remove-instruction" style="padding: 8px 12px; margin-top: 24px;">×</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-instruction" class="btn btn-secondary">+ Add Another Step</button>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Share Recipe</button>
                    <a href="recipes.php" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Add/Remove Ingredients
document.getElementById('add-ingredient').addEventListener('click', function() {
    const container = document.getElementById('ingredients-container');
    const index = container.children.length;
    const div = document.createElement('div');
    div.className = 'ingredient-group';
    div.innerHTML = `
        <div class="form-group">
            <label>Ingredient ${index + 1}</label>
            <div style="display: flex; gap: 0.5rem;">
                <input type="text" name="ingredients[]" placeholder="e.g., 2 cups flour" style="flex: 1;">
                <button type="button" class="btn btn-ghost remove-ingredient" style="padding: 8px 12px;">×</button>
            </div>
        </div>
    `;
    container.appendChild(div);
});

// Add/Remove Instructions
document.getElementById('add-instruction').addEventListener('click', function() {
    const container = document.getElementById('instructions-container');
    const index = container.children.length;
    const div = document.createElement('div');
    div.className = 'instruction-group';
    div.innerHTML = `
        <div class="form-group">
            <label>Step ${index + 1}</label>
            <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                <textarea name="instructions[]" rows="2" placeholder="Describe this step..." style="flex: 1;"></textarea>
                <button type="button" class="btn btn-ghost remove-instruction" style="padding: 8px 12px; margin-top: 24px;">×</button>
            </div>
        </div>
    `;
    container.appendChild(div);
});

// Remove ingredient/instruction
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-ingredient') || e.target.classList.contains('remove-instruction')) {
        e.target.closest('.ingredient-group, .instruction-group').remove();
        // Update labels
        updateLabels('ingredients-container', 'Ingredient');
        updateLabels('instructions-container', 'Step');
    }
});

function updateLabels(containerId, prefix) {
    const container = document.getElementById(containerId);
    const groups = container.getElementsByClassName('form-group');
    for (let i = 0; i < groups.length; i++) {
        const label = groups[i].getElementsByTagName('label')[0];
        label.textContent = `${prefix} ${i + 1}`;
    }
}
</script>

<?php include_once 'footer.php'; ?>
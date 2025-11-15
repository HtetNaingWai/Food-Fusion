<?php
include_once 'header.php'; 

// Fetch Featured Recipes (Top 4 rated or latest 4)
$featured_recipes = [];
// Assuming Recipe is an indexable page (Task 3.3)
$sql_recipes = "SELECT R.recipe_id, R.recipe_name, R.description, R.image_url 
                FROM Recipe R 
                ORDER BY R.created_at DESC 
                LIMIT 4"; 
if ($result = $conn->query($sql_recipes)) {
    while ($row = $result->fetch_assoc()) {
        $featured_recipes[] = $row;
    }
}

// Fetch Upcoming Event (for Hero Card or Carousel)
$upcoming_event = null;
$sql_event = "SELECT title, date, description FROM Events ORDER BY date ASC LIMIT 1";
if ($result = $conn->query($sql_event)) {
    $upcoming_event = $result->fetch_assoc();
}
?>

<section class="hero">
    <div class="container hero-wrap">
      <div>
        <span class="tag">🔥 New • Limited Time</span>
        <h1 class="headline">Taste the Blend of <span style="color:var(--brand)">World Cuisines</span>.</h1>
        <p class="sub">Welcome to Food Fusion — where classic recipes meet bold creativity. Explore a curated menu of Asian–Western mashups, vibrant bowls, and chef-crafted street eats.</p>
        <div class="hero-cta">
          <a href="recipes.php" class="btn btn-primary">Explore Recipes</a>
          <a href="about.php" class="btn btn-ghost">Our Story</a>
        </div>
        
        <?php if ($upcoming_event): ?>
        <div class="grid" style="margin-top:20px">
          <div class="hero-card">
            <img src="https://images.unsplash.com/photo-1579782522770-b7470f1469e6?q=80&w=800&auto=format&fit=crop" alt="Event image"/>
            <div>
              <div class="hero-badge">Upcoming Event • Register Now</div>
              <strong><?php echo htmlspecialchars($upcoming_event['title']); ?></strong>
              <div class="row">
                  <span class="muted"><?php echo date("F j", strtotime($upcoming_event['date'])); ?></span>
                  <a href="community.php#events" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;">View</a>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
      <div>
        <img src="https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=1100&auto=format&fit=crop" alt="Food Fusion hero" style="width:100%; border-radius:24px; box-shadow:var(--shadow); object-fit:cover; height:460px"/>
      </div>
    </div>
</section>

<section id="menu">
    <div class="container">
      <div class="section-head">
        <h2>Featured Recipes</h2>
        <a href="recipes.php" class="btn btn-ghost" style="padding:10px 15px;">View All</a>
      </div>

      <div class="menu-grid" id="menuGrid">
        <?php if (empty($featured_recipes)): ?>
            <p class="muted" style="grid-column: 1 / -1;">No featured recipes available yet. Please add recipes to the database.</p>
        <?php endif; ?>
        <?php foreach ($featured_recipes as $recipe): ?>
        <article class="card" data-tags="featured">
          <div class="thumb">
            <img src="<?php echo htmlspecialchars($recipe['image_url'] ?? 'images/default_recipe.jpg'); ?>" alt="<?php echo htmlspecialchars($recipe['recipe_name']); ?>"/>
          </div>
          <div class="body">
            <h3><?php echo htmlspecialchars($recipe['recipe_name']); ?></h3>
            <p class="muted"><?php echo htmlspecialchars(substr($recipe['description'], 0, 70)) . '...'; ?></p>
            <div class="row" style="margin-top:auto;">
              <span class="price">Avg Rating: 4.5/5</span>
              <a class="btn btn-ghost" href="recipe_details.php?id=<?php echo $recipe['recipe_id']; ?>">View Recipe</a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <div class="cta">
        <div>
          <h3 style="margin:0 0 6px">Contribute to the Community Cookbook</h3>
          <div class="muted">Share your own fusion recipes and culinary tips with the community.</div>
        </div>
        <a class="btn btn-primary" href="community.php">Share Recipe</a>
      </div>

    </div>
</section>

<section id="about">
    <div class="container about">
      <div class="panel">
        <span class="tag">Our Story</span>
        <h2 style="margin:10px 0 8px">Crafted with Curiosity</h2>
        <p class="muted">We mix techniques and flavors from across the globe — Japanese precision, Italian comfort, Thai brightness, and more. Every week we ship in-season produce and grind spices in-house to get layers of flavor that pop.</p>
        <div class="grid" style="grid-template-columns:repeat(3,1fr); gap:14px; margin-top:10px">
          <div>
            <strong>48h</strong>
            <div class="muted">Ferments</div>
          </div>
          <div>
            <strong>12+</strong>
            <div class="muted">Fusion staples</div>
          </div>
          <div>
            <strong>100%</strong>
            <div class="muted">Fresh & bold</div>
          </div>
        </div>
      </div>
      <div class="grid" style="gap:14px">
        <img src="https://images.unsplash.com/photo-1482049016688-2d3e1b311543?q=80&w=1200&auto=format&fit=crop" alt="Kitchen action" style="width:100%; border-radius:18px; height:220px; object-fit:cover"/>
        <img src="https://images.unsplash.com/photo-1550547660-d9450f859349?q=80&w=1200&auto=format&fit=crop" alt="Plating" style="width:100%; border-radius:18px; height:220px; object-fit:cover"/>
      </div>
    </div>
</section>

<section>
    <div class="container">
      <div class="section-head">
        <h2>What People Say</h2>
      </div>
      <div class="testi">
        <div class="quote">
          <div class="stars">★★★★★</div>
          “A playful mix of flavors that still feels balanced. The kimchi pasta is elite.”
          <div class="muted">— Lin, food blogger</div>
        </div>
        <div class="quote">
          <div class="stars">★★★★★</div>
          “Fresh, fast, and creative. Every bite is a little surprise.”
          <div class="muted">— Omar, regular</div>
        </div>
        <div class="quote">
          <div class="stars">★★★★★</div>
          “Finally a menu that lets me choose spice and texture. Love the bowls.”
          <div class="muted">— Mei, designer</div>
        </div>
      </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>
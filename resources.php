<?php include 'header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>Culinary Resources</h1>
        <p class="muted">Download recipe cards, watch cooking tutorials, and master kitchen techniques</p>
    </div>

    <div class="resources-grid">
        <!-- Recipe Cards Section -->
        <section class="resource-section">
            <h2>📋 Downloadable Recipe Cards</h2>
            <div class="download-grid grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                <div class="card">
                    <div class="body">
                        <div class="download-icon" style="font-size: 2.5rem; margin-bottom: 1rem;">📄</div>
                        <h3>Beginner's Recipe Collection</h3>
                        <p class="muted">10 easy recipes for beginners with step-by-step instructions</p>
                        <a href="downloads/beginner_recipes.pdf" class="btn btn-primary" download>Download PDF</a>
                    </div>
                </div>
                <div class="card">
                    <div class="body">
                        <div class="download-icon" style="font-size: 2.5rem; margin-bottom: 1rem;">📄</div>
                        <h3>Healthy Meal Prep Guide</h3>
                        <p class="muted">Weekly meal prep recipes with nutritional information</p>
                        <a href="downloads/healthy_meal_prep.pdf" class="btn btn-primary" download>Download PDF</a>
                    </div>
                </div>
                <div class="card">
                    <div class="body">
                        <div class="download-icon" style="font-size: 2.5rem; margin-bottom: 1rem;">📄</div>
                        <h3>International Cuisine Pack</h3>
                        <p class="muted">Recipes from around the world with cultural notes</p>
                        <a href="downloads/international_cuisine.pdf" class="btn btn-primary" download>Download PDF</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cooking Tutorials Section -->
        <section class="resource-section">
            <h2>🎬 Cooking Tutorials</h2>
            <div class="video-grid grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="card">
                    <div class="thumb">
                        <div class="video-placeholder" style="background: linear-gradient(135deg, var(--brand), #ff5f6d); display: flex; align-items: center; justify-content: center;">
                            <div class="play-icon" style="background: rgba(255,255,255,0.9); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: var(--brand);">▶</div>
                        </div>
                    </div>
                    <div class="body">
                        <h3>Essential Knife Skills</h3>
                        <p class="muted">Master basic cutting techniques for efficient cooking</p>
                        <div class="row">
                            <span class="tag">🎯 Beginner</span>
                            <span class="muted">15 min</span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="thumb">
                        <div class="video-placeholder" style="background: linear-gradient(135deg, var(--brand-2), #ffb347); display: flex; align-items: center; justify-content: center;">
                            <div class="play-icon" style="background: rgba(255,255,255,0.9); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: var(--brand-2);">▶</div>
                        </div>
                    </div>
                    <div class="body">
                        <h3>Sauce Making Basics</h3>
                        <p class="muted">Learn to create 5 mother sauces from scratch</p>
                        <div class="row">
                            <span class="tag">👨‍🍳 Intermediate</span>
                            <span class="muted">22 min</span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="thumb">
                        <div class="video-placeholder" style="background: linear-gradient(135deg, #76e0a5, #52c1ff); display: flex; align-items: center; justify-content: center;">
                            <div class="play-icon" style="background: rgba(255,255,255,0.9); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #52c1ff;">▶</div>
                        </div>
                    </div>
                    <div class="body">
                        <h3>Baking Fundamentals</h3>
                        <p class="muted">Perfect your baking measurements and techniques</p>
                        <div class="row">
                            <span class="tag">🍰 Advanced</span>
                            <span class="muted">18 min</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Kitchen Hacks Section -->
        <section class="resource-section">
            <h2>💡 Kitchen Hacks & Techniques</h2>
            <div class="hacks-grid grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div class="card">
                    <div class="body">
                        <h3>Food Preservation</h3>
                        <ul style="list-style: none; padding: 0; color: var(--muted);">
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Proper herb storage methods</li>
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Freezing techniques for different foods</li>
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Canning and pickling basics</li>
                        </ul>
                    </div>
                </div>
                <div class="card">
                    <div class="body">
                        <h3>Time-Saving Tips</h3>
                        <ul style="list-style: none; padding: 0; color: var(--muted);">
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Meal prep strategies</li>
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Batch cooking techniques</li>
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Quick cleaning methods</li>
                        </ul>
                    </div>
                </div>
                <div class="card">
                    <div class="body">
                        <h3>Ingredient Substitutions</h3>
                        <ul style="list-style: none; padding: 0; color: var(--muted);">
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Common baking substitutes</li>
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Allergy-friendly alternatives</li>
                            <li style="padding: 0.25rem 0; position: relative; padding-left: 1rem;">• Seasoning replacements</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include 'footer.php'; ?>
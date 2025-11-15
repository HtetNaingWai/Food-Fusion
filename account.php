<?php
require_once 'config.php';

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

include_once 'header.php';
?>

<section>
    <div class="container" style="max-width: 800px;">
        <span class="tag">Account Dashboard</span>
        <h2 style="margin:10px 0 8px">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>
        <p class="muted">Manage your profile, recipes, and saved items here.</p>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-top: 30px;">
            <div class="card" style="padding:20px;">
                <h3>Profile Actions</h3>
                <p class="muted">Update your security settings.</p>
                <div class="grid" style="gap:10px;">
                    <a href="reset_password.php" class="btn btn-secondary">Change Password</a>
                    <a href="logout.php" class="btn btn-ghost" style="color: var(--brand);">Sign Out</a>
                </div>
            </div>
            
            <div class="card" style="padding:20px;">
                <h3>My Recipes</h3>
                <p class="muted">View the recipes you have submitted.</p>
                <a href="community.php?user_id=<?php echo $_SESSION['id']; ?>" class="btn btn-primary">View Submitted</a>
            </div>

            <div class="card" style="padding:20px;">
                <h3>Saved Recipes</h3>
                <p class="muted">Check out your bookmarked fusion favorites.</p>
                <a href="recipes.php?view=bookmarks" class="btn btn-primary">View Bookmarks</a>
            </div>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>
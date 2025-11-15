<?php
include_once 'header.php';

// Logic to handle new community post submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_cookbook'])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }

    $user_id = $_SESSION['id'];
    $post_msg = trim($_POST['post_msg']);

    if (!empty($post_msg)) {
        $sql = "INSERT INTO Community_CookBook (user_id, PostMsg) VALUES (?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $user_id, $post_msg);
            $stmt->execute();
            $stmt->close();
            // Redirect to prevent form resubmission
            header("location: community.php?status=success#posts");
            exit;
        }
    }
}

// Fetch all community posts
$posts = [];
$sql_posts = "SELECT C.*, U.user_name 
              FROM Community_CookBook C
              LEFT JOIN User U ON C.user_id = U.user_id
              ORDER BY C.shared_date DESC";
if ($result = $conn->query($sql_posts)) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
?>

<section id="community-page">
    <div class="container">
        <span class="tag">Collaborate & Share</span>
        <h2 style="margin:10px 0 8px">Community Cookbook</h2>
        <p class="sub">Share your favorite recipes, cooking tips, and culinary experiences with the FoodFusion community.</p>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success">Your post has been shared successfully!</div>
        <?php endif; ?>

        <div class="panel" style="margin-top: 30px; padding: 24px;">
            <h3>Share Your Tip or Recipe Idea</h3>
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <form action="community.php" method="post" class="grid" style="gap:14px;">
                    <textarea name="post_msg" rows="4" placeholder="What's cooking? Share your text, links, or tips here..." required></textarea>
                    <button type="submit" name="post_cookbook" class="btn btn-primary" style="width: auto;">Post to Cookbook</button>
                </form>
            <?php else: ?>
                <p class="muted">Please <a href="login.php" style="color:var(--brand-2);">login</a> to contribute to the community cookbook.</p>
            <?php endif; ?>
        </div>

        <h3 id="posts" style="margin-top: 40px; border-bottom: 1px solid rgba(255,255,255,.1); padding-bottom: 10px;">Latest Community Posts</h3>
        
        <?php if (empty($posts)): ?>
            <p class="muted">No community posts yet. Be the first to share!</p>
        <?php endif; ?>
        
        <div class="grid" style="gap: 20px; grid-template-columns: 1fr;">
            <?php foreach ($posts as $post): ?>
                <article class="quote">
                    <p><?php echo nl2br(htmlspecialchars($post['PostMsg'])); ?></p>
                    <div class="muted" style="margin-top: 10px; font-size: 13px;">
                        — <strong><?php echo htmlspecialchars($post['user_name'] ?? 'Guest'); ?></strong> on <?php echo date("M j, Y", strtotime($post['shared_date'])); ?>
                        </div>
                </article>
            <?php endforeach; ?>
        </div>

        <h2 id="events" style="margin-top: 60px; border-bottom: 1px solid rgba(255,255,255,.1); padding-bottom: 10px;">Upcoming Cooking Events</h2>
        <div class="grid" style="gap: 20px; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-top: 20px;">
            <div class="card" style="padding:20px;">
                <h3>Fusion Masterclass</h3>
                <p class="muted">Date: October 25, 2025</p>
                <p>Learn to blend Asian and Latin flavors with our head chef.</p>
                <a href="#" class="btn btn-primary" style="padding: 10px 15px; font-size: 14px; margin-top: 10px;">Register (Uses Event_Registration table)</a>
            </div>
            </div>

    </div>
</section>

<?php include_once 'footer.php'; ?>
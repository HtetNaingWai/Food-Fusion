<?php
require_once 'config.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    session_unset();
    session_destroy();
    header("location: login.php?error=lockout");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Food Fusion | Taste the Blend</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div id="signup-modal" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close" onclick="closeModal()">×</button>
      <h2>Join Food Fusion</h2>
      <p class="muted">Sign up to share your culinary creations and connect with our community.</p>
      <form action="register.php" method="post" class="grid" style="gap:14px">
          <input type="text" name="username" placeholder="Username" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password (min 6 chars)" required />
          <input type="password" name="confirm_password" placeholder="Confirm Password" required />
          <button class="btn btn-primary" type="submit">Sign Up Now</button>
      </form>
    </div>
  </div>

  <header>
    <div class="container nav">
      <a class="brand" href="index.php">
        <span class="logo"></span>
        <span>Food <span style="color:var(--brand-2)">Fusion</span></span>
      </a>
      <nav>
        <ul>
          <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
          <li><a href="recipes.php" class="<?php echo ($current_page == 'recipes.php') ? 'active' : ''; ?>">Recipes</a></li>
          <li><a href="community.php" class="<?php echo ($current_page == 'community.php') ? 'active' : ''; ?>">Community</a></li>
          <li><a href="resources.php" class="<?php echo ($current_page == 'resources.php') ? 'active' : ''; ?>">Culinary Resources</a></li>
          <li><a href="educational.php" class="<?php echo ($current_page == 'educational.php') ? 'active' : ''; ?>">Educational</a></li>
          <li><a href="about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">About Us</a></li>
          <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact Us</a></li>
        </ul>
      </nav>
      <div class="nav-actions">
        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
          <a class="btn btn-primary" href="account.php">Account</a>
          <a class="btn btn-ghost" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="btn btn-ghost" href="login.php">Login</a>
          <a class="btn btn-primary" href="#" onclick="openModal()">Sign Up Now</a> <?php endif; ?>
        <button class="menu-toggle" aria-label="Open menu" onclick="toggleMenu()">Menu</button>
      </div>
    </div>
  </header>

  <main>
    <div class="container">
    </div>
  </main>
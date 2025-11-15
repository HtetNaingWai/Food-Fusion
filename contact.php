<?php
include_once 'header.php';

$name = $email = $message = $success_msg = $error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $error_msg = "Please fill out all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format.";
    } else {
        // Insert into Contact_Us table
        $sql = "INSERT INTO Contact_Us (name, email, message) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $name, $email, $message);
            if ($stmt->execute()) {
                $success_msg = "Thank you for your message! We will be in touch soon.";
                // Clear inputs
                $name = $email = $message = "";
            } else {
                $error_msg = "Oops! Something went wrong on the server: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<section id="contact">
    <div class="container">
      <div class="section-head">
        <h2>Contact & Enquiries</h2>
      </div>
      
      <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
      <?php endif; ?>
      <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
      <?php endif; ?>

      <div class="grid" style="grid-template-columns:1.2fr .8fr">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="grid" style="gap:14px">
          <div>
            <label for="name">Name</label>
            <input id="name" name="name" placeholder="Your name" value="<?php echo htmlspecialchars($name); ?>" required />
          </div>
          <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($email); ?>" required />
          </div>
          <div>
            <label for="msg">Message</label>
            <textarea id="msg" name="message" rows="4" placeholder="Ask your question, give feedback, or send a recipe request..."><?php echo htmlspecialchars($message); ?></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Send Message</button>
        </form>

        <div class="panel">
          <h3 style="margin:0 0 8px">Visit Us</h3>
          <p class="muted">123 Fusion Street, Flavor Town</p>
          <p class="muted">Email: info@foodfusion.com</p>
          <p class="muted">Phone: (555) FUS-ION2</p>
          <p class="muted" style="margin-top: 15px;">Open: Mon–Sun, 10:00–22:00</p>
          <div class="social" style="margin-top:10px">
            <a href="#" aria-label="Facebook">f</a>
            <a href="#" aria-label="Instagram">i</a>
            <a href="#" aria-label="TikTok">t</a>
          </div>
        </div>
      </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>
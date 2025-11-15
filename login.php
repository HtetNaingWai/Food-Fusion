<?php
require_once 'config.php';
include_once 'header.php'; // Header handles lockout redirect

$email = $password = "";
$email_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Check for lockout based on IP/email (using email for simplicity)
    $input_email = trim($_POST["email"]);
    $sql = "SELECT user_id, user_name, password_hash, failed_attempts, lockout_time FROM User WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $input_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $username, $hashed_password, $failed_attempts, $lockout_time);
            $stmt->fetch();

            $stmt->close(); // Close previous statement

            // Check if account is locked
            if (!empty($lockout_time) && strtotime($lockout_time) > time()) {
                $login_err = "This account is locked due to too many failed attempts. Please try again after 3 minutes.";
            } else {
                // 2. Process login
                $password = trim($_POST["password"]);
                
                if (empty($password)) {
                    $password_err = "Please enter your password.";
                } elseif (password_verify($password, $hashed_password)) {
                    // Password is correct, reset attempts and log in
                    
                    // Reset failed attempts
                    $update_sql = "UPDATE User SET failed_attempts = 0, lockout_time = NULL WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Start session
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $user_id;
                    $_SESSION["username"] = $username;                            
                    
                    header("location: account.php");
                    exit;

                } else {
                    // Password is NOT valid, increment attempts and check for lockout 
                    $failed_attempts++;
                    $lockout_time = null;

                    if ($failed_attempts >= 3) {
                     $lockout_time = date('Y-m-d H:i:s', time() + 180);
                     $login_err = "Too many failed attempts! Account locked for 3 minutes. Please try again at " . date('H:i:s', time() + 180);
             } else {
                     $remaining_attempts = 3 - $failed_attempts;
                        $password_err = "Invalid password. You have " . $remaining_attempts . " attempt(s) remaining.";
            }

                    // Update attempts and lockout time
                    $update_sql = "UPDATE User SET failed_attempts = ?, lockout_time = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("isi", $failed_attempts, $lockout_time, $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }

        } else {
            $login_err = "No account found with that email address.";
        }
    } else {
        $login_err = "Oops! Something went wrong on the server.";
    }
}
?>

<div class="form-container">
    <h2>Login</h2>
    <p class="muted">Please fill in your credentials to login.</p>

    <?php 
    if(!empty($login_err)){
        echo '<div class="alert alert-danger">' . $login_err . '</div>';
    } 
    if (isset($_GET['error']) && $_GET['error'] == 'lockout') {
        echo '<div class="alert alert-danger">Your account was locked due to too many failed attempts. Please try again later.</div>';
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
            <span class="help-block"><?php echo $email_err; ?></span>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
            <span class="help-block"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Login">
        </div>
        <p class="muted">Don't have an account? <a href="register.php" onclick="openModal(); return false;">Sign up now</a>.</p>
</div>

<?php include_once 'footer.php'; ?>
<?php
require_once "config.php";
include_once "header.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$old_password = $new_password = $confirm_password = "";
$old_password_err = $new_password_err = $confirm_password_err = $general_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["id"];

    // 1. Validate old password
    if (empty(trim($_POST["old_password"]))) {
        $old_password_err = "Please enter your current password.";
    } else {
        $old_password = trim($_POST["old_password"]);

        // Fetch current password hash from DB
        $sql = "SELECT password_hash FROM User WHERE user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($hashed_password);
                    if ($stmt->fetch()) {
                        if (!password_verify($old_password, $hashed_password)) {
                            $old_password_err = "The current password you entered is incorrect.";
                        }
                    }
                }
            }
            $stmt->close();
        }
    }

    // 2. Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // 3. Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the new password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }

    // 4. Check errors and update DB
    if (empty($old_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
        
        $sql = "UPDATE User SET password_hash = ? WHERE user_id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_password, $param_user_id);

            // Hash the new password (Task 2 Security)
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_user_id = $user_id;

            if ($stmt->execute()) {
                $success_msg = "Your password was successfully updated.";
                // Clear the password fields
                $old_password = $new_password = $confirm_password = "";
            } else {
                $general_err = "Oops! Something went wrong. Please try again later. Error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<div class="form-container">
    <h2>Change Password</h2>
    <p class="muted">Please fill out this form to update your password.</p>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    <?php if (!empty($general_err)): ?>
        <div class="alert alert-danger"><?php echo $general_err; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="grid" style="gap:14px">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="old_password" class="form-control" required>
            <span class="help-block"><?php echo $old_password_err; ?></span>
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" required>
            <span class="help-block"><?php echo $new_password_err; ?></span>
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
            <span class="help-block"><?php echo $confirm_password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Reset Password">
        </div>
        <p><a href="account.php" class="muted">Back to Account Dashboard</a></p>
    </form>
</div>

<?php include_once "footer.php"; ?>
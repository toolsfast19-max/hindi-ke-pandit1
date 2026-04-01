<?php
require_once 'config/database.php';

$error = '';
$success = '';
$token_valid = false;
$student_id = null;

// Check token
if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    // Find valid token
    $query = "SELECT * FROM password_resets 
              WHERE token = '$token' AND used = 0 AND expiry > NOW()";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $reset = mysqli_fetch_assoc($result);
        $student_id = $reset['student_id'];
        $token_valid = true;
    } else {
        $error = "Invalid or expired reset token.";
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password != $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Update password
        $hashed_password = hashPassword($password);
        $updateQuery = "UPDATE students SET password = '$hashed_password' WHERE id = $student_id";
        
        if (mysqli_query($conn, $updateQuery)) {
            // Mark token as used
            $updateTokenQuery = "UPDATE password_resets SET used = 1 WHERE token = '$token'";
            mysqli_query($conn, $updateTokenQuery);
            
            $success = "Password reset successful! You can now login with your new password.";
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Online Test System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>🔑 Reset Password</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <div class="login-links">
                    <a href="login.php">Click here to login</a>
                </div>
            <?php endif; ?>
            
            <?php if ($token_valid && !$success): ?>
                <form method="POST" action="">
                    <div class="input-group">
                        <label>New Password</label>
                        <input type="password" name="password" required 
                               placeholder="Min 6 characters">
                    </div>
                    
                    <div class="input-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required 
                               placeholder="Re-enter new password">
                    </div>
                    
                    <button type="submit" class="btn-login">Reset Password</button>
                </form>
            <?php elseif (!$token_valid && !$success): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <div class="login-links">
                    <a href="forgot_password.php">Request new reset link</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
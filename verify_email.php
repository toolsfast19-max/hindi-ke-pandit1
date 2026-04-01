<?php
require_once 'config/database.php';

$message = '';
$error = '';

if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    // Find student with this token
    $query = "SELECT id, email FROM students WHERE verification_token = '$token' AND email_verified = 0";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $student = mysqli_fetch_assoc($result);
        
        // Verify the email
        $updateQuery = "UPDATE students SET email_verified = 1, verification_token = NULL WHERE id = " . $student['id'];
        
        if (mysqli_query($conn, $updateQuery)) {
            $message = "Email verified successfully! You can now login.";
        } else {
            $error = "Verification failed. Please try again.";
        }
    } else {
        $error = "Invalid or expired verification token.";
    }
} else {
    $error = "No verification token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Online Test System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>📧 Email Verification</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
                <div class="login-links">
                    <a href="login.php">Click here to login</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <div class="login-links">
                    <a href="register.php">Register again</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
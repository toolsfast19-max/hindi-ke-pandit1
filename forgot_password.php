<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';
$step = 1;
$saved_email = '';

function generateResetCode() {
    return str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}

function sendResetCode($email, $code, $name) {
    $logFile = __DIR__ . '/email_log.txt';
    $logContent = date('Y-m-d H:i:s') . " - To: $email - Reset Code: $code\n";
    file_put_contents($logFile, $logContent, FILE_APPEND);
    return true;
}

if (isset($_POST['reset_password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password != $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $query = "SELECT * FROM students WHERE email = '$email' AND reset_code = '$code' AND reset_code_expiry > NOW()";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $student = mysqli_fetch_assoc($result);
            $update = "UPDATE students SET password = '$new_password', reset_code = NULL, reset_code_expiry = NULL WHERE id = " . $student['id'];
            if (mysqli_query($conn, $update)) {
                $success = "Password reset successful! You can now login.";
                $step = 0;
            } else {
                $error = "Failed to reset password.";
            }
        } else {
            $error = "Invalid or expired reset code!";
        }
    }
}

if (isset($_POST['send_code'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "SELECT id, name FROM students WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $student = mysqli_fetch_assoc($result);
        $code = generateResetCode();
        $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        $update = "UPDATE students SET reset_code = '$code', reset_code_expiry = '$expiry' WHERE id = " . $student['id'];
        mysqli_query($conn, $update);
        
        sendResetCode($email, $code, $student['name']);
        
        $success = "A 6-digit reset code has been sent to your email.";
        $step = 2;
        $saved_email = $email;
    } else {
        $error = "Email address not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Online Test System</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}
        .container{background:#fff;padding:40px;border-radius:10px;box-shadow:0 10px 40px rgba(0,0,0,0.1);width:100%;max-width:450px}
        h1{text-align:center;color:#667eea;margin-bottom:10px}
        p{text-align:center;color:#666;margin-bottom:20px}
        .input-group{margin-bottom:20px;position:relative}
        .input-group label{display:block;margin-bottom:5px;color:#333;font-weight:500}
        .input-group input{width:100%;padding:10px;padding-right:40px;border:1px solid #ddd;border-radius:5px;font-size:16px}
        .eye{position:absolute;right:12px;bottom:12px;cursor:pointer;color:#667eea;font-size:18px}
        button{width:100%;padding:12px;background:#667eea;color:#fff;border:none;border-radius:5px;font-size:16px;cursor:pointer}
        button:hover{background:#5a67d8}
        .error{background:#fed7d7;color:#c53030;padding:10px;border-radius:5px;margin-bottom:20px;text-align:center}
        .success{background:#c6f6d5;color:#276749;padding:10px;border-radius:5px;margin-bottom:20px;text-align:center}
        .links{text-align:center;margin-top:20px;padding-top:20px;border-top:1px solid #e2e8f0}
        .links a{color:#667eea;text-decoration:none;margin:0 10px}
        .code-input{text-align:center;font-size:20px;letter-spacing:5px}
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Forgot Password</h1>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if($step == 1): ?>
            <p>Enter your email to receive a 6-digit reset code.</p>
            <form method="POST">
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <button type="submit" name="send_code">Send Reset Code</button>
            </form>
            
        <?php elseif($step == 2 && $saved_email): ?>
            <p>Code sent to <strong><?php echo htmlspecialchars($saved_email); ?></strong></p>
            <form method="POST">
                <input type="hidden" name="email" value="<?php echo $saved_email; ?>">
                <div class="input-group">
                    <label>6-Digit Code</label>
                    <input type="text" name="code" required placeholder="Enter code" class="code-input" maxlength="6">
                </div>
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="new_password" required placeholder="New password">
                    <span class="eye" onclick="toggle('new_password')">👁️</span>
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm password">
                    <span class="eye" onclick="toggle('confirm_password')">👁️</span>
                </div>
                <button type="submit" name="reset_password">Reset Password</button>
            </form>
            <div class="links">
                <a href="forgot_password.php">Request new code</a>
            </div>
            
        <?php elseif($step == 0): ?>
            <div class="links">
                <a href="login.php">Click here to login</a>
            </div>
        <?php endif; ?>
        
        <div class="links">
            <a href="login.php">← Back to Login</a>
            <span>|</span>
            <a href="register.php">Create Account</a>
        </div>
    </div>
    
    <script>
        function toggle(id) {
            var x = document.getElementById(id);
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }
    </script>
</body>
</html>
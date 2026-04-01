<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_test_system');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['student_id']);
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Function to sanitize input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// SIMPLE PASSWORD FUNCTIONS - NO HASHING
function validatePassword($password) {
    // Password must be at least 6 characters
    if (strlen($password) < 6) {
        return false;
    }
    // Can contain letters, numbers, symbols
    return true;
}

// Generate random 6-digit code
function generateResetCode() {
    return str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}

// Send email with reset code
function sendResetCode($email, $code, $name) {
    $subject = "Password Reset Code - Online Test System";
    $body = "
    <html>
    <head>
        <title>Password Reset Code</title>
    </head>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Password Reset Request</h2>
        <p>Hello <strong>$name</strong>,</p>
        <p>We received a request to reset your password.</p>
        <p>Your password reset code is:</p>
        <h1 style='background: #667eea; color: white; padding: 15px; text-align: center; border-radius: 8px; font-size: 32px; letter-spacing: 5px;'>$code</h1>
        <p>This code will expire in <strong>30 minutes</strong>.</p>
        <p>If you didn't request this, please ignore this email.</p>
        <br>
        <p>Regards,<br>Online Test System Team</p>
    </body>
    </html>
    ";
    
    // Log to file for testing
    $logFile = __DIR__ . '/../email_log.txt';
    $logContent = date('Y-m-d H:i:s') . " - To: $email - Subject: $subject\n";
    $logContent .= "Reset Code: $code\n\n";
    file_put_contents($logFile, $logContent, FILE_APPEND);
    
    // In production, use actual email sending
    return true;
}
?>


// Database configuration
define('DB_HOST', 'localhost');  // ये same रहेगा
define('DB_USER', 'root');       // XAMPP में default username
define('DB_PASS', '');           // XAMPP में default password (blank)
define('DB_NAME', 'online_test_system');  // Database name

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
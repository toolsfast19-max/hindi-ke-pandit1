<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($student_id) || empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM students WHERE student_id = '$student_id'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Student ID already exists!";
        } else {
            $check = mysqli_query($conn, "SELECT id FROM students WHERE email = '$email'");
            if (mysqli_num_rows($check) > 0) {
                $error = "Email already registered!";
            } else {
                $query = "INSERT INTO students (student_id, name, email, password, email_verified) 
                          VALUES ('$student_id', '$name', '$email', '$password', 1)";
                if (mysqli_query($conn, $query)) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Test System</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}
        .register-container{background:#fff;padding:40px;border-radius:10px;box-shadow:0 10px 40px rgba(0,0,0,0.1);width:100%;max-width:450px}
        h1{text-align:center;color:#667eea;margin-bottom:10px}
        h2{text-align:center;color:#333;margin-bottom:30px;font-size:18px}
        .input-group{margin-bottom:20px;position:relative}
        .input-group label{display:block;margin-bottom:5px;color:#333;font-weight:500}
        .input-group input{width:100%;padding:10px;padding-right:40px;border:1px solid #ddd;border-radius:5px;font-size:16px}
        .eye{position:absolute;right:12px;bottom:12px;cursor:pointer;color:#667eea;font-size:18px}
        button{width:100%;padding:12px;background:#667eea;color:#fff;border:none;border-radius:5px;font-size:16px;cursor:pointer}
        button:hover{background:#5a67d8}
        .error{background:#fed7d7;color:#c53030;padding:10px;border-radius:5px;margin-bottom:20px;text-align:center}
        .success{background:#c6f6d5;color:#276749;padding:10px;border-radius:5px;margin-bottom:20px;text-align:center}
        .links{text-align:center;margin-top:20px}
        .links a{color:#667eea;text-decoration:none}
    </style>
</head>
<body>
    <div class="register-container">
        <h1>📚 Create Account</h1>
        <h2>Student Registration</h2>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label>Student ID</label>
                <input type="text" name="student_id" required placeholder="Enter Student ID">
            </div>
            
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" required placeholder="Enter your full name">
            </div>
            
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="Enter your email">
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" id="password" required placeholder="Create password">
                <span class="eye" onclick="toggle('password')">👁️</span>
            </div>
            
            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm password">
                <span class="eye" onclick="toggle('confirm_password')">👁️</span>
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <div class="links">
            <a href="login.php">Already have an account? Login</a>
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
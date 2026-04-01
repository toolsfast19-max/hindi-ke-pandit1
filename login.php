<?php
session_start();
require_once 'config/database.php';

$error = '';

// Agar already login hai to dashboard pe bhej do
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Login form submit hua
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $password = $_POST['password'];
    
    // Student ID ya email se search karo
    $query = "SELECT * FROM students WHERE student_id = '$student_id' OR email = '$student_id'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Password check karo
        if ($password == $user['password']) {
            // Session mein data store karo
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['student_name'] = $user['name'];
            $_SESSION['student_username'] = $user['student_id'];
            $_SESSION['student_email'] = $user['email'];
            $_SESSION['student_roll'] = $user['roll_number'];
            $_SESSION['student_photo'] = $user['photo'];
            $_SESSION['student_class'] = $user['class'];
            
            // Last login update karo
            mysqli_query($conn, "UPDATE students SET last_login = NOW() WHERE id = " . $user['id']);
            
            // Dashboard pe redirect karo
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Invalid password!";
            // Log failed attempt (optional)
            $logFile = '../logs/student_login.log';
            $logContent = date('Y-m-d H:i:s') . " - Failed login for: $student_id\n";
            @file_put_contents($logFile, $logContent, FILE_APPEND);
        }
    } else {
        $error = "Student ID or Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Shrinath Chanakya</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 420px;
            animation: fadeInUp 0.5s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo i {
            font-size: 60px;
            color: #667eea;
        }
        
        .logo h1 {
            font-size: 28px;
            color: #667eea;
            margin-top: 10px;
        }
        
        .logo p {
            font-size: 14px;
            color: #718096;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 18px;
            font-weight: 500;
        }
        
        .input-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 42px;
            color: #a0aec0;
            font-size: 16px;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 42px;
            cursor: pointer;
            color: #a0aec0;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }
        
        .error {
            background: #fed7d7;
            color: #c53030;
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #c53030;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .security-note {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
        }
        
        .security-note i {
            margin-right: 5px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 35px 25px;
            }
            
            .logo i {
                font-size: 50px;
            }
            
            .logo h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <h1>श्रीनाथ चाणक्य</h1>
            <p>Student Portal</p>
        </div>
        <h2>🔐 Student Login</h2>
        
        <?php if($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <div class="input-group">
                <label><i class="fas fa-id-card"></i> Student ID or Email</label>
                <i class="fas fa-user"></i>
                <input type="text" name="student_id" id="student_id" required placeholder="Enter Student ID or Email" autocomplete="off">
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" required placeholder="Enter your password">
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>
            
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Login to Dashboard
            </button>
        </form>
        
        <div class="security-note">
            <i class="fas fa-shield-alt"></i> Secure Student Access Only
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Prevent browser autofill styling
        document.getElementById('student_id').autocomplete = 'off';
        document.getElementById('password').autocomplete = 'off';
    </script>
</body>
</html>
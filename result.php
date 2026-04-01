<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$result_id = $_GET['result_id'];

$result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM results WHERE id = $result_id"));
$test = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tests WHERE id = " . $result['test_id']));
$answers = json_decode($result['answers'], true);
$total_marks = $result['total_questions'] * 4;

$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id = " . $_SESSION['student_id']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result - <?php echo $test['title']; ?></title>
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
            padding: 40px 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .result-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .result-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .result-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .student-info {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 25px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            flex-wrap: wrap;
        }
        
        .student-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }
        
        .student-details h3 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .score-section {
            display: flex;
            justify-content: space-around;
            padding: 30px;
            background: white;
            flex-wrap: wrap;
            gap: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .score-card {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 20px;
            min-width: 150px;
        }
        
        .score-number {
            font-size: 42px;
            font-weight: bold;
            color: #667eea;
        }
        
        .percentage-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: conic-gradient(#48bb78 0deg <?php echo ($result['percentage'] / 100) * 360; ?>deg, #e9ecef <?php echo ($result['percentage'] / 100) * 360; ?>deg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        
        .percentage-inner {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #48bb78;
        }
        
        .answers-section {
            padding: 30px;
        }
        
        .answer-item {
            padding: 18px;
            margin-bottom: 15px;
            border-radius: 15px;
            border-left: 5px solid;
        }
        
        .answer-item.correct {
            border-left-color: #48bb78;
            background: #f0fff4;
        }
        
        .answer-item.incorrect {
            border-left-color: #e53e3e;
            background: #fff5f5;
        }
        
        .answer-question {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .answer-details {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 8px;
        }
        
        .status-correct {
            background: #48bb78;
            color: white;
        }
        
        .status-incorrect {
            background: #e53e3e;
            color: white;
        }
        
        .action-buttons {
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-dashboard {
            background: #667eea;
            color: white;
        }
        
        .btn-print {
            background: #48bb78;
            color: white;
            border: none;
        }
        
        @media print {
            .btn, .action-buttons {
                display: none;
            }
            body {
                background: white;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="result-card">
        <div class="result-header">
            <h1><i class="fas fa-trophy"></i> Test Result</h1>
            <p><?php echo htmlspecialchars($test['title']); ?></p>
        </div>
        
        <div class="student-info">
            <?php 
            $photo = isset($student['photo']) && !empty($student['photo']) ? $student['photo'] : 'default.jpg';
            $photo_path = 'uploads/students/' . $photo;
            if (!file_exists($photo_path)) {
                $photo_path = 'https://ui-avatars.com/api/?background=667eea&color=fff&size=80&name=' . urlencode($student['name']);
            }
            ?>
            <img src="<?php echo $photo_path; ?>" class="student-photo" alt="Student Photo">
            <div class="student-details">
                <h3><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($student['name']); ?></h3>
                <p><i class="fas fa-id-card"></i> Student ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
                <p><i class="fas fa-hashtag"></i> Roll Number: <?php echo isset($student['roll_number']) ? htmlspecialchars($student['roll_number']) : 'Not Assigned'; ?></p>
            </div>
        </div>
        
        <div class="score-section">
            <div class="score-card">
                <div class="score-number"><?php echo $result['score']; ?> / <?php echo $total_marks; ?></div>
                <div class="score-label">Total Score</div>
            </div>
            <div class="score-card">
                <div class="percentage-circle">
                    <div class="percentage-inner"><?php echo round($result['percentage']); ?>%</div>
                </div>
                <div class="score-label">Percentage</div>
            </div>
            <div class="score-card">
                <div class="score-number"><i class="fas fa-calendar-alt"></i></div>
                <div class="score-label"><?php echo date('d M Y', strtotime($result['submitted_at'])); ?></div>
            </div>
        </div>
        
        <div class="answers-section">
            <h3><i class="fas fa-list-ul"></i> Detailed Answers</h3>
            <?php $num = 1; foreach($answers as $ans): ?>
            <div class="answer-item <?php echo $ans['is_correct'] ? 'correct' : 'incorrect'; ?>">
                <div class="answer-question">
                    <i class="fas fa-question-circle"></i> Q<?php echo $num; ?>: <?php echo htmlspecialchars($ans['question']); ?>
                </div>
                <div class="answer-details">
                    <strong>Your Answer:</strong> <?php echo htmlspecialchars($ans['user_text']); ?><br>
                    <strong>Correct Answer:</strong> <?php echo htmlspecialchars($ans['correct_text']); ?>
                </div>
                <div class="status-badge <?php echo $ans['is_correct'] ? 'status-correct' : 'status-incorrect'; ?>">
                    <i class="fas <?php echo $ans['is_correct'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <?php echo $ans['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                </div>
            </div>
            <?php $num++; endforeach; ?>
        </div>
        
        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-dashboard">
                <i class="fas fa-tachometer-alt"></i> Back to Dashboard
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> Print Result
            </button>
        </div>
    </div>
</div>
</body>
</html>
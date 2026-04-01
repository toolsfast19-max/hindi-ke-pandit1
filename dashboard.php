<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$query = "SELECT * FROM students WHERE id = $student_id";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

$student_name = $student['name'];
$student_username = $student['student_id'];
$student_roll = isset($student['roll_number']) ? $student['roll_number'] : 'Not Assigned';
$student_photo = isset($student['photo']) ? $student['photo'] : 'default.jpg';
$student_class = isset($student['class']) ? $student['class'] : 'Not Assigned';

// Fetch tests
if ($student_class != 'Not Assigned') {
    $tests_query = "SELECT * FROM tests WHERE class_for = '$student_class' OR class_for = 'All' ORDER BY created_at DESC";
} else {
    $tests_query = "SELECT * FROM tests ORDER BY created_at DESC";
}
$tests_result = mysqli_query($conn, $tests_query);

// Fetch results
$results_query = "SELECT r.*, t.title FROM results r 
                  JOIN tests t ON r.test_id = t.id 
                  WHERE r.student_id = $student_id 
                  ORDER BY r.submitted_at DESC";
$results_result = mysqli_query($conn, $results_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online Test System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f7fafc; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .student-info { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .student-photo { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #667eea; background: #e2e8f0; }
        .student-details h1 { color: #667eea; font-size: 24px; margin-bottom: 5px; }
        .student-details p { color: #718096; margin-top: 5px; }
        .btn-logout { padding: 10px 20px; background: #e53e3e; color: white; text-decoration: none; border-radius: 5px; }
        .section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section h2 { margin-bottom: 20px; color: #333; font-size: 20px; border-left: 4px solid #667eea; padding-left: 12px; }
        .tests-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .test-card { border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; transition: transform 0.3s, box-shadow 0.3s; background: white; }
        .test-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .test-card h3 { color: #667eea; margin-bottom: 10px; font-size: 18px; }
        .test-card p { color: #4a5568; margin-bottom: 15px; }
        .test-info { margin: 15px 0; display: flex; gap: 15px; font-size: 14px; color: #718096; flex-wrap: wrap; }
        .test-badge { background: #48bb78; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; display: inline-block; }
        .btn-start { width: 100%; padding: 10px; background: #48bb78; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background 0.3s; }
        .btn-start:hover { background: #38a169; }
        
        /* Fullscreen Modal */
        .fullscreen-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .fullscreen-modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .fullscreen-modal h2 { color: #667eea; margin-bottom: 15px; font-size: 28px; }
        .fullscreen-modal p { color: #666; margin-bottom: 25px; line-height: 1.6; }
        .fullscreen-modal .warning-text { color: #e53e3e; font-weight: bold; background: #fff5f5; padding: 10px; border-radius: 10px; margin: 15px 0; }
        .fullscreen-modal button {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            cursor: pointer;
            margin: 0 10px;
            transition: all 0.3s;
        }
        .fullscreen-modal button:hover { transform: translateY(-2px); }
        .btn-fullscreen-allow { background: #48bb78; color: white; }
        .btn-fullscreen-cancel { background: #e53e3e; color: white; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f7fafc; font-weight: 600; color: #4a5568; }
        .btn-view { padding: 5px 10px; background: #4299e1; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .no-results { text-align: center; color: #718096; padding: 40px; }
        .class-badge { background: #667eea; color: white; padding: 2px 10px; border-radius: 20px; font-size: 12px; display: inline-block; margin-top: 5px; }
        
        @media (max-width: 768px) {
            .header { flex-direction: column; text-align: center; }
            .student-info { flex-direction: column; text-align: center; }
            .tests-grid { grid-template-columns: 1fr; }
            .fullscreen-modal-content { margin: 20px; padding: 30px; }
        }
    </style>
</head>
<body>

<!-- Fullscreen Permission Modal -->
<div id="fullscreenModal" class="fullscreen-modal">
    <div class="fullscreen-modal-content">
        <h2>🔒 Fullscreen Mode Required</h2>
        <p>For exam security, you must enable fullscreen mode before starting the test.</p>
        <div class="warning-text">
            ⚠️ During the exam:<br>
            • You cannot switch tabs or windows<br>
            • Right click, copy, paste are disabled<br>
            • 3 violations will auto-submit your exam
        </div>
        <div style="margin-top: 20px;">
            <button id="allowFullscreenBtn" class="btn-fullscreen-allow">✅ Allow Fullscreen</button>
            <button id="cancelFullscreenBtn" class="btn-fullscreen-cancel">❌ Cancel</button>
        </div>
    </div>
</div>

<div class="container">
    <div class="header">
        <div class="student-info">
            <?php 
            $photo_name = isset($student['photo']) && !empty($student['photo']) ? $student['photo'] : 'default.jpg';
            $photo_path = 'uploads/students/' . $photo_name;
            if (!file_exists($photo_path)) {
                $photo_path = 'https://ui-avatars.com/api/?background=667eea&color=fff&size=80&name=' . urlencode($student['name']);
            }
            ?>
            <img src="<?php echo $photo_path; ?>" class="student-photo" alt="Student Photo">
            <div class="student-details">
                <h1>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h1>
                <p>📚 Student ID: <?php echo htmlspecialchars($student_username); ?></p>
                <p>🎓 Roll Number: <?php echo htmlspecialchars($student_roll); ?></p>
                <p>📖 Class: <strong class="class-badge"><?php echo htmlspecialchars($student_class); ?></strong></p>
            </div>
        </div>
        <a href="logout.php" class="btn-logout">🚪 Logout</a>
    </div>
    
    <div class="section">
        <h2>📋 Available Tests for <?php echo htmlspecialchars($student_class); ?> Class</h2>
        <div class="tests-grid">
            <?php if ($tests_result && mysqli_num_rows($tests_result) > 0): ?>
                <?php while ($test = mysqli_fetch_assoc($tests_result)): ?>
                    <div class="test-card">
                        <h3><?php echo htmlspecialchars($test['title']); ?></h3>
                        <p><?php echo htmlspecialchars($test['description']); ?></p>
                        <div class="test-info">
                            <span>⏱️ <?php echo $test['duration']; ?> minutes</span>
                            <span>📝 <?php echo $test['total_questions']; ?> questions</span>
                            <span>🎯 <?php echo $test['marks_per_question']; ?> marks each</span>
                        </div>
                        <?php if($test['class_for'] == 'All'): ?>
                            <span class="test-badge">📖 Common for All Classes</span>
                        <?php else: ?>
                            <span class="test-badge">📘 For <?php echo $test['class_for']; ?> Class Only</span>
                        <?php endif; ?>
                        <button onclick="requestFullscreenAndStart(<?php echo $test['id']; ?>)" class="btn-start" style="margin-top: 10px;">
                            🚀 Start Test
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">No tests available for your class at the moment.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="section">
        <h2>📊 Previous Results</h2>
        <?php if ($results_result && mysqli_num_rows($results_result) > 0): ?>
            <div style="overflow-x: auto;">
                <button onclick="window.print()" style="margin-bottom: 15px; padding: 8px 15px; background: #48bb78; color: white; border: none; border-radius: 5px; cursor: pointer;">🖨️ Print Result</button>
                <table>
                    <thead>
                        <tr><th>Test Name</th><th>Score</th><th>Percentage</th><th>Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($result = mysqli_fetch_assoc($results_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['title']); ?></td>
                                <td><?php echo $result['score'] . '/' . ($result['total_questions'] * 4); ?></td>
                                <td><?php echo round($result['percentage']); ?>%</td>
                                <td><?php echo date('d M Y', strtotime($result['submitted_at'])); ?></td>
                                <td><button onclick="viewResult(<?php echo $result['id']; ?>)" class="btn-view">View Details</button></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-results">No tests taken yet. Start a test above!</div>
        <?php endif; ?>
    </div>
</div>

<script>
    let pendingTestId = null;
    const modal = document.getElementById('fullscreenModal');
    const allowBtn = document.getElementById('allowFullscreenBtn');
    const cancelBtn = document.getElementById('cancelFullscreenBtn');
    
    function requestFullscreenAndStart(testId) {
        pendingTestId = testId;
        modal.style.display = 'flex';
    }
    
    function enterFullscreen() {
        const elem = document.documentElement;
        if (elem.requestFullscreen) elem.requestFullscreen();
        else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
        else if (elem.msRequestFullscreen) elem.msRequestFullscreen();
    }
    
    function isFullscreen() {
        return document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
    }
    
    allowBtn.onclick = function() {
        modal.style.display = 'none';
        enterFullscreen();
        
        let checkInterval = setInterval(function() {
            if (isFullscreen()) {
                clearInterval(checkInterval);
                if (pendingTestId) {
                    window.location.href = 'exam.php?test_id=' + pendingTestId;
                    pendingTestId = null;
                }
            }
        }, 100);
        
        setTimeout(function() {
            clearInterval(checkInterval);
            if (!isFullscreen()) {
                alert("⚠️ Fullscreen mode is required to take the exam. Please allow fullscreen.");
                if (pendingTestId) {
                    requestFullscreenAndStart(pendingTestId);
                }
            }
        }, 3000);
    };
    
    cancelBtn.onclick = function() {
        modal.style.display = 'none';
        pendingTestId = null;
        alert("Exam cancelled. Fullscreen mode is required.");
    };
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            pendingTestId = null;
        }
    };
    
    function viewResult(resultId) {
        window.location.href = 'result.php?result_id=' + resultId;
    }
</script>
</body>
</html>
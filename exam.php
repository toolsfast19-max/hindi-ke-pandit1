<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$test_id = isset($_GET['test_id']) ? intval($_GET['test_id']) : 0;
if ($test_id == 0) {
    die("Invalid test ID");
}

// Get test details
$test_query = "SELECT * FROM tests WHERE id = $test_id";
$test_result = mysqli_query($conn, $test_query);
$test = mysqli_fetch_assoc($test_result);

if (!$test) {
    die("Test not found");
}

// Get questions
$questions_query = "SELECT * FROM questions WHERE test_id = $test_id";
$questions_result = mysqli_query($conn, $questions_query);
$questions = mysqli_fetch_all($questions_result, MYSQLI_ASSOC);
shuffle($questions);

$total_questions = count($questions);
$marks_per_question = isset($test['marks_per_question']) ? $test['marks_per_question'] : 4;
$total_marks = $total_questions * $marks_per_question;
$_SESSION['violation_count'] = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($test['title']); ?> - Secure Exam</title>
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
        }
        
        /* Fullscreen Warning */
        .fullscreen-warning {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            text-align: center;
        }
        .fullscreen-warning h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #ff6b6b;
        }
        .fullscreen-warning p {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .fullscreen-warning button {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 30px;
            transition: transform 0.3s;
        }
        .fullscreen-warning button:hover {
            transform: translateY(-2px);
        }
        
        /* Warning Popup */
        .warning-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #ff4757, #e84118);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            z-index: 10001;
            display: none;
            font-weight: 600;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
            align-items: center;
            gap: 10px;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Main Container */
        .exam-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .exam-header {
            background: white;
            padding: 20px 25px;
            border-radius: 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .test-info h2 {
            color: #667eea;
            font-size: 22px;
            margin-bottom: 5px;
        }
        .test-info p {
            color: #718096;
            font-size: 13px;
        }
        .timer-box {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            padding: 15px 25px;
            border-radius: 15px;
            text-align: center;
            color: white;
        }
        .timer-box .time {
            font-size: 32px;
            font-weight: bold;
            font-family: monospace;
        }
        .timer-box .label {
            font-size: 10px;
            opacity: 0.9;
        }
        .violation-box {
            background: linear-gradient(135deg, #e53e3e, #c53030);
            padding: 10px 20px;
            border-radius: 15px;
            text-align: center;
            color: white;
            margin-top: 8px;
        }
        .violation-box .time {
            font-size: 20px;
            font-weight: bold;
        }
        
        /* Info Bar */
        .info-bar {
            background: white;
            padding: 12px 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .info-bar span {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #4a5568;
        }
        .warning-text {
            color: #e53e3e !important;
            font-weight: 600;
        }
        
        /* Question Card */
        .question-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .question-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .question-number {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .question-text {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .option {
            background: #f7fafc;
            padding: 12px 18px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
        }
        .option:hover {
            background: #edf2f7;
            transform: translateX(5px);
        }
        .option.selected {
            background: linear-gradient(135deg, #48bb78, #38a169);
            border-color: #48bb78;
            color: white;
        }
        .option input {
            margin-right: 12px;
            transform: scale(1.1);
            cursor: pointer;
            accent-color: #48bb78;
        }
        .option label {
            cursor: pointer;
            font-size: 15px;
            flex: 1;
        }
        .option.selected label {
            color: white;
        }
        
        /* Submit Button */
        .submit-btn {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            border: none;
            padding: 16px 30px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(72,187,120,0.3);
        }
        
        @media (max-width: 768px) {
            .exam-header {
                flex-direction: column;
                text-align: center;
            }
            .question-text {
                font-size: 16px;
            }
            .option label {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div id="fullscreenWarning" class="fullscreen-warning" style="display: flex;">
    <i class="fas fa-expand" style="font-size: 60px; margin-bottom: 20px; color: #ff6b6b;"></i>
    <h2>🔒 Fullscreen Mode Required</h2>
    <p>For exam security, you must enable fullscreen mode before starting the test.</p>
    <p><i class="fas fa-ban"></i> <strong>IMPORTANT:</strong> You CANNOT exit fullscreen during the exam!</p>
    <p><i class="fas fa-exclamation-triangle"></i> Exiting fullscreen will automatically submit your exam!</p>
    <button id="fullscreenBtn"><i class="fas fa-expand"></i> Enter Fullscreen</button>
</div>

<div id="warningPopup" class="warning-popup">
    <i class="fas fa-exclamation-triangle"></i>
    <span id="warningMessage">Warning!</span>
</div>

<div class="exam-container" style="display: none;" id="examContainer">
    <div class="exam-header">
        <div class="test-info">
            <h2><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($test['title']); ?></h2>
            <p><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($_SESSION['student_name']); ?> | ID: <?php echo htmlspecialchars($_SESSION['student_username']); ?></p>
            <p><i class="fas fa-question-circle"></i> <?php echo $total_questions; ?> Questions | <i class="fas fa-star"></i> <?php echo $marks_per_question; ?> marks each | <i class="fas fa-trophy"></i> Total: <?php echo $total_marks; ?> marks</p>
        </div>
        <div>
            <div class="timer-box">
                <div class="time" id="timer"><?php echo $test['duration'] * 60; ?></div>
                <div class="label">seconds left</div>
            </div>
            <div class="violation-box">
                <div class="label"><i class="fas fa-shield-alt"></i> Violations</div>
                <div class="time" id="violationCount">0</div>
                <div class="label">/ 3 max</div>
            </div>
        </div>
    </div>
    
    <div class="info-bar">
        <span><i class="fas fa-shield-alt" style="color: #48bb78;"></i> Secure Exam Mode Active</span>
        <span class="warning-text"><i class="fas fa-exclamation-triangle"></i> DO NOT switch tabs, right click, or copy text!</span>
        <span><i class="fas fa-gavel"></i> <strong>Exiting fullscreen = Auto Submit!</strong></span>
    </div>

    <form method="POST" action="submit_exam.php" id="examForm">
        <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
        
        <?php $num = 1; foreach($questions as $q): 
            $options = ['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']];
        ?>
        <div class="question-card" data-qid="<?php echo $q['id']; ?>">
            <div class="question-number">Question <?php echo $num; ?> of <?php echo $total_questions; ?></div>
            <div class="question-text"><?php echo $num . '. ' . htmlspecialchars($q['question_text']); ?></div>
            <div class="options">
                <?php foreach($options as $letter => $text): ?>
                <div class="option" data-letter="<?php echo $letter; ?>" onclick="selectOption(this, <?php echo $q['id']; ?>, '<?php echo $letter; ?>')">
                    <input type="radio" name="q<?php echo $q['id']; ?>" value="<?php echo $letter; ?>" id="q<?php echo $q['id']; ?>_<?php echo $letter; ?>">
                    <label for="q<?php echo $q['id']; ?>_<?php echo $letter; ?>"><?php echo $letter; ?>. <?php echo htmlspecialchars($text); ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php $num++; endforeach; ?>
        
        <button type="submit" class="submit-btn" onclick="return confirmSubmit()">
            <i class="fas fa-paper-plane"></i> SUBMIT TEST
        </button>
    </form>
</div>

<script>
    let violationCount = 0;
    const MAX_VIOLATIONS = 3;
    let examStarted = false;
    let examSubmitted = false;
    
    // Fullscreen function for both localhost and live server
    function toggleFullscreen() {
        let elem = document.documentElement;
        
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
            // Enter fullscreen
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        } else {
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }
    
    function isFullscreen() {
        return document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
    }
    
    // Button click handler
    document.getElementById('fullscreenBtn').onclick = function() {
        toggleFullscreen();
    };
    
    function showWarning(message) {
        let popup = document.getElementById('warningPopup');
        let msgSpan = document.getElementById('warningMessage');
        msgSpan.innerHTML = message + ' (' + violationCount + '/' + MAX_VIOLATIONS + ')';
        popup.style.display = 'flex';
        setTimeout(() => { popup.style.display = 'none'; }, 3000);
    }
    
    function handleViolation(violationType) {
        if (examSubmitted) return;
        violationCount++;
        document.getElementById('violationCount').innerText = violationCount;
        showWarning(violationType);
        
        if (violationCount >= MAX_VIOLATIONS) {
            examSubmitted = true;
            alert('❌ MAXIMUM VIOLATIONS REACHED!\n\nYou have violated exam rules 3 times.\nYour exam will be submitted automatically.');
            document.getElementById('examForm').submit();
        }
    }
    
    // Check fullscreen status and start exam
    function checkFullscreenAndStart() {
        if (isFullscreen()) {
            document.getElementById('fullscreenWarning').style.display = 'none';
            document.getElementById('examContainer').style.display = 'block';
            if (!examStarted) {
                examStarted = true;
                startTimer();
            }
        }
    }
    
    // Monitor fullscreen changes
    document.addEventListener('fullscreenchange', checkFullscreenAndStart);
    document.addEventListener('webkitfullscreenchange', checkFullscreenAndStart);
    document.addEventListener('msfullscreenchange', checkFullscreenAndStart);
    
    // Also check periodically (for browsers that don't fire events properly)
    let checkInterval = setInterval(function() {
        if (isFullscreen()) {
            clearInterval(checkInterval);
            checkFullscreenAndStart();
        }
    }, 500);
    
    // FULLSCREEN EXIT - AUTO SUBMIT
    document.addEventListener('fullscreenchange', function() {
        if (!isFullscreen() && examStarted && !examSubmitted) {
            examSubmitted = true;
            alert('⚠️ YOU EXITED FULLSCREEN MODE!\n\nYour exam will be submitted automatically.');
            document.getElementById('examForm').submit();
        }
    });
    
    document.addEventListener('webkitfullscreenchange', function() {
        if (!isFullscreen() && examStarted && !examSubmitted) {
            examSubmitted = true;
            alert('⚠️ YOU EXITED FULLSCREEN MODE!\n\nYour exam will be submitted automatically.');
            document.getElementById('examForm').submit();
        }
    });
    
    function startTimer() {
        let timeLeft = <?php echo $test['duration'] * 60; ?>;
        let timerSpan = document.getElementById('timer');
        let timerInterval = setInterval(() => {
            if (examSubmitted) {
                clearInterval(timerInterval);
                return;
            }
            timeLeft--;
            let mins = Math.floor(timeLeft / 60);
            let secs = timeLeft % 60;
            timerSpan.innerHTML = mins + ":" + (secs < 10 ? "0" + secs : secs);
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                alert("⏰ Time's up! Submitting your exam...");
                document.getElementById('examForm').submit();
            }
        }, 1000);
    }
    
    // Select option function
    function selectOption(div, qid, value) {
        if (examSubmitted) return;
        
        let card = div.closest('.question-card');
        let options = card.querySelectorAll('.option');
        options.forEach(opt => opt.classList.remove('selected'));
        div.classList.add('selected');
        
        let radio = div.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }
    }
    
    // Confirm submit
    function confirmSubmit() {
        if (examSubmitted) return false;
        
        let radios = document.querySelectorAll('input[type="radio"]');
        let answered = 0;
        radios.forEach(radio => { if (radio.checked) answered++; });
        
        if (answered < <?php echo $total_questions; ?>) {
            alert('⚠️ You have ' + (<?php echo $total_questions; ?> - answered) + ' unanswered questions!\n\nPlease answer all questions before submitting.');
            return false;
        }
        
        if (violationCount > 0) {
            return confirm('⚠️ You had ' + violationCount + ' rule violations during the exam.\n\nThis has been recorded.\n\nAre you sure you want to submit?');
        }
        
        return confirm('Are you sure you want to submit your test?');
    }
    
    // ========== ANTI-CHEATING MEASURES ==========
    
    // 1. Disable Right Click
    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        handleViolation('Right click');
        return false;
    });
    
    // 2. Disable Copy/Cut/Paste
    document.addEventListener('copy', (e) => { e.preventDefault(); handleViolation('Copy attempt'); return false; });
    document.addEventListener('cut', (e) => { e.preventDefault(); handleViolation('Cut attempt'); return false; });
    document.addEventListener('paste', (e) => { e.preventDefault(); handleViolation('Paste attempt'); return false; });
    
    // 3. Disable Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        if (examSubmitted) return;
        
        if (e.key === 'F12') {
            e.preventDefault();
            handleViolation('Developer tools');
            return false;
        }
        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) {
            e.preventDefault();
            handleViolation('Developer tools');
            return false;
        }
        if (e.ctrlKey && e.key === 'u') {
            e.preventDefault();
            handleViolation('View source');
            return false;
        }
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            handleViolation('Print attempt');
            return false;
        }
        if (e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 'x')) {
            e.preventDefault();
            handleViolation('Copy/Paste');
            return false;
        }
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            handleViolation('Search');
            return false;
        }
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            handleViolation('Save page');
            return false;
        }
    });
    
    // 4. Tab Switch Detection
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && !examSubmitted && examStarted) {
            handleViolation('Tab switch');
        }
    });
    
    // 5. Window Blur Detection
    window.addEventListener('blur', () => {
        if (!examSubmitted && examStarted) {
            handleViolation('Window focus lost');
        }
    });
    
    // 6. Disable Back Button
    history.pushState(null, null, location.href);
    window.addEventListener('popstate', () => {
        history.pushState(null, null, location.href);
        handleViolation('Back button');
    });
    
    // 7. Disable dragging
    document.addEventListener('dragstart', (e) => {
        e.preventDefault();
        handleViolation('Drag attempt');
        return false;
    });
    
    // 8. Prevent text selection (additional)
    document.addEventListener('selectstart', (e) => {
        e.preventDefault();
        return false;
    });
</script>
</body>
</html>
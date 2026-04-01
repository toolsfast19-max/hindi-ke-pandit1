<?php
require_once 'admin/db_config.php';

$test_id = isset($_GET['test_id']) ? intval($_GET['test_id']) : 0;

if (!$test_id) {
    die("Invalid test ID");
}

// Get test details
$stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
$stmt->execute([$test_id]);
$test = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$test) {
    die("Test not found");
}

// Get questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE test_id = ?");
$stmt->execute([$test_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($test['test_name']); ?> - Take Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; }
        .question-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .question-text { font-size: 18px; font-weight: bold; margin-bottom: 15px; }
        .option { margin: 10px 0; }
        .option input { margin-right: 10px; }
        .btn-submit {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .timer {
            background: #ff9800;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo htmlspecialchars($test['test_name']); ?></h1>
    <div class="timer">Time Remaining: <span id="timer"><?php echo $test['duration'] * 60; ?></span> seconds</div>
    
    <form method="post" action="submit_test.php">
        <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
        
        <?php foreach ($questions as $index => $q): ?>
        <div class="question-card">
            <div class="question-text">
                <?php echo ($index + 1) . '. ' . htmlspecialchars($q['question_text']); ?>
            </div>
            <div class="option">
                <input type="radio" name="q<?php echo $q['id']; ?>" value="A"> 
                <?php echo htmlspecialchars($q['option_a']); ?>
            </div>
            <div class="option">
                <input type="radio" name="q<?php echo $q['id']; ?>" value="B"> 
                <?php echo htmlspecialchars($q['option_b']); ?>
            </div>
            <div class="option">
                <input type="radio" name="q<?php echo $q['id']; ?>" value="C"> 
                <?php echo htmlspecialchars($q['option_c']); ?>
            </div>
            <div class="option">
                <input type="radio" name="q<?php echo $q['id']; ?>" value="D"> 
                <?php echo htmlspecialchars($q['option_d']); ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <button type="submit" class="btn-submit">Submit Test</button>
    </form>
</div>

<script>
    let timeLeft = <?php echo $test['duration'] * 60; ?>;
    const timerElement = document.getElementById('timer');
    
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = minutes + ":" + (seconds < 10 ? "0" + seconds : seconds);
        
        if (timeLeft <= 0) {
            document.querySelector('form').submit();
        }
        timeLeft--;
    }
    
    setInterval(updateTimer, 1000);
</script>
</body>
</html>
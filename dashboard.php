<?php
require_once 'admin/db_config.php';

// Fetch all tests
$stmt = $pdo->query("SELECT * FROM tests ORDER BY created_date DESC");
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        .test-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .test-title { font-size: 20px; font-weight: bold; color: #2196F3; margin-bottom: 10px; }
        .test-info { margin: 10px 0; color: #666; }
        .btn-start {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
        .no-tests { text-align: center; padding: 50px; background: white; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1>📚 Available Tests</h1>
    
    <?php if (count($tests) > 0): ?>
        <?php foreach ($tests as $test): ?>
        <div class="test-card">
            <div class="test-title"><?php echo htmlspecialchars($test['test_name']); ?></div>
            <div class="test-info">
                📝 <?php echo htmlspecialchars($test['description']); ?><br>
                ⏱️ Duration: <?php echo $test['duration']; ?> minutes<br>
                ❓ Questions: <?php echo $test['total_questions']; ?><br>
                📅 Created: <?php echo date('d M Y', strtotime($test['created_date'])); ?>
            </div>
            <a href="take_test.php?test_id=<?php echo $test['id']; ?>" class="btn-start">▶ Start Test</a>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-tests">
            <p>No tests available yet.</p>
            <p>Admin will add tests soon.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
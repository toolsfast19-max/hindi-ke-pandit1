<?php
require_once '../config/database.php';

$query = "SELECT * FROM tests ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - View Tests</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fafc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #667eea; margin-bottom: 20px; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #f7fafc;
            font-weight: 600;
        }
        .btn-add {
            display: inline-block;
            padding: 10px 20px;
            background: #48bb78;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-delete {
            padding: 5px 10px;
            background: #e53e3e;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }
        .btn-questions {
            padding: 5px 10px;
            background: #4299e1;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📋 Manage Tests</h1>
    <a href="upload_test.php" class="btn-add">+ Add New Test (Upload PDF/TXT)</a>
    
     <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Test Name</th>
                <th>Description</th>
                <th>Duration</th>
                <th>Questions</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($test = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $test['id']; ?></td>
                <td><?php echo htmlspecialchars($test['title']); ?></td>
                <td><?php echo htmlspecialchars($test['description']); ?></td>
                <td><?php echo $test['duration']; ?> min</td>
                <td><?php echo $test['total_questions']; ?></td>
                <td><?php echo date('d M Y', strtotime($test['created_at'])); ?></td>
                <td>
                    <a href="view_questions.php?test_id=<?php echo $test['id']; ?>" class="btn-questions">Questions</a>
                    <a href="delete_test.php?id=<?php echo $test['id']; ?>" class="btn-delete" onclick="return confirm('Delete this test?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
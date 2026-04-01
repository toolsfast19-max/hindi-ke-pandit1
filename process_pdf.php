<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

require_once '../config/database.php';  // Your existing database connection

function send_response($success, $message, $data = []) {
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Please use POST method');
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    send_response(false, 'File upload failed');
}

$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['file']['name']));
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
    send_response(false, 'Failed to save file');
}

$content = file_get_contents($filePath);
if (empty($content)) {
    unlink($filePath);
    send_response(false, 'File is empty');
}

// Parse MCQs
$mcqs = [];
$lines = explode("\n", $content);
$current_question = null;
$options = [];
$current_answer = null;

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    if (preg_match('/^(\d+)[\.\)]\s*(.+)/', $line, $matches)) {
        if ($current_question && count($options) === 4) {
            $mcqs[] = [
                'question' => $current_question,
                'options' => $options,
                'answer' => $current_answer
            ];
        }
        $current_question = trim($matches[2]);
        $options = [];
        $current_answer = null;
    }
    elseif (preg_match('/^([A-D])[\.\)]\s*(.+)/i', $line, $matches)) {
        $letter = strtoupper($matches[1]);
        $options[$letter] = trim($matches[2]);
    }
    elseif (preg_match('/answer\s*[:]\s*([A-D])/i', $line, $matches) || 
            preg_match('/ans\s*[:]\s*([A-D])/i', $line, $matches)) {
        $current_answer = strtoupper($matches[1]);
    }
}

if ($current_question && count($options) === 4) {
    $mcqs[] = [
        'question' => $current_question,
        'options' => $options,
        'answer' => $current_answer
    ];
}

if (empty($mcqs)) {
    unlink($filePath);
    send_response(false, 'No MCQs found. Use format:
1. Question
A. Option
B. Option
C. Option
D. Option
Answer: A');
}

$testName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
$description = "Test from: " . $testName;
$duration = max(10, count($mcqs));

try {
    // Insert test into existing tests table
    $query = "INSERT INTO tests (title, description, duration, total_questions, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssii", $testName, $description, $duration, count($mcqs));
    mysqli_stmt_execute($stmt);
    $testId = mysqli_insert_id($conn);
    
    $questionCount = 0;
    foreach ($mcqs as $mcq) {
        $query = "INSERT INTO questions (test_id, question_text, option_a, option_b, option_c, option_d, correct_answer, marks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        $marks = 1;
        mysqli_stmt_bind_param($stmt, "issssssi", $testId, $mcq['question'], $mcq['options']['A'], $mcq['options']['B'], $mcq['options']['C'], $mcq['options']['D'], $mcq['answer'], $marks);
        mysqli_stmt_execute($stmt);
        $questionCount++;
    }
    
    unlink($filePath);
    
    send_response(true, 'Test created successfully!', [
        'test_id' => $testId,
        'test_name' => $testName,
        'questions_count' => $questionCount
    ]);
    
} catch (Exception $e) {
    unlink($filePath);
    send_response(false, 'Database error: ' . $e->getMessage());
}
?>
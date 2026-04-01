<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: dashboard.php');
    exit;
}

$test_id = $_POST['test_id'];
$student_id = $_SESSION['student_id'];

// Get all questions
$result = mysqli_query($conn, "SELECT * FROM questions WHERE test_id = $test_id");
$questions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $questions[] = $row;
}

$score = 0;
$answers = [];

foreach ($questions as $q) {
    $qid = $q['id'];
    $user_answer = isset($_POST['q' . $qid]) ? $_POST['q' . $qid] : '';
    $correct_answer = $q['correct_answer'];
    $is_correct = ($user_answer == $correct_answer);
    
    if ($is_correct) {
        $score = $score + 4;  // 4 marks per question
    }
    
    // Get text for display
    $user_text = "Not answered";
    if ($user_answer == "A") $user_text = $q['option_a'];
    if ($user_answer == "B") $user_text = $q['option_b'];
    if ($user_answer == "C") $user_text = $q['option_c'];
    if ($user_answer == "D") $user_text = $q['option_d'];
    
    $correct_text = "";
    if ($correct_answer == "A") $correct_text = $q['option_a'];
    if ($correct_answer == "B") $correct_text = $q['option_b'];
    if ($correct_answer == "C") $correct_text = $q['option_c'];
    if ($correct_answer == "D") $correct_text = $q['option_d'];
    
    $answers[] = [
        'question' => $q['question_text'],
        'user' => $user_answer,
        'user_text' => $user_text,
        'correct' => $correct_answer,
        'correct_text' => $correct_text,
        'is_correct' => $is_correct
    ];
}

$total_marks = count($questions) * 4;
$percentage = ($total_marks > 0) ? ($score / $total_marks) * 100 : 0;

$answers_json = json_encode($answers);
$sql = "INSERT INTO results (student_id, test_id, score, total_questions, percentage, answers, submitted_at) 
        VALUES ($student_id, $test_id, $score, " . count($questions) . ", $percentage, '$answers_json', NOW())";

mysqli_query($conn, $sql);
$result_id = mysqli_insert_id($conn);

header("Location: result.php?result_id=$result_id");
exit;
?>
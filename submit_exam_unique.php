<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $test_id = intval($_POST['test_id']);
    $exam_session = mysqli_real_escape_string($conn, $_POST['exam_session']);
    $user_answers = $_POST['answers'] ?? [];
    
    // Get the student's randomized questions
    $query = "SELECT randomized_questions FROM student_exam_data 
              WHERE exam_session = '$exam_session' 
              AND student_id = $student_id 
              AND test_id = $test_id 
              AND status = 'active'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 0) {
        die("Invalid exam session!");
    }
    
    $row = mysqli_fetch_assoc($result);
    $questions = json_decode($row['randomized_questions'], true);
    
    // Calculate score
    $score = 0;
    $total_questions = count($questions);
    $total_marks = $total_questions * 4;
    $detailed_answers = [];
    
    foreach ($questions as $index => $q) {
        $qid = $q['original_id'];
        $user_answer = isset($user_answers[$qid]) ? $user_answers[$qid] : 'Not answered';
        $is_correct = ($user_answer == $q['correct_answer']);
        
        if ($is_correct) {
            $score += 4;
        }
        
        $detailed_answers[$qid] = [
            'question_number' => $index + 1,
            'question_text' => $q['question_text'],
            'user_answer' => $user_answer,
            'user_answer_text' => $user_answer != 'Not answered' ? ($q['options'][$user_answer] ?? 'Not answered') : 'Not answered',
            'correct_answer' => $q['correct_answer'],
            'correct_answer_text' => $q['options'][$q['correct_answer']],
            'is_correct' => $is_correct,
            'marks' => 4
        ];
    }
    
    $percentage = ($score / $total_marks) * 100;
    
    // Save results
    $answers_json = json_encode($detailed_answers);
    $insert_query = "INSERT INTO results (student_id, test_id, score, total_questions, percentage, answers) 
                     VALUES ($student_id, $test_id, $score, $total_questions, $percentage, '$answers_json')";
    
    if (mysqli_query($conn, $insert_query)) {
        // Mark exam session as completed
        $update_query = "UPDATE student_exam_data SET status = 'completed', end_time = NOW() 
                         WHERE exam_session = '$exam_session'";
        mysqli_query($conn, $update_query);
        
        $result_id = mysqli_insert_id($conn);
        
        // DIRECT REDIRECT - NO DEBUG OUTPUT
        header("Location: result.php?result_id=$result_id");
        exit();
    } else {
        die("Error saving results: " . mysqli_error($conn));
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>
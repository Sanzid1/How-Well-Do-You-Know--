<?php
session_start();
require_once '../config.php';

// Only admin can approve
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../frontend/index.php");
    exit;
}

// Check if quiz_id is provided
if (!isset($_GET['quiz_id'])) {
    $_SESSION['error'] = "No quiz selected.";
    header("Location: ../../frontend/admin/pending_quizzes.php");
    exit;
}

$quiz_id = (int) $_GET['quiz_id'];

try {
    // Update status to 'approved'
    $stmt = $pdo->prepare("UPDATE quizzes SET status = 'approved' WHERE quiz_id = :qid");
    $stmt->execute([':qid' => $quiz_id]);

    $_SESSION['success'] = "Quiz #{$quiz_id} approved successfully.";
    header("Location: ../../frontend/admin/pending_quizzes.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = "DB Error: " . $e->getMessage();
    header("Location: ../../frontend/admin/pending_quizzes.php");
    exit;
}
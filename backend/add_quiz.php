<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to create a quiz.";
    header("Location: ../frontend/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_title     = trim($_POST['quiz_title']);
    $category_id    = trim($_POST['category_id']);
    $subcategory_id = trim($_POST['subcategory_id']);
    if ($subcategory_id === '') {
        $subcategory_id = null;
    }

    $user_id  = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'user';
    $status   = ($user_role === 'admin') ? 'approved' : 'pending';

    $questionTexts  = $_POST['question_text'] ?? [];
    $optionTexts    = $_POST['option_text'] ?? [];
    $correctOptions = $_POST['correct_option'] ?? [];

    if (empty($quiz_title) || empty($category_id) || empty($questionTexts)) {
        $_SESSION['error'] = "Please fill out all required fields.";
        header("Location: ../frontend/create_quiz.php");
        exit;
    }

    try {
        $stmtQuiz = $pdo->prepare("
            INSERT INTO quizzes (quiz_title, created_by, category_id, subcategory_id, status)
            VALUES (:title, :uid, :cat, :subcat, :stat)
        ");
        $stmtQuiz->execute([
            ':title' => $quiz_title,
            ':uid'   => $user_id,
            ':cat'   => $category_id,
            ':subcat'=> $subcategory_id,
            ':stat'  => $status
        ]);
        $newQuizId = $pdo->lastInsertId();

        $qIndex = 0;
        foreach ($questionTexts as $qText) {
            $qText = trim($qText);
            if (empty($qText)) {
                continue;
            }
            $stmtQ = $pdo->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (:qid, :qtxt)");
            $stmtQ->execute([':qid' => $newQuizId, ':qtxt' => $qText]);
            $questionId = $pdo->lastInsertId();

            $correctIndex = isset($correctOptions[$qIndex]) ? (int)$correctOptions[$qIndex] : 0;
            if (isset($optionTexts[$qIndex])) {
                for ($i=0; $i<count($optionTexts[$qIndex]); $i++) {
                    $optText   = trim($optionTexts[$qIndex][$i]);
                    $isCorrect = ($i === $correctIndex) ? 1 : 0;
                    if (!empty($optText)) {
                        $stmtOpt = $pdo->prepare("
                            INSERT INTO options (question_id, option_text, is_correct)
                            VALUES (:qid, :otext, :iscorrect)
                        ");
                        $stmtOpt->execute([
                            ':qid'       => $questionId,
                            ':otext'     => $optText,
                            ':iscorrect' => $isCorrect
                        ]);
                    }
                }
            }
            $qIndex++;
        }

        $_SESSION['success'] = "Quiz created successfully!";
        header("Location: ../frontend/create_quiz.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "DB Error: " . $e->getMessage();
        header("Location: ../frontend/create_quiz.php");
        exit;
    }
} else {
    header("Location: ../frontend/create_quiz.php");
    exit;
}
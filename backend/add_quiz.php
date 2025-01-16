<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to create a quiz.";
    header("Location: ../frontend/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_title      = trim($_POST['quiz_title']);
    $category_id     = trim($_POST['category_id']);
    $subcategory_id  = trim($_POST['subcategory_id']);

    if ($subcategory_id === '') {
        $subcategory_id = null;
    }
    $user_id         = $_SESSION['user_id'];
    $user_role       = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';

    // If user is admin, quiz auto-approved, else pending
    $quiz_status = ($user_role === 'admin') ? 'approved' : 'pending';

    // question_text[] => array of question texts
    // option_text[i][] => each question i has 4 options
    // correct_option[i] => which option index is correct for question i
    $questionTexts  = isset($_POST['question_text']) ? $_POST['question_text'] : [];
    $correctOptions = isset($_POST['correct_option']) ? $_POST['correct_option'] : [];
    $optionTexts    = isset($_POST['option_text']) ? $_POST['option_text'] : [];

    // Basic validation
    if (empty($quiz_title) || empty($category_id) || empty($questionTexts)) {
        $_SESSION['error'] = "Please fill out all required fields.";
        header("Location: ../frontend/create_quiz.php");
        exit;
    }

    try {
        // 1) Insert into 'quizzes'
        $stmtQz = $pdo->prepare("
            INSERT INTO quizzes (quiz_title, created_by, category_id, subcategory_id, status)
            VALUES (:title, :uid, :cat, :subcat, :stat)
        ");
        $stmtQz->execute([
            ':title'  => $quiz_title,
            ':uid'    => $user_id,
            ':cat'    => $category_id,
            ':subcat' => $subcategory_id,
            ':stat'   => $quiz_status
        ]);

        // Get the newly inserted quiz ID
        $newQuizId = $pdo->lastInsertId();

        // 2) Loop through each question
        //    We know questionTexts is a simple array, e.g. question_text[0], question_text[1], ...
        //    But correct_option/option_text is indexed by questionIndex, which might not match 0,1,2 if the user added more questions.

        // We'll keep an internal index to track each question block
        $qIndex = 0;
        foreach ($questionTexts as $qText) {
            $qText = trim($qText);
            if (empty($qText)) {
                // skip empty question
                continue;
            }

            // Insert question
            $stmtQs = $pdo->prepare("
                INSERT INTO questions (quiz_id, question_text) 
                VALUES (:qzid, :qtxt)
            ");
            $stmtQs->execute([
                ':qzid' => $newQuizId,
                ':qtxt' => $qText
            ]);
            $newQuestionId = $pdo->lastInsertId();

            // The correct option index for this question
            // If it's not set, default to 0 (just in case)
            $correctIndex = isset($correctOptions[$qIndex]) ? (int) $correctOptions[$qIndex] : 0;

            // Insert options
            if (isset($optionTexts[$qIndex])) {
                // $optionTexts[$qIndex] => array of 4 text inputs
                for ($i=0; $i<count($optionTexts[$qIndex]); $i++) {
                    $optText = trim($optionTexts[$qIndex][$i]);
                    if (empty($optText)) {
                        continue;
                    }
                    // is this the correct option?
                    $isCorrect = ($i === $correctIndex) ? 1 : 0;

                    $stmtOpt = $pdo->prepare("
                        INSERT INTO options (question_id, option_text, is_correct)
                        VALUES (:qid, :otext, :iscorrect)
                    ");
                    $stmtOpt->execute([
                        ':qid' => $newQuestionId,
                        ':otext' => $optText,
                        ':iscorrect' => $isCorrect
                    ]);
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
    // Not a POST request
    header("Location: ../frontend/create_quiz.php");
    exit;
}
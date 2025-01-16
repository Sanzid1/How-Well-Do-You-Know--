<?php
session_start();
require_once '../backend/config.php';

// We'll require users to be logged in to attempt a quiz.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to attempt quizzes.";
    header("Location: login.php");
    exit;
}

// Check if quiz_id is provided
if (!isset($_GET['quiz_id'])) {
    $_SESSION['error'] = "No quiz specified.";
    header("Location: browse_quizzes.php");
    exit;
}

$quizId = (int) $_GET['quiz_id'];

// If form is submitted, handle scoring
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // We’ll read the correct options from the DB or from hidden fields
    // For simplicity, let's do it from the DB again
    try {
        // Fetch all questions and options for this quiz
        $stmt = $pdo->prepare("
            SELECT q.question_id, q.question_text, o.option_id, o.is_correct
            FROM questions q
            JOIN options o ON q.question_id = o.question_id
            WHERE q.quiz_id = :qid
            ORDER BY q.question_id ASC
        ");
        $stmt->execute([':qid' => $quizId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by question
        $questions = [];
        foreach($rows as $row) {
            $qId = $row['question_id'];
            if (!isset($questions[$qId])) {
                $questions[$qId] = [
                    'question_id' => $qId,
                    'options' => []
                ];
            }
            // push option info
            $questions[$qId]['options'][] = [
                'option_id' => $row['option_id'],
                'is_correct' => $row['is_correct']
            ];
        }

        $score = 0;
        $penalty = 0; // If you want to implement penalty for wrong answers

        // $_POST['answer'][question_id] = option_id
        if (isset($_POST['answer'])) {
            foreach($_POST['answer'] as $qId => $chosenOptionId) {
                // Check if chosen is correct
                if (isset($questions[$qId])) {
                    $found = false;
                    foreach($questions[$qId]['options'] as $opt) {
                        if ($opt['option_id'] == $chosenOptionId) {
                            // If correct
                            if ($opt['is_correct'] == 1) {
                                $score++;
                            } else {
                                $penalty++; // if we want to track penalty
                            }
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $penalty++;
                    }
                }
            }
        }

        // For demonstration, store final score in session
        $_SESSION['success'] = "Quiz Completed! You scored: $score (Penalties: $penalty)";
        header("Location: browse_quizzes.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error scoring quiz: " . $e->getMessage();
        header("Location: browse_quizzes.php");
        exit;
    }

} else {
    // Display the quiz questions
    try {
        // First, confirm the quiz is approved
        $stmtQuiz = $pdo->prepare("
            SELECT quiz_id, quiz_title, status 
            FROM quizzes 
            WHERE quiz_id = :qid AND status = 'approved'
        ");
        $stmtQuiz->execute([':qid' => $quizId]);
        $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

        if (!$quiz) {
            $_SESSION['error'] = "This quiz is not available or not approved.";
            header("Location: browse_quizzes.php");
            exit;
        }

        // Fetch all questions + options
        $stmt = $pdo->prepare("
            SELECT q.question_id, q.question_text, o.option_id, o.option_text
            FROM questions q
            JOIN options o ON q.question_id = o.question_id
            WHERE q.quiz_id = :qid
            ORDER BY q.question_id ASC
        ");
        $stmt->execute([':qid' => $quizId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize them by question
        $quizData = [];
        foreach($rows as $row) {
            $qId = $row['question_id'];
            if (!isset($quizData[$qId])) {
                $quizData[$qId] = [
                    'question_id' => $qId,
                    'question_text' => $row['question_text'],
                    'options' => []
                ];
            }
            $quizData[$qId]['options'][] = [
                'option_id' => $row['option_id'],
                'option_text' => $row['option_text']
            ];
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "DB Error: " . $e->getMessage();
        header("Location: browse_quizzes.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Take Quiz</title>
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
  <?php if($_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
    <h2>Quiz: <?php echo htmlspecialchars($quiz['quiz_title']); ?></h2>
    <form method="POST">
      <?php foreach($quizData as $qId => $qInfo): ?>
        <div class="card mb-4">
          <div class="card-body">
            <h5>Question: <?php echo htmlspecialchars($qInfo['question_text']); ?></h5>
            <?php foreach($qInfo['options'] as $opt): ?>
              <div class="form-check">
                <input class="form-check-input" type="radio" 
                       name="answer[<?php echo $qId; ?>]" 
                       value="<?php echo $opt['option_id']; ?>" required>
                <label class="form-check-label">
                  <?php echo htmlspecialchars($opt['option_text']); ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-primary">Submit Quiz</button>
    </form>
  <?php endif; ?>
</div>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to attempt quizzes.";
    header("Location: login.php");
    exit;
}

if (!isset($_GET['quiz_id']) && !isset($_POST['quiz_id'])) {
    $_SESSION['error'] = "No quiz specified.";
    header("Location: browse_quizzes.php");
    exit;
}
$quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : (int)$_POST['quiz_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
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

        $stmt = $pdo->prepare("
            SELECT q.question_id, q.question_text, o.option_id, o.option_text
            FROM questions q
            JOIN options o ON q.question_id = o.question_id
            WHERE q.quiz_id = :qid
            ORDER BY q.question_id ASC
        ");
        $stmt->execute([':qid' => $quizId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            $_SESSION['error'] = "No questions found for this quiz.";
            header("Location: browse_quizzes.php");
            exit;
        }

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

        $stmtA = $pdo->prepare("
            INSERT INTO quiz_attempts (user_id, quiz_id)
            VALUES (:uid, :qid)
        ");
        $stmtA->execute([
            ':uid' => $_SESSION['user_id'],
            ':qid' => $quizId
        ]);
        $attemptId = $pdo->lastInsertId();
        $_SESSION['current_attempt_id'] = $attemptId;
    } catch (PDOException $e) {
        $_SESSION['error'] = "DB Error: " . $e->getMessage();
        header("Location: browse_quizzes.php");
        exit;
    }

    include_once 'partials/header.php';
    ?>
    <h2><?php echo htmlspecialchars($quiz['quiz_title']); ?></h2>
    <form method="POST" action="take_quiz.php">
      <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
      <?php foreach($quizData as $qInfo): ?>
        <div class="card mb-4">
          <div class="card-body">
            <h5><?php echo htmlspecialchars($qInfo['question_text']); ?></h5>
            <?php foreach($qInfo['options'] as $opt): ?>
              <div class="form-check">
                <input class="form-check-input" type="radio"
                       name="answer[<?php echo $qInfo['question_id']; ?>]"
                       value="<?php echo $opt['option_id']; ?>" required>
                <label class="form-check-label"><?php echo htmlspecialchars($opt['option_text']); ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-primary">Submit Quiz</button>
    </form>
    <?php
    include_once 'partials/footer.php';
    exit;
} else {
    try {
        if (!isset($_SESSION['current_attempt_id'])) {
            $_SESSION['error'] = "No active quiz attempt found.";
            header("Location: browse_quizzes.php");
            exit;
        }
        $attemptId = (int)$_SESSION['current_attempt_id'];

        $stmtQ = $pdo->prepare("
            SELECT o.option_id, o.is_correct, q.question_id
            FROM questions q
            JOIN options o ON q.question_id = o.question_id
            WHERE q.quiz_id = :qid
        ");
        $stmtQ->execute([':qid' => $quizId]);
        $allOptions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

        $optionLookup = [];
        foreach($allOptions as $item) {
            $qId = $item['question_id'];
            if (!isset($optionLookup[$qId])) {
                $optionLookup[$qId] = [];
            }
            $optionLookup[$qId][$item['option_id']] = $item['is_correct'];
        }

        $score = 0;
        $penalty = 0;
        $answers = $_POST['answer'] ?? [];

        foreach ($answers as $qId => $chosenOptionId) {
            $isCorrect = 0;
            if (isset($optionLookup[$qId][$chosenOptionId])) {
                if ($optionLookup[$qId][$chosenOptionId] == 1) {
                    $score++;
                    $isCorrect = 1;
                } else {
                    $penalty++;
                }
            } else {
                $penalty++;
            }
            $stmtUA = $pdo->prepare("
                INSERT INTO user_answers (attempt_id, question_id, chosen_option_id, is_correct)
                VALUES (:aid, :qid, :oid, :iscorrect)
            ");
            $stmtUA->execute([
                ':aid' => $attemptId,
                ':qid' => $qId,
                ':oid' => $chosenOptionId,
                ':iscorrect' => $isCorrect
            ]);
        }

        $stmtUp = $pdo->prepare("
            UPDATE quiz_attempts
            SET score = :score, end_time = NOW()
            WHERE attempt_id = :aid
        ");
        $stmtUp->execute([
            ':score' => $score,
            ':aid'   => $attemptId
        ]);

        unset($_SESSION['current_attempt_id']);

        $_SESSION['success'] = "Quiz submitted! You scored $score. (Penalties: $penalty)";
        header("Location: browse_quizzes.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error scoring quiz: " . $e->getMessage();
        header("Location: browse_quizzes.php");
        exit;
    }
}
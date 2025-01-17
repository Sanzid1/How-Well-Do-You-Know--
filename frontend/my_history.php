<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to view your quiz history.";
    header("Location: login.php");
    exit;
}
require_once '../backend/config.php';

$userId = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("
        SELECT qa.attempt_id, qa.score, qa.start_time, qa.end_time, q.quiz_title
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.quiz_id
        WHERE qa.user_id = :uid
        ORDER BY qa.start_time DESC
    ");
    $stmt->execute([':uid' => $userId]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "DB Error: " . $e->getMessage();
    $attempts = [];
}
include_once 'partials/header.php';
?>
<h2>My Quiz History</h2>
<?php if(isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if(empty($attempts)): ?>
  <p>No quiz attempts found.</p>
<?php else: ?>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Quiz Title</th>
        <th>Score</th>
        <th>Start Time</th>
        <th>End Time</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($attempts as $a): ?>
      <tr>
        <td><?php echo htmlspecialchars($a['quiz_title']); ?></td>
        <td><?php echo $a['score']; ?></td>
        <td><?php echo $a['start_time']; ?></td>
        <td><?php echo $a['end_time'] ?: 'In Progress'; ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php include_once 'partials/footer.php'; ?>
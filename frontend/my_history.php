<?php
session_start();
require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to view your quiz history.";
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch all attempts by this user
try {
    $stmt = $pdo->prepare("
        SELECT qa.attempt_id, qa.score, qa.start_time, qa.end_time,
               q.quiz_title
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Quiz History</title>
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
  <h2>My Quiz History</h2>
  <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
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
          <td><?php echo $a['end_time'] ? $a['end_time'] : 'In Progress'; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
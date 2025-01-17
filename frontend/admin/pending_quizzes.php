<?php
session_start();
require_once '../../backend/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
try {
    $stmt = $pdo->prepare("
        SELECT q.quiz_id, q.quiz_title, u.name AS creator_name, q.created_at
        FROM quizzes q
        JOIN users u ON q.created_by = u.user_id
        WHERE q.status = 'pending'
        ORDER BY q.created_at DESC
    ");
    $stmt->execute();
    $pendingQuizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "DB Error: " . $e->getMessage();
    $pendingQuizzes = [];
}
include_once '../partials/header.php';
?>
<h2>Pending Quizzes</h2>
<?php if(isset($_SESSION['success'])): ?>
  <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if(isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if(count($pendingQuizzes) === 0): ?>
  <p>No pending quizzes at the moment.</p>
<?php else: ?>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Quiz ID</th>
        <th>Quiz Title</th>
        <th>Created By</th>
        <th>Date Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pendingQuizzes as $quiz): ?>
        <tr>
          <td><?php echo $quiz['quiz_id']; ?></td>
          <td><?php echo htmlspecialchars($quiz['quiz_title']); ?></td>
          <td><?php echo htmlspecialchars($quiz['creator_name']); ?></td>
          <td><?php echo $quiz['created_at']; ?></td>
          <td>
            <a href="../../backend/admin/approve_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" 
               class="btn btn-success btn-sm">Approve</a>
            <a href="../../backend/admin/reject_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" 
               class="btn btn-danger btn-sm">Reject</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php include_once '../partials/footer.php'; ?>
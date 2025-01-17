<?php
session_start();
require_once '../backend/config.php';

try {
    $stmt = $pdo->prepare("
        SELECT q.quiz_id, q.quiz_title, q.created_at,
               c.category_name AS category, sc.category_name AS subcategory,
               u.name AS creator_name
        FROM quizzes q
        JOIN users u ON q.created_by = u.user_id
        LEFT JOIN categories c ON q.category_id = c.category_id
        LEFT JOIN categories sc ON q.subcategory_id = sc.category_id
        WHERE q.status = 'approved'
        ORDER BY q.created_at DESC
    ");
    $stmt->execute();
    $approvedQuizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "DB Error: " . $e->getMessage();
    $approvedQuizzes = [];
}
include_once 'partials/header.php';
?>
<h2>Approved Quizzes</h2>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if (empty($approvedQuizzes)): ?>
  <p>No approved quizzes available at the moment.</p>
<?php else: ?>
  <div class="list-group">
    <?php foreach($approvedQuizzes as $quiz): ?>
      <a href="take_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" 
         class="list-group-item list-group-item-action">
        <h5 class="mb-1"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h5>
        <small>
          Category: <?php echo htmlspecialchars($quiz['category'] ?? 'N/A'); ?> /
          Subcategory: <?php echo htmlspecialchars($quiz['subcategory'] ?? 'None'); ?> <br>
          Created by: <?php echo htmlspecialchars($quiz['creator_name']); ?>
          on <?php echo $quiz['created_at']; ?>
        </small>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php include_once 'partials/footer.php'; ?>
<?php
session_start();
require_once '../backend/config.php';

// We allow any user to browse, even guests (not logged in).
// They can see the list but must log in before taking the quiz, if you choose to enforce that.

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Browse Approved Quizzes</title>
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="index.php">How Well Do You Know?</a>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="admin/categories.php">Manage Categories</a>
          </li>
        <?php endif; ?>
        <li class="nav-item">
          <?php if (!isset($_SESSION['user_id'])): ?>
            <a class="nav-link" href="login.php">Login</a>
          <?php else: ?>
            <a class="nav-link" href="../backend/logout.php">Logout</a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h2>Approved Quizzes</h2>

  <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <?php 
        echo $_SESSION['error']; 
        unset($_SESSION['error']);
      ?>
    </div>
  <?php endif; ?>

  <?php if(count($approvedQuizzes) === 0): ?>
    <p>No approved quizzes available at the moment.</p>
  <?php else: ?>
    <div class="list-group">
      <?php foreach($approvedQuizzes as $quiz): ?>
        <a 
          href="take_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" 
          class="list-group-item list-group-item-action"
        >
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
</div>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
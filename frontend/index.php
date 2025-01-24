<?php
include_once 'partials/header.php';
?>
<div class="container mt-4">
  <h2>Welcome to "How Well Do You Know?"</h2>
  <?php if (!$isLoggedIn): ?>
    <p>If you are not logged in, please <a href="login.php">log in</a> or <a href="register.php">register</a>.</p>
  <?php else: ?>
    <p>Welcome back, <?php echo htmlspecialchars($userName); ?>! Start <a href="browse_quizzes.php">browsing quizzes</a>.</p>
  <?php endif; ?>
</div>
<?php include_once 'partials/footer.php'; ?>
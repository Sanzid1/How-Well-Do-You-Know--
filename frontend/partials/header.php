<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $isLoggedIn ? $_SESSION['user_role'] : null;
$userName   = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>How Well Do You Know?</title>
  <!-- Correct CSS paths -->
  <link rel="stylesheet" href="/How-Well-Do-You-Know--/frontend/bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/How-Well-Do-You-Know--/frontend/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <!-- Fixed home link -->
    <a class="navbar-brand" href="/How-Well-Do-You-Know--/index.php">How Well Do You Know?</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" 
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
  <?php if (!$isLoggedIn): ?>
    <!-- Login/Register (relative to frontend/) -->
    <li class="nav-item"><a class="nav-link" href="/How-Well-Do-You-Know--/frontend/login.php">Login</a></li>
    <li class="nav-item"><a class="nav-link" href="/How-Well-Do-You-Know--/frontend/register.php">Register</a></li>
  <?php else: ?>
    <!-- User Links (relative to frontend/) -->
    <li class="nav-item"><a class="nav-link" href="/How-Well-Do-You-Know--/frontend/browse_quizzes.php">Browse Quizzes</a></li>
    <li class="nav-item"><a class="nav-link" href="/How-Well-Do-You-Know--/frontend/leaderboard.php">Leaderboard</a></li>
    <li class="nav-item"><a class="nav-link" href="/How-Well-Do-You-Know--/frontend/my_history.php">My History</a></li>
    
    <?php if ($userRole === 'user'): ?>
      <li class="nav-item"><a class="nav-link" href="/How-Well-Do-You-Know--/frontend/create_quiz.php">Create Quiz</a></li>
    <?php endif; ?>
    
    <?php if ($userRole === 'admin'): ?>
      <!-- Admin Links (relative to frontend/) -->
      <li class="nav-item"><a class="nav-link" href="/How-Well-Do-You-Know--/frontend/admin/categories.php">Manage Categories</a></li>
      <li class="nav-item"><a class="nav-link" href="/How-Well-Do-You-Know--/frontend/admin/pending_quizzes.php">Pending Quizzes</a></li>
    <?php endif; ?>
    
    <!-- Logout (relative to backend/) -->
    <li class="nav-item">
      <a class="nav-link" href="/How-Well-Do-You-Know--/backend/logout.php">
        Logout (<?php echo htmlspecialchars($userName); ?>)
      </a>
    </li>
  <?php endif; ?>
</ul>
    </div>
  </div>
</nav>
<div class="main-container"></div>

<!-- Bootstrap JS and dependencies -->
<script src="/How-Well-Do-You-Know--/frontend/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
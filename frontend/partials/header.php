<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config.php'; // Path to config is correct

$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $isLoggedIn ? $_SESSION['user_role'] : null;
$userName   = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>How Well Do You Know?</title>
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css"> <!-- Fixed path -->
  <link rel="stylesheet" href="css/style.css"> <!-- Fixed path -->
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">How Well Do You Know?</a> <!-- Link to frontend/index.php -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" 
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
  <?php if (!$isLoggedIn): ?>
    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
  <?php else: ?>
    <li class="nav-item"><a class="nav-link" href="browse_quizzes.php">Browse Quizzes</a></li>
    <li class="nav-item"><a class="nav-link" href="leaderboard.php">Leaderboard</a></li>
    <li class="nav-item"><a class="nav-link" href="my_history.php">My History</a></li>
    <?php if ($userRole === 'user'): ?>
      <li class="nav-item"><a class="nav-link" href="create_quiz.php">Create Quiz</a></li> <!-- Added for users -->
    <?php endif; ?>
    <?php if ($userRole === 'admin'): ?>
      <li class="nav-item"><a class="nav-link" href="admin/categories.php">Manage Categories</a></li>
      <li class="nav-item"><a class="nav-link" href="admin/pending_quizzes.php">Pending Quizzes</a></li> <!-- Added for admins -->
    <?php endif; ?>
    <li class="nav-item">
      <a class="nav-link" href="../backend/logout.php">
        Logout (<?php echo htmlspecialchars($userName); ?>)
      </a>
    </li>
  <?php endif; ?>
</ul>
    </div>
  </div>
</nav>
<div class="main-container"></div>
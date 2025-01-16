<?php
// Start session if needed (for user detection, etc.)
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>How Well Do You Know? - Home</title>
  <!-- Bootstrap CSS (Local or CDN) -->
  <!-- If using local: -->
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="index.php">How Well Do You Know?</a>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if(!isset($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="register.php">Register</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="login.php">Login</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="../backend/logout.php">Logout</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<div class="container mt-5">
  <div class="text-center">
    <h1>Welcome to "How Well Do You Know?"</h1>
    <p>Take quizzes, create your own, and track your progress.</p>
    <a href="register.php" class="btn btn-primary">Get Started</a>
  </div>
</div>

<!-- Bootstrap JS (Local or CDN) -->
<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="js/main.js"></script>
</body>
</html>
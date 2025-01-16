<?php
session_start();
require_once '../backend/config.php';

// We can allow anyone to see the leaderboard, even if not logged in
// If you want only logged-in users to see it, wrap in if statements.

try {
    // Summation approach: sum all quiz_attempt scores per user
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.name AS user_name, 
               COALESCE(SUM(qa.score), 0) AS total_score
        FROM users u
        LEFT JOIN quiz_attempts qa ON u.user_id = qa.user_id
        GROUP BY u.user_id
        ORDER BY total_score DESC
        LIMIT 10
    ");
    $stmt->execute();
    $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "DB Error: " . $e->getMessage();
    $leaders = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leaderboard</title>
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
  <h2>Leaderboard</h2>
  <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <?php if(empty($leaders)): ?>
    <p>No scores available.</p>
  <?php else: ?>
    <table class="table table-bordered">
      <thead>
      <tr>
        <th>Rank</th>
        <th>User Name</th>
        <th>Total Score</th>
      </tr>
      </thead>
      <tbody>
      <?php 
      $rank = 1;
      foreach($leaders as $l):
      ?>
        <tr>
          <td><?php echo $rank++; ?></td>
          <td><?php echo htmlspecialchars($l['user_name']); ?></td>
          <td><?php echo $l['total_score']; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
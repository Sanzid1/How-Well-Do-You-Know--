<?php
session_start();
require_once '../backend/config.php';

try {
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
include_once 'partials/header.php';
?>
<h2>Leaderboard</h2>
<?php if(isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
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
      <?php $rank = 1; ?>
      <?php foreach($leaders as $l): ?>
        <tr>
          <td><?php echo $rank++; ?></td>
          <td><?php echo htmlspecialchars($l['user_name']); ?></td>
          <td><?php echo $l['total_score']; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php include_once 'partials/footer.php'; ?>
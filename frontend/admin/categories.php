<?php
session_start();
require_once '../../backend/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin')) {
    // Not an admin, redirect to homepage
    header('Location: ../index.php');
    exit;
}

// Handle any success/error messages in session
$successMsg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$errorMsg   = isset($_SESSION['error'])   ? $_SESSION['error']   : '';
unset($_SESSION['success'], $_SESSION['error']);

// Fetch all top-level categories (parent_id = NULL) and their subcategories
try {
    // Top-level
    $stmtTop = $pdo->prepare("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY category_name ASC");
    $stmtTop->execute();
    $topCategories = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

    // All categories (we'll filter subcategories in the loop)
    $stmtAll = $pdo->prepare("SELECT * FROM categories ORDER BY category_name ASC");
    $stmtAll->execute();
    $allCategories = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMsg = "DB Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Manage Categories</title>
  <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="../index.php">How Well Do You Know?</a>
    <div>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="../index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../../backend/logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h2>Category Management</h2>

  <?php if(!empty($successMsg)): ?>
    <div class="alert alert-success"><?php echo $successMsg; ?></div>
  <?php endif; ?>
  <?php if(!empty($errorMsg)): ?>
    <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
  <?php endif; ?>

  <!-- Form to Add New Category -->
  <div class="card mb-4">
    <div class="card-body">
      <h4 class="card-title">Add a New Category</h4>
      <form action="../../backend/admin/add_category.php" method="POST">
        <div class="mb-3">
          <label for="category_name" class="form-label">Category Name</label>
          <input type="text" name="category_name" id="category_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="parent_id" class="form-label">Parent Category (Optional)</label>
          <select name="parent_id" id="parent_id" class="form-select">
            <option value="">None (Top-level)</option>
            <?php foreach ($topCategories as $cat): ?>
              <option value="<?php echo $cat['category_id']; ?>">
                <?php echo $cat['category_name']; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Category</button>
      </form>
    </div>
  </div>

  <!-- Display Existing Categories -->
  <h4>Existing Categories</h4>
  <ul class="list-group">
    <?php foreach ($topCategories as $topCat): ?>
      <li class="list-group-item">
        <strong><?php echo $topCat['category_name']; ?></strong> (ID: <?php echo $topCat['category_id']; ?>)
        <?php
          // Find subcategories
          $subCats = array_filter($allCategories, function($c) use ($topCat) {
              return $c['parent_id'] == $topCat['category_id'];
          });
          if (!empty($subCats)) {
              echo "<ul class='mt-2'>";
              foreach($subCats as $sc) {
                  echo "<li>{$sc['category_name']} (ID: {$sc['category_id']})</li>";
              }
              echo "</ul>";
          }
        ?>
      </li>
    <?php endforeach; ?>
  </ul>

</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
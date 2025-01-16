<?php
session_start();
require_once '../backend/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Retrieve top-level categories
try {
    // We might allow subcategories too, but let’s retrieve all categories at once
    $stmtCat = $pdo->prepare("SELECT * FROM categories ORDER BY category_name ASC");
    $stmtCat->execute();
    $allCategories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// We'll handle quiz creation in backend/add_quiz.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Quiz</title>
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
        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="admin/categories.php">Manage Categories</a>
          </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link" href="../backend/logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h2>Create a New Quiz</h2>
  <?php
    // Display any error/success messages
    if (isset($_SESSION['error'])) {
      echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
      unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
      echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
      unset($_SESSION['success']);
    }
  ?>

  <form action="../backend/add_quiz.php" method="POST" id="quizForm">
    <!-- Quiz Title -->
    <div class="mb-3">
      <label for="quiz_title" class="form-label">Quiz Title</label>
      <input type="text" class="form-control" id="quiz_title" name="quiz_title" required>
    </div>

    <!-- Category Selection -->
    <div class="mb-3">
      <label for="category_id" class="form-label">Select Category</label>
      <select name="category_id" id="category_id" class="form-select" required>
        <option value="">--Choose a Category--</option>
        <?php
        // Let's group top-level vs subcategories in the same dropdown 
        // You can refine to have two dropdowns if you want
        foreach($allCategories as $cat) {
          // If parent_id is null, it's a top-level; otherwise subcategory
          if ($cat['parent_id'] == null) {
            echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
          }
        }
        ?>
      </select>
    </div>

    <!-- Subcategory Selection -->
    <div class="mb-3">
      <label for="subcategory_id" class="form-label">Select Subcategory (Optional)</label>
      <select name="subcategory_id" id="subcategory_id" class="form-select">
        <option value="">--No Subcategory--</option>
        <?php
        foreach($allCategories as $cat) {
          if ($cat['parent_id'] !== null) {
            echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
          }
        }
        ?>
      </select>
    </div>

    <!-- Questions and Options (Multiple) -->
    <!-- We'll start with a single block repeated via JS, or you can just fix a certain number for now. -->
    <div id="questionsContainer">
      <div class="question-block border p-3 mb-3">
        <h5>Question 1</h5>
        <div class="mb-3">
          <label class="form-label">Question Text</label>
          <textarea class="form-control" name="question_text[]" rows="2" required></textarea>
        </div>
        
        <!-- Options (4) -->
        <div class="row g-2">
          <?php for($i=1; $i<=4; $i++): ?>
            <div class="col-6 mb-3">
              <label class="form-label">Option <?php echo $i; ?></label>
              <input type="text" class="form-control" name="option_text[0][]" required>
              <div class="form-check mt-1">
                <input class="form-check-input" type="radio" 
                       name="correct_option[0]" value="<?php echo $i-1; ?>" 
                       <?php echo ($i===1 ? 'checked' : ''); ?>>
                <label class="form-check-label">
                  Correct
                </label>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

    <!-- Add another question button -->
    <div class="mb-3">
      <button type="button" class="btn btn-secondary" id="addQuestionBtn">Add Another Question</button>
    </div>

    <button type="submit" class="btn btn-primary">Submit Quiz</button>
  </form>
</div>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
// We'll add a bit of JS to clone the question block
let questionIndex = 1;
document.getElementById('addQuestionBtn').addEventListener('click', function() {
  questionIndex++;
  const container = document.getElementById('questionsContainer');
  
  // Clone the first question-block
  const firstBlock = container.querySelector('.question-block');
  const newBlock = firstBlock.cloneNode(true);
  
  // Update the heading
  newBlock.querySelector('h5').textContent = 'Question ' + questionIndex;
  
  // Clear textareas and inputs
  newBlock.querySelectorAll('textarea').forEach(ta => ta.value = '');
  newBlock.querySelectorAll('input[type=text]').forEach(inp => inp.value = '');
  
  // Update name attributes for correct_option radio group
  // e.g., from correct_option[0] to correct_option[1], etc.
  newBlock.querySelectorAll('input[type=radio]').forEach((radio, idx) => {
    radio.name = 'correct_option[' + questionIndex + ']';
    radio.value = idx; 
    // set the first radio as checked
    if(idx===0) radio.checked = true;
  });
  
  // Also update name attributes for option text 
  // e.g., name="option_text[0][]" => name="option_text[1][]"
  newBlock.querySelectorAll('input[type=text]').forEach((textInput) => {
    textInput.name = 'option_text[' + questionIndex + '][]';
  });
  // Also update question_text
  newBlock.querySelectorAll('textarea').forEach((ta) => {
    ta.name = 'question_text[]';
  });
  
  container.appendChild(newBlock);
});
</script>

</body>
</html>
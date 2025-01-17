<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../backend/config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY category_name ASC");
    $stmt->execute();
    $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "DB Error: " . $e->getMessage();
    $allCategories = [];
}
include_once 'partials/header.php';
?>
<h2>Create a New Quiz</h2>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<form action="../backend/add_quiz.php" method="POST" id="quizForm">
  <div class="mb-3">
    <label for="quiz_title" class="form-label">Quiz Title</label>
    <input type="text" class="form-control" id="quiz_title" name="quiz_title" required>
  </div>
  <div class="mb-3">
    <label for="category_id" class="form-label">Select Category</label>
    <select name="category_id" id="category_id" class="form-select" required>
      <option value="">--Choose a Category--</option>
      <?php
      foreach($allCategories as $cat) {
        if ($cat['parent_id'] == null) {
          echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
        }
      }
      ?>
    </select>
  </div>
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
  <div id="questionsContainer">
    <div class="question-block border p-3 mb-3">
      <h5>Question 1</h5>
      <div class="mb-3">
        <label class="form-label">Question Text</label>
        <textarea class="form-control" name="question_text[]" rows="2" required></textarea>
      </div>
      <div class="row g-2">
        <?php for($i=1; $i<=4; $i++): ?>
          <div class="col-6 mb-3">
            <label class="form-label">Option <?php echo $i; ?></label>
            <input type="text" class="form-control" name="option_text[0][]" required>
            <div class="form-check mt-1">
              <input class="form-check-input" type="radio" 
                     name="correct_option[0]" value="<?php echo $i-1; ?>"
                     <?php echo ($i===1 ? 'checked' : ''); ?>>
              <label class="form-check-label">Correct</label>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
  <div class="mb-3">
    <button type="button" class="btn btn-secondary" id="addQuestionBtn">Add Another Question</button>
  </div>
  <button type="submit" class="btn btn-primary">Submit Quiz</button>
</form>
<script>
let questionIndex = 1;
document.getElementById('addQuestionBtn').addEventListener('click', function() {
  questionIndex++;
  const container = document.getElementById('questionsContainer');
  const firstBlock = container.querySelector('.question-block');
  const newBlock = firstBlock.cloneNode(true);
  newBlock.querySelector('h5').textContent = 'Question ' + questionIndex;
  newBlock.querySelectorAll('textarea').forEach(ta => ta.value = '');
  newBlock.querySelectorAll('input[type=text]').forEach(inp => inp.value = '');
  newBlock.querySelectorAll('input[type=radio]').forEach((radio, idx) => {
    radio.name = 'correct_option[' + questionIndex + ']';
    radio.value = idx;
    if(idx===0) radio.checked = true;
  });
  newBlock.querySelectorAll('input[type=text]').forEach((textInput) => {
    textInput.name = 'option_text[' + questionIndex + '][]';
  });
  container.appendChild(newBlock);
});
</script>
<?php include_once 'partials/footer.php'; ?>
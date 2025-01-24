<?php
session_start();

include_once 'partials/header.php';
?>
<h2>Register</h2>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<!-- Update the form action -->
<form action="../backend/register.php" method="POST"> <!-- Correct path to backend -->
  <div class="mb-3">
    <label for="name" class="form-label">Full Name</label>
    <input type="text" name="name" id="name" class="form-control" required>
  </div>
  <div class="mb-3">
    <label for="email" class="form-label">Email Address</label>
    <input type="email" name="email" id="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" name="password" id="password" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-custom">Sign Up</button>
</form>
<p class="mt-3">
  Already have an account? <a href="login.php">Log in here</a>.
</p>
<?php include_once 'partials/footer.php'; ?>
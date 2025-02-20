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
<form action="../backend/register.php" method="POST">
  <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
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
    <div class="input-group">
      <input type="password" name="password" id="password" class="form-control" required>
      <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', event)"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <div class="mb-3">
    <label for="confirm_password" class="form-label">Confirm Password</label>
    <div class="input-group">
      <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
      <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', event)"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <button type="submit" class="btn btn-custom">Sign Up</button>
</form>
<p class="mt-3">
  Already have an account? <a href="login.php">Log in here</a>.
</p>
<?php include_once 'partials/footer.php'; ?>
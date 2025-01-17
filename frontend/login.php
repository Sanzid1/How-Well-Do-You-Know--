<?php
session_start();

include_once 'partials/header.php';
?>
<h2>Login</h2>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
<form action="../backend/login.php" method="POST">
  <div class="mb-3">
    <label for="email" class="form-label">Email Address</label>
    <input type="email" name="email" id="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" name="password" id="password" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-custom">Log In</button>
</form>
<p class="mt-3">
  Not registered? <a href="register.php">Register here</a>.
</p>
<?php include_once 'partials/footer.php'; ?>
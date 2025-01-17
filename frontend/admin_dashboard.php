<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
include_once 'partials/header.php';
?>
<h2>Admin Dashboard</h2>
<p>Welcome, Admin!</p>
<p>Use the navigation to manage categories, pending quizzes, etc.</p>
<?php include_once 'partials/footer.php'; ?>
```

---

### **frontend/user_dashboard.php**
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] === 'admin')) {
    header("Location: index.php");
    exit;
}
include_once 'partials/header.php';
?>
<h2>User Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
<p>Use the navigation to browse or create quizzes, check your history, etc.</p>
<?php include_once 'partials/footer.php'; ?>
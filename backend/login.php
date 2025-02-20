<?php
session_start();
require_once 'config.php'; // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: ../frontend/login.php");
        exit;
    }

    // Sanitize input
    $email = filter_var(sanitizeInput($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    // Input validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill out all fields.";
        header("Location: ../frontend/login.php");
        exit;
    }

    if (!$email) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: ../frontend/login.php");
        exit;
    }

    // Check for too many login attempts
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $_SESSION['last_attempt'] < LOGIN_TIMEOUT) {
            $_SESSION['error'] = "Too many failed attempts. Please try again later.";
            header("Location: ../frontend/login.php");
            exit;
        } else {
            // Reset attempts after timeout
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt']);
        }
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password_hash'])) {
                // Reset login attempts on successful login
                unset($_SESSION['login_attempts']);
                unset($_SESSION['last_attempt']);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: ../frontend/index.php");
                exit;
            }
        }

        // Increment login attempts
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_attempt'] = time();

        $_SESSION['error'] = "Invalid email or password.";
        header("Location: ../frontend/login.php");
        exit;

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during login. Please try again later.";
        header("Location: ../frontend/login.php");
        exit;
    }
} else {
    header("Location: ../frontend/login.php");
    exit;
}
?>
<?php
session_start();
require_once 'config.php'; // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: ../frontend/register.php");
        exit;
    }

    // Sanitize and validate input
    $name = sanitizeInput($_POST['name']);
    $email = filter_var(sanitizeInput($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    // Input validation
    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill out all fields.";
        header("Location: ../frontend/register.php");
        exit;
    }

    if (!$email) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: ../frontend/register.php");
        exit;
    }

    // Password strength validation
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: ../frontend/register.php");
        exit;
    }

    try {
        $checkStmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "Email already in use.";
            header("Location: ../frontend/register.php");
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        $insertStmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role, created_at)
            VALUES (:name, :email, :password_hash, 'user', NOW())
        ");
        $insertStmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $hashedPassword
        ]);

        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'user';
        $_SESSION['success'] = "Registration successful! Welcome, $name.";
        header("Location: ../frontend/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during registration. Please try again later.";
        header("Location: ../frontend/register.php");
        exit;
    }
} else {
    header("Location: ../frontend/register.php");
    exit;
}
?>
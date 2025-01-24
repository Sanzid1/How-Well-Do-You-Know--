<?php
session_start();
require_once 'config.php'; // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill out all fields.";
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

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role)
            VALUES (:name, :email, :password_hash, 'user')
        ");
        $insertStmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $hashedPassword
        ]);

        $userId = $pdo->lastInsertId();
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'user';
        $_SESSION['success'] = "Registration successful! Welcome, $name.";
        header("Location: ../frontend/index.php"); // Redirect to user dashboard
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "DB Error: " . $e->getMessage();
        header("Location: ../frontend/register.php");
        exit;
    }
} else {
    header("Location: ../frontend/register.php");
    exit;
}
?>
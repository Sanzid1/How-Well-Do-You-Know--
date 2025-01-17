<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill out all fields.";
        header("Location: ../frontend/login.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: ../frontend/dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Invalid email or password.";
                header("Location: ../frontend/login.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: ../frontend/login.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "DB Error: " . $e->getMessage();
        header("Location: ../frontend/login.php");
        exit;
    }
} else {
    header("Location: ../frontend/login.php");
    exit;
}
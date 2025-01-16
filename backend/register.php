<?php
session_start();
require_once 'config.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        // You can set a session message or handle errors as you wish
        $_SESSION['error'] = "Please fill out all fields.";
        header("Location: ../frontend/register.php");
        exit;
    }

    try {
        // Check if email already exists
        $checkStmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "Email already in use.";
            header("Location: ../frontend/register.php");
            exit;
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $insertStmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role) 
            VALUES (:name, :email, :password_hash, 'user')
        ");
        $insertStmt->bindParam(':name', $name);
        $insertStmt->bindParam(':email', $email);
        $insertStmt->bindParam(':password_hash', $hashedPassword);
        $insertStmt->execute();

        // Optionally log the user in immediately
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['success'] = "Registration successful! Welcome, $name.";

        // Redirect to home or a dashboard
        header("Location: ../frontend/index.php");
        exit;

    } catch (PDOException $e) {
        // Handle DB errors
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../frontend/register.php");
        exit;
    }

} else {
    // If not a POST request, redirect back to register form
    header("Location: ../frontend/register.php");
    exit;
}
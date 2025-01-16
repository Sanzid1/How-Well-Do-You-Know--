<?php
session_start();
require_once '../config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin')) {
    header('Location: ../../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name']);
    $parentId     = trim($_POST['parent_id']);

    // Convert empty parent_id to null
    if ($parentId === '') {
        $parentId = null;
    }

    if (empty($categoryName)) {
        $_SESSION['error'] = "Category name is required.";
        header('Location: ../../frontend/admin/categories.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name, parent_id) 
                               VALUES (:catName, :parent)");
        $stmt->execute([
            ':catName' => $categoryName,
            ':parent'  => $parentId
        ]);

        $_SESSION['success'] = "Category '{$categoryName}' added successfully!";
        header('Location: ../../frontend/admin/categories.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header('Location: ../../frontend/admin/categories.php');
        exit;
    }

} else {
    // Not a POST request
    header('Location: ../../frontend/admin/categories.php');
    exit;
}
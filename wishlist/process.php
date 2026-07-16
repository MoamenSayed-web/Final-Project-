<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? 'add';
    
    if ($productId > 0) {
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }
        
        if ($action === 'add') {
            if (!in_array($productId, $_SESSION['wishlist'])) {
                $_SESSION['wishlist'][] = $productId;
            }
        } elseif ($action === 'remove') {
            $index = array_search($productId, $_SESSION['wishlist']);
            if ($index !== false) {
                unset($_SESSION['wishlist'][$index]);
                $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
            }
        }
    }
}
header('Location: ../wishlist.php');
exit();

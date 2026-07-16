<?php
require_once __DIR__ . '/session.php';

function isLoggedIn(): bool {
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

function getLoggedInUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        // Find relative path to root for login redirect
        // Since we consolidated root frontend pages to root, login is at /login.php
        // If they are in a subfolder like /admin/, we redirect to ../login.php
        $script = $_SERVER['SCRIPT_NAME'];
        if (str_contains($script, '/admin/')) {
            header('Location: ../login.php');
        } else {
            header('Location: login.php');
        }
        exit();
    }
}

function requireRole(string $role): void {
    requireLogin();
    $user = getLoggedInUser();
    if ($user['role'] !== $role) {
        if ($role === 'admin') {
            // Non-admin trying to access admin page
            header('Location: ../index.php');
        } else {
            header('Location: index.php');
        }
        exit();
    }
}

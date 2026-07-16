<?php

class Session {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function setSession(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public function getSession(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function sessionHas(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public function removeFromSession(string $key): void {
        if ($this->sessionHas($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function login(User $user): void {
        $this->setSession('user_id', $user->id);
        $this->setSession('user_name', $user->name);
        $this->setSession('user_role', $user->role);
        $this->setSession('is_logged_in', true);
    }

    public function logout(): void {
        session_unset();
        session_destroy();
  
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
    }

    public function isLoggedIn(): bool {
        return $this->getSession('is_logged_in', false);
    }

    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }

    public function requireRole(string $role): void {
        $this->requireLogin();
        if ($this->getSession('user_role') !== $role) {
            header('Location: index.php');
            exit();
        }
    }
}
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $conn = getDatabaseConnection();
        
        // Auto-seed admin account if missing
        $adminEmail = 'admin@freshrescue.com';
        $checkAdmin = $conn->prepare("SELECT id FROM `user` WHERE `email` = ?");
        $checkAdmin->bind_param("s", $adminEmail);
        $checkAdmin->execute();
        $checkAdmin->store_result();
        if ($checkAdmin->num_rows == 0) {
            $adminPass = password_hash('Admin@123', PASSWORD_DEFAULT);
            $adminName = 'System Administrator';
            $adminRole = 'admin';
            $seedAdmin = $conn->prepare("INSERT INTO `user` (`name`, `email`, `password`, `role`) VALUES (?, ?, ?, ?)");
            $seedAdmin->bind_param("ssss", $adminName, $adminEmail, $adminPass, $adminRole);
            $seedAdmin->execute();
            $seedAdmin->close();
        }
        $checkAdmin->close();
        
        // Retrieve user
        $stmt = $conn->prepare("SELECT `id`, `name`, `password`, `role` FROM `user` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['is_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    header('Location: ../index.php');
                }
                exit();
            }
        }
        
        $_SESSION['login_error'] = "Invalid email or password!";
        header('Location: ../login.php');
        exit();
    } else {
        $_SESSION['login_error'] = "All fields are required!";
        header('Location: ../login.php');
        exit();
    }
} else {
    header('Location: ../login.php');
    exit();
}

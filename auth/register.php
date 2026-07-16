<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($name) && !empty($email) && !empty($phone) && !empty($address) && !empty($password)) {
        $conn = getDatabaseConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM `user` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $_SESSION['register_error'] = "Email already registered!";
            header('Location: ../register.php');
            exit();
        }
        $stmt->close();
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';
        
        // Insert new user
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO `user` (`name`, `email`, `password`, `role`) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
            $stmt->execute();
            $userId = $conn->insert_id;
            $stmt->close();
            
            // Insert phone
            $phoneStmt = $conn->prepare("INSERT INTO `user_phones` (`user_id`, `phone`) VALUES (?, ?)");
            $phoneStmt->bind_param("is", $userId, $phone);
            $phoneStmt->execute();
            $phoneStmt->close();
            
            // Insert address
            $addrStmt = $conn->prepare("INSERT INTO `user_addresses` (`user_id`, `address`) VALUES (?, ?)");
            $addrStmt->bind_param("is", $userId, $address);
            $addrStmt->execute();
            $addrStmt->close();
            
            $conn->commit();
            
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            
            header('Location: ../index.php');
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['register_error'] = "Registration failed. Please try again.";
            header('Location: ../register.php');
            exit();
        }
    } else {
        $_SESSION['register_error'] = "All fields are required!";
        header('Location: ../register.php');
        exit();
    }
} else {
    header('Location: ../register.php');
    exit();
}

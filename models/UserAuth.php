<?php

class UserAuth {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function register(string $name, string $email, string $phone, string $address, string $password): array {
        // Normalize input
        $name = trim($name);
        $email = trim(strtolower($email));
        $phone = trim($phone);
        $address = trim($address);

        // Simple validation
        if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        // Check if email already exists
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'This email address is already registered.'];
        }

        try {
            $this->db->beginTransaction();

            // 1. Insert into 'user' table
            $stmt = $this->db->prepare("INSERT INTO `user` (`name`, `email`, `password`, `role`) VALUES (:name, :email, :password, 'user')");
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            $userId = (int)$this->db->lastInsertId();

            // 2. Insert into 'user_phones' table
            $stmt = $this->db->prepare("INSERT INTO `user_phones` (`user_id`, `phone`) VALUES (:user_id, :phone)");
            $stmt->execute([
                ':user_id' => $userId,
                ':phone' => $phone
            ]);

            // 3. Insert into 'user_addresses' table
            $stmt = $this->db->prepare("INSERT INTO `user_addresses` (`user_id`, `address`) VALUES (:user_id, :address)");
            $stmt->execute([
                ':user_id' => $userId,
                ':address' => $address
            ]);

            $this->db->commit();
            return ['success' => true, 'user_id' => $userId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
        }
    }

    /**
     * Authenticates a user and returns a User object.
     * 
     * @param string $email
     * @param string $password
     * @return array Array with success status, message, and/or User object.
     */
    public function login(string $email, string $password): array {
        $email = trim(strtolower($email));
        
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Please enter both email and password.'];
        }

        try {
            // Find user by email
            $stmt = $this->db->prepare("SELECT * FROM `user` WHERE `email` = :email");
            $stmt->execute([':email' => $email]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userRow || !password_verify($password, $userRow['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }

            // Instantiate the User model object
            $user = new User(
                (int)$userRow['id'],
                $userRow['name'],
                $userRow['email'],
                $userRow['role'] ?? 'user',
                $userRow['created_at']
            );

            // Fetch and load phones
            $stmt = $this->db->prepare("SELECT `phone` FROM `user_phones` WHERE `user_id` = :user_id");
            $stmt->execute([':user_id' => $user->id]);
            $phones = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($phones as $phone) {
                $user->addPhone($phone);
            }

            // Fetch and load addresses
            $stmt = $this->db->prepare("SELECT `address` FROM `user_addresses` WHERE `user_id` = :user_id");
            $stmt->execute([':user_id' => $user->id]);
            $addresses = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($addresses as $address) {
                $user->addAddress($address);
            }

            return [
                'success' => true,
                'user' => $user
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Authentication error: ' . $e->getMessage()];
        }
    }

    /**
     * Checks if email exists in 'user' table.
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `user` WHERE `email` = :email");
        $stmt->execute([':email' => strtolower(trim($email))]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

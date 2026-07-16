<?php

class Database {
    private string $host = "localhost";
    private string $db_name = "e-commerce"; 
    private string $username = "root";           
    private string $password = "";               
    public ?PDO $conn = null;

    public function getConnection(): ?PDO {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch (PDOException $exception) {
            echo "Connection Error" . $exception->getMessage();
        }

        return $this->conn;
    }
}
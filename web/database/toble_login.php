<?php
require_once 'connect.php';
Class Table_get
{
    private $conn;
    // Change from private to public constructor
    public function __construct()
    {
        $this->conn = new database();
    }
    
    public function Getlogin($identifier)
    {
        // Assuming you have an 'email' column in your tb_login table
        $sql = "SELECT username, password, email FROM users WHERE username = :identifier OR email = :identifier";
        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null; // Return null if no user found
    }
    
    // Check if username or email already exists
    public function checkUserExists($username, $email)
    {
        $sql = "SELECT * FROM users WHERE (username = :username OR email = :email)";
        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        if ($stmt->execute()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? true : false; // Return true if user exists, false otherwise
        } else {
            return false;
        }
        
    }
    
    
    // Register a new user
    public function registerUser($username, $email, $password)
    {
        try {
            $sql = "INSERT INTO users (username, email, password) 
                    VALUES (:username, :email, :password)";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false; // Return false if there is an error
        }
    }
}
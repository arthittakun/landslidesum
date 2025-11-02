<?php
class database{
       private $host = "thsv3.hostatom.com";
    private $user = "docdagco_landslide";
    private $password = "PDvuYBJCEU25dZxsmft7";
    private $db = "docdagco_landslide";

    // private $host = "da94.hostneverdie.com";
    // private $user = "landslid_deploy";
    // private $password = "RJucKr6mRBrH4beTzUtQ";
    // private $db = "	landslid_db";
    
    // private $host = "thsv3.hostatom.com";
    // private $user = "docdagco_landslide";
    // private $password = "PDvuYBJCEU25dZxsmft7";
    // private $db = "docdagco_landslide";
    private $conn;

    public function __construct(){
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8";
            $this->conn = new PDO($dsn, $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection(){
        return $this->conn;
    }
    
    public function closeConnection(){
        $this->conn = null;
    }
    
    public function __destruct(){
        $this->closeConnection();
    }
}
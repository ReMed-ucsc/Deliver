<?php
class Database{
    private $host = '192.168.1.20';
    private $db_name = 'test';
    private $username = 'hiruna';
    private $password = 'password';
    public $conn;

    public function connect(){
        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name , $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}

//uses PDO (php data objetc) database connection
//return new instance every time requested
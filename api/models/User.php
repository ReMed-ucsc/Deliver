<?php
class User {
    private $conn;
    private $table = 'users';
    private $table_comments = 'comments';

    public $id;
    public $email;
    public $password;
    public $name;
    public $apiKey;
    public $comment;
    public $deliId;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register(){
        $query = "SELECT * FROM " .$this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0){
            return [
                "status" => "error",
                "message" => "user already exist"
            ];
        }

        //email doesn't exixt
        $query = "INSERT INTO " .$this->table . " SET email = :email, password = :password, name = :name, apiKey = :apiKey";
        $this->apiKey = bin2hex(random_bytes(16));

        $stmt = $this->conn->prepare($query);
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':apiKey', $this->apiKey);
        $stmt->execute(); 

        return [
            "status" => "success"
        ];
    }

    public function login(){
        $query = "SELECT id, password, apiKey FROM " . $this->table . " WHERE email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);

        if($stmt->execute() && $stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if(password_verify($this->password, $row['password'])){
                return [
                    'status' => 'success',
                    'id' => $row['id'],
                    'api_key' => $row['apiKey']
                ];
            }
        }
        return false;
    }

    public function getDriversToken(){
        $query = "SELECT fcmToken, driverId FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        if($stmt->execute()){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }

    public function addComment(){
        $query = "SELECT apiKey, FROM " .$this->table_comments . " WHERE apiKey = :apiKey";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":apiKey", $this->apiKey);

        if($stmt->execute() && $stmt->rowCount() > 0){
            $query = "INSERT INTO " .$this->table_comments . " SET userId = :userId, deliveryId = :deliveryId, comment = :comment";

            $stmt->bindParam(':userId', $this->id);
            $stmt->bindParam(':deliveryId', $this->deliId);
            $stmt->bindParam(':comment', $this->comment);

            $stmt->execute();

            return[
                "status" => "success"
            ];
        }else{
            return[
                "status" => "error",
                "message" => "invalid API KEY"
            ];
        }
    }
}
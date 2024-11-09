<?php
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/../utils/JWTHandler.php';

class AuthController {
    private $db;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
    }

    public function register($data){
        if(!empty($data['email']) && !empty($data['password']) && !empty($data['name'])){
            $this->user->email = $data['email'];
            $this->user->password = $data['password'];
            $this->user->name = $data['name'];

            return $this->user->register();
        }else{
            return [
                "status" => "error",
                "message" => "all fields are required"
            ];
        }
        
    }

    public function login($data){
        $this->user->email = $data->email;
        $this->user->password = $data->password;

        $userDetails = $this->user->login();

        if($userDetails){
            $token = JWTHandler::encode(['$id' => $userDetails['id']]);
            return [
                'token' => $token,
                'apiKey' => $userDetails['api_key'],
                'id' => $userDetails['id']
            ];
        }
        return null;
    }
}
<?php
header("Content-Type: application/json");
include_once 'controllers/AuthController.php';

$auth = new AuthController();
$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode(["error" => "Request error", "raw_input" => file_get_contents("php://input")]);
    exit;
}else

$result = $auth->register($data);

if($result["status"] != "error"){
    echo json_encode(["message" => "User regsitered successfully"]);
}else{
    echo json_encode([
        "message" => "User registration failed",
        "error" => $result["message"]
    ]);
}
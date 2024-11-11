<?php
header("Content-Type: application/json");
include_once 'controllers/AuthController.php';

$auth = new AuthController();
$data = json_decode(file_get_contents("php://input"));
$response = $auth->login($data);

if($response){
    echo json_encode([
        "message" => "User regsitered successfully",
        "data" => $response
    ]);
}else{
    echo json_encode(["message" => "User registration failed"]);
}

/*
    Response architecture
    
    'Message' :
    'data':
        'token:
        'apiKey':
        'id':
*/
<?php

include_once __DIR__ . '/../controllers/commentController.php';

$commentController = new CommentController();

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$path = str_replace('api/comment', '', $requestUri);

$inputData = json_decode(file_get_contents('php://input'), true) ?: $_REQUEST;

if($requestMethod === 'POST') {
    if($path === '/add') {
        $reponse = $commentController->addNewComment($inputData);
    } elseif ($path === '/update') {
        $reponse = $commentController->updateExistingComment($inputData);
    } elseif ($path == '/delete') {
        $reponse = $commentController->deleteExistingComment($inputData);
    }
} elseif ($requestMethod == 'GET') {
    if ($path === '/get') {
        if(isset($_GET['driverId'])) {
            $driverId = $_GET['driverId'];
            $reponse = $commentController->getCommentsOfDriver(['driverId' => $driverId]);
        }else{
            $reponse = [
                "status" => "error",
                "message" => "driverId id required"
            ];
        }
    }
    $reponse = [
        "status" => "error",
        "message" => "invalid endpoint"
    ];
} else{
    $reponse = [
        "status" => "error",
        "message" => "request method invalid"
    ];
}

header('content-Type: application/json');
echo json_encode($reponse);
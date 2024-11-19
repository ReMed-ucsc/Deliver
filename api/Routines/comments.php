<?php

include_once __DIR__ . '/../controllers/commentController.php';

$commentController = new CommentController();

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$response = "";

$path = str_replace('/api/comment', '', parse_url($requestUri, PHP_URL_PATH));

$inputData = json_decode(file_get_contents('php://input'), true) ?: $_REQUEST;

if ($requestMethod === 'POST') {
    if ($path === '/add') {
        $response = $commentController->addNewComment($inputData);
    } elseif ($path === '/update') {
        $response = $commentController->updateExistingComment($inputData);
    } elseif ($path === '/delete') {
        $response = $commentController->deleteExistingComment($inputData);
    } else {
        $response = [
            "status" => "error",
            "message" => "Invalid POST endpoint"
        ];
    }
} elseif ($requestMethod === 'GET') {
    if ($path === '/get') {
        if (isset($_GET['driverId'])) {
            $driverId = $_GET['driverId'];
            $response = $commentController->getCommentsOfDriver(['driverId' => $driverId]);
        } else {
            $response = [
                "status" => "error",
                "message" => "driverId is required"
            ];
        }
    } else if($path === '/getone') {
        if(isset($_GET['commentId'])){
            $commentId = $_GET['commentId'];
            $response = $commentController->getOneComment(['commentId' => $commentId]);
        } else {
            $response = [
                "status" => "error",
                "message" => "commentId is required"
            ];
        }

    }else {
        $response = [
            "status" => "error",
            "message" => "Invalid GET endpoint"
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid request method"
    ];
}

header('Content-Type: application/json');
echo json_encode($response);

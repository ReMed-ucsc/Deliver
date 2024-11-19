<?php

include_once __DIR__ . '/../controllers/deliveryController.php';

$deliveryController = new DeliveryController();

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$response = "";

$path = str_replace('/api/delivery', '', parse_url($requestUri, PHP_URL_PATH));

$inputData = json_decode(file_get_contents('php://input'), true) ?: $_REQUEST;

if ($requestMethod === 'POST') {
    if ($path === '/confirm') {
        $response = $deliveryController->acceptDelivery($inputData);
    } elseif ($path === '/update') {
        //$response = $commentController->updateExistingComment($inputData);
    } elseif ($path === '/delete') {
        //$response = $commentController->deleteExistingComment($inputData);
    } else {
        $response = [
            "status" => "error",
            "message" => "Invalid POST endpoint"
        ];
    }
} elseif ($requestMethod === 'GET') {
    if ($path === '/get') {
        return[
            "status" => "success",
            "message" => "enpoint /api/delivery/get"
        ];
    } else if($path === '/getone') {
        if(isset($_GET['commentId'])){
            //$commentId = $_GET['commentId'];
            //$response = $commentController->getOneComment(['commentId' => $commentId]);
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

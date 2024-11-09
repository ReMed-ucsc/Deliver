<?php

header("Content-Type: application/json");

header("Access-Control-Allow-Origins: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$requestUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch($requestUrl) {
    case '/api/login':
        require __DIR__ . '/login.php';
        break;

    case '/api/register':
        require __DIR__ . '/register.php';
        break;

    case '/api/logout':
        require __DIR__ . '/logout.php';
        break;

    case '/api/test':
        echo json_encode(["message" => "request receiverd"]);
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found yako"]);
        break;
}
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$path = $_GET['endpoint'] ?? '';

switch ($path) {
    case 'login':
        require 'api/auth.php';
        break;

    case 'products':
        require 'api/products.php';
        break;

    case 'categories':
        require 'api/categories.php';
        break;

    case 'dashboard':
        require 'api/dashboard.php';
        break;    
    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint no encontrado"]);
}

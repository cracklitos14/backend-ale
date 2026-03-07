<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

$sql = "UPDATE productos SET estado = 0 WHERE id_producto = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);

echo json_encode(['success' => true]);
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

$nombre = $data['name'] ?? '';
$precio = $data['price'] ?? 0;
$stock  = $data['stock'] ?? 0;
$id_categoria = $data['category_id'] ?? null;
$codigo_barras = $data['codigo_barras'] ?? '';

if (!$nombre || !$id_categoria) {
  echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
  exit;
}

$sql = "INSERT INTO productos (nombre, precio, id_categoria, estado, codigo_barras)
        VALUES (:nombre, :precio, :categoria, 1, :codigo_barras)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':nombre' => $nombre,
  ':precio' => $precio,
  ':categoria' => $id_categoria,
  ':codigo_barras' => $codigo_barras
 
]);

$id_producto = $pdo->lastInsertId();

$sqlStock = "INSERT INTO inventario (id_producto, stock)
             VALUES (:id, :stock)";

$stmt2 = $pdo->prepare($sqlStock);
$stmt2->execute([
  ':id' => $id_producto,
  ':stock' => $stock
]);

echo json_encode(['success' => true]);
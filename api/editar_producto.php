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

$id             = $data['id'] ?? null;
$nombre         = $data['name'] ?? '';
$precio         = $data['price'] ?? 0;
$stock          = $data['stock'] ?? 0;
$idCategoria    = $data['category_id'] ?? null;
$codigo_barras  = $data['codigo_barras'] ?? '';

if (!$id || !$nombre || !$idCategoria) {
  echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
  exit;
}

// 🔥 ACTUALIZAR PRODUCTO (YA CON CÓDIGO DE BARRAS)
$sql = "UPDATE productos 
        SET nombre = :nombre,
            precio = :precio,
            id_categoria = :categoria,
            codigo_barras = :codigo_barras
        WHERE id_producto = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':nombre' => $nombre,
  ':precio' => $precio,
  ':categoria' => $idCategoria,
  ':codigo_barras' => $codigo_barras,
  ':id' => $id
]);

// 🔄 ACTUALIZAR STOCK
$sqlStock = "UPDATE inventario 
             SET stock = :stock 
             WHERE id_producto = :id";

$stmt2 = $pdo->prepare($sqlStock);
$stmt2->execute([
  ':stock' => $stock,
  ':id' => $id
]);

echo json_encode(['success' => true]);
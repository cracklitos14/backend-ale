<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['nombre'])) {
    $sql = "INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $data['nombre'],
        ':descripcion' => $data['descripcion'] ?? null
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Categoría creada']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nombre requerido']);
}
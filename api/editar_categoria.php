<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id_categoria']) && !empty($data['nombre'])) {
    $sql = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $data['nombre'],
        ':descripcion' => $data['descripcion'] ?? null,
        ':id' => $data['id_categoria']
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Categoría actualizada']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
}
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id_categoria'])) {
    $sql = "DELETE FROM categorias WHERE id_categoria = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $data['id_categoria']]);

    echo json_encode(['status' => 'success', 'message' => 'Categoría eliminada']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
}
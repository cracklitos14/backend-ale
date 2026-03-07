<?php
require __DIR__ . '/../config/database.php';


$data = json_decode(file_get_contents("php://input"), true);

$usuario  = $data['usuario'] ?? '';
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("
    SELECT id_usuario, nombre, usuario, password, rol
    FROM usuarios
    WHERE usuario = ? AND estado = 1
");
$stmt->execute([$usuario]);

$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    exit;
}

/*
DESPUÉS  a password_hash().
*/


if ($password !== $user['password']) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => [
        "id"   => $user['id_usuario'],
        "name" => $user['nombre'],
        "role" => $user['rol']
    ]
]);

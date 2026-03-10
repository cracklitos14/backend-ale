<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
require '../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
date_default_timezone_set("America/Mexico_City");

$items = $data['items'];
$total = $data['total'];
$id_usuario = $data['id_usuario'];

try {
    $pdo->beginTransaction();

    // 1️⃣ insertar venta
    $stmt = $pdo->prepare("
        INSERT INTO ventas (fecha, total, id_usuario, metodo_pago)
        VALUES (NOW(), ?, ?, ?)
    ");
    $stmt->execute([$total, $id_usuario,$data['metodo_pago'] 
]);
    $id_venta = $pdo->lastInsertId();

    // 2️⃣ insertar detalle y descontar stock
    foreach ($items as $item) {

        // detalle_venta
        $stmt = $pdo->prepare("
            INSERT INTO detalle_venta (id_venta, id_producto, cantidad, subtotal)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_venta,
            $item['id'],
            $item['qty'],
            $item['price'] * $item['qty']
        ]);

        // inventario
        $stmt = $pdo->prepare("
            UPDATE inventario
            SET stock = stock - ?
            WHERE id_producto = ?
        ");
        $stmt->execute([$item['qty'], $item['id']]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'id_venta' => $id_venta
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
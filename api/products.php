<?php
// CORS (OBLIGATORIO para Angular)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../config/database.php';

$sql = "
    SELECT
        p.id_producto   AS id,
        p.nombre        AS name,
        p.precio        AS price,
        IFNULL(i.stock, 0) AS stock,
        c.nombre        AS category,
        p.id_categoria  AS category_id,
        p.codigo_barras AS codigo_barras
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
    LEFT JOIN inventario i ON p.id_producto = i.id_producto
    WHERE p.estado = 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
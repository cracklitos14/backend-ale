<?php
date_default_timezone_set("America/Mexico_City");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'cors.php';
require __DIR__ . '/../config/database.php';

try {

$inicio = date("Y-m-d 00:00:00");
$fin = date("Y-m-d 23:59:59");

/* ventas hoy */

$stmt = $pdo->prepare("
SELECT IFNULL(SUM(total),0)
FROM ventas
WHERE fecha BETWEEN ? AND ?
");

$stmt->execute([$inicio,$fin]);
$ventasHoy = $stmt->fetchColumn();

/* productos vendidos */

$stmt = $pdo->prepare("
SELECT IFNULL(SUM(dv.cantidad),0)
FROM detalle_venta dv
INNER JOIN ventas v ON v.id_venta = dv.id_venta
WHERE v.fecha BETWEEN ? AND ?
");

$stmt->execute([$inicio,$fin]);
$productosVendidos = $stmt->fetchColumn();

/* stock */

$stmt = $pdo->query("
SELECT SUM(i.stock)
FROM inventario i
INNER JOIN productos p ON p.id_producto = i.id_producto
WHERE p.estado = 1
");

$stock = $stmt->fetchColumn();

/* categorias */

$stmt = $pdo->query("
SELECT COUNT(*)
FROM categorias
");

$categorias = $stmt->fetchColumn();

/* 🔥 actividad reciente CORREGIDA */

$stmt = $pdo->query("
SELECT
DATE_FORMAT(v.fecha,'%H:%i') as hora,
p.nombre,
dv.subtotal
FROM detalle_venta dv
INNER JOIN ventas v ON v.id_venta = dv.id_venta
INNER JOIN productos p ON p.id_producto = dv.id_producto
ORDER BY dv.id_detalle DESC
LIMIT 5
");

$actividad = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ultima venta */

$stmt = $pdo->query("
SELECT
DATE_FORMAT(fecha,'%H:%i') as hora,
total
FROM ventas
ORDER BY id_venta DESC
LIMIT 1
");

$ultimaVenta = $stmt->fetch(PDO::FETCH_ASSOC);

/* grafica ventas del dia */

$stmt = $pdo->prepare("
SELECT
DATE_FORMAT(fecha,'%H:00') as hora,
SUM(total) as total
FROM ventas
WHERE fecha BETWEEN ? AND ?
GROUP BY hora
ORDER BY hora
");

$stmt->execute([$inicio,$fin]);

$ventasGrafica = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* respuesta */

echo json_encode([
"ventasHoy"=>$ventasHoy,
"productosVendidos"=>$productosVendidos,
"stock"=>$stock,
"categorias"=>$categorias,
"actividad"=>$actividad,
"ultimaVenta"=>$ultimaVenta,
"ventasGrafica"=>$ventasGrafica
]);

}catch(Exception $e){

echo json_encode([
"ventasHoy"=>0,
"productosVendidos"=>0,
"stock"=>0,
"categorias"=>0,
"actividad"=>[],
"ultimaVenta"=>null,
"ventasGrafica"=>[]
]);

}
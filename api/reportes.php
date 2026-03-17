<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../config/database.php';

$fechaInicio = $_GET['fechaInicio'] ?? null;
$fechaFin = $_GET['fechaFin'] ?? null;

if (!$fechaInicio || !$fechaFin) {
    http_response_code(400);
    echo json_encode(["error"=>"Debe proporcionar fechaInicio y fechaFin"]);
    exit;
}

if ($fechaFin < $fechaInicio) {
    http_response_code(400);
    echo json_encode(["error"=>"La fecha de fin no puede ser menor que la fecha de inicio"]);
    exit;
}

$hoy = date("Y-m-d");

if ($fechaInicio > $hoy || $fechaFin > $hoy) {
    http_response_code(400);
    echo json_encode(["error"=>"No puedes seleccionar fechas futuras"]);
    exit;
}

try {

$sqlIngresos = "
SELECT SUM(total) 
FROM ventas
WHERE fecha BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sqlIngresos);
$stmt->execute([
"inicio"=>$fechaInicio." 00:00:00",
"fin"=>$fechaFin." 23:59:59"
]);

$ingresosTotales = $stmt->fetchColumn() ?? 0;


$sqlProductos = "
SELECT p.nombre,
SUM(d.cantidad) unidades,
SUM(d.subtotal) ingresos
FROM detalle_venta d
INNER JOIN productos p ON p.id_producto=d.id_producto
INNER JOIN ventas v ON v.id_venta=d.id_venta
WHERE v.fecha BETWEEN :inicio AND :fin
GROUP BY p.id_producto,p.nombre
";

$stmt = $pdo->prepare($sqlProductos);
$stmt->execute([
"inicio"=>$fechaInicio." 00:00:00",
"fin"=>$fechaFin." 23:59:59"
]);

$productosVendidos = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sqlAgotados = "
SELECT p.nombre,i.stock,i.stock_minimo
FROM inventario i
INNER JOIN productos p ON p.id_producto=i.id_producto
WHERE i.stock = 0
";

$stmt = $pdo->query($sqlAgotados);

$productosAgotados = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sqlStockBajo = "
SELECT p.nombre,i.stock,i.stock_minimo
FROM inventario i
INNER JOIN productos p ON p.id_producto=i.id_producto
WHERE i.stock > 0
AND i.stock <= IFNULL(i.stock_minimo,5)
";

$stmt = $pdo->query($sqlStockBajo);

$productosStockBajo = $stmt->fetchAll(PDO::FETCH_ASSOC);


$mensajeAlertas = (count($productosAgotados)===0 && count($productosStockBajo)===0)
? "Todos los productos tienen stock suficiente"
: null;


echo json_encode([
"ingresosTotales"=>$ingresosTotales,
"productosAgotados"=>$productosAgotados,
"productosStockBajo"=>$productosStockBajo,
"mensajeAlertas"=>$mensajeAlertas,
"ventasPorMetodo"=>[],
"productosVendidos"=>$productosVendidos
]);

}
catch(Exception $e){

http_response_code(500);

echo json_encode([
"error"=>$e->getMessage()
]);

}
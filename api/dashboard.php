<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Manejo de preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'cors.php';
require __DIR__ . '/../config/database.php';

// 🔹 Ajuste de zona horaria
date_default_timezone_set("America/Mexico_City");

try {
  // 🔹 Rango del día actual
  $inicio = date("Y-m-d 00:00:00");
  $fin    = date("Y-m-d 23:59:59");

  // Ventas del día
  $stmt = $pdo->prepare("
    SELECT IFNULL(SUM(total),0) 
    FROM ventas 
    WHERE fecha BETWEEN ? AND ?
  ");
  $stmt->execute([$inicio, $fin]);
  $ventasHoy = $stmt->fetchColumn();

  // Productos vendidos hoy
  $stmt = $pdo->prepare("
    SELECT IFNULL(SUM(dv.cantidad),0)
    FROM detalle_venta dv
    INNER JOIN ventas v ON v.id_venta = dv.id_venta
    WHERE v.fecha BETWEEN ? AND ?
  ");
  $stmt->execute([$inicio, $fin]);
  $productosVendidos = $stmt->fetchColumn();

  // Stock total
  $stmt = $pdo->query("
    SELECT SUM(i.stock) AS total_stock
    FROM inventario i
    INNER JOIN productos p 
      ON i.id_producto = p.id_producto
    WHERE p.estado = 1
  ");
  $stock = $stmt->fetchColumn();

  // Categorías
  $stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM categorias
  ");
  $categorias = $stmt->fetchColumn();

  // Actividad reciente
  $stmt = $pdo->query("
    SELECT 
      DATE_FORMAT(v.fecha, '%H:%i') AS hora,
      p.nombre,
      dv.subtotal
    FROM detalle_venta dv
    INNER JOIN ventas v ON v.id_venta = dv.id_venta
    INNER JOIN productos p ON p.id_producto = dv.id_producto
    ORDER BY v.fecha DESC
    LIMIT 5
  ");
  $actividad = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 🔹 Respuesta JSON
  echo json_encode([
    "ventasHoy" => $ventasHoy,
    "productosVendidos" => $productosVendidos,
    "stock" => $stock,
    "categorias" => $categorias,
    "actividad" => $actividad
  ]);

} catch (Exception $e) {
  // Fallback seguro
  echo json_encode([
    "ventasHoy" => 0,
    "productosVendidos" => 0,
    "stock" => 0,
    "categorias" => 0,
    "actividad" => []
  ]);
}

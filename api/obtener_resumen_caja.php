<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require '../config/database.php';

$id_usuario = $_GET['id_usuario'] ?? 0;

$stmt = $pdo->prepare("
  SELECT
    IFNULL(SUM(total),0) AS total_ventas,
    IFNULL(SUM(CASE WHEN metodo_pago='efectivo' THEN total END),0) AS total_efectivo,
    IFNULL(SUM(CASE WHEN metodo_pago='tarjeta' THEN total END),0) AS total_tarjeta
  FROM ventas
  WHERE cerrada = 0
  AND id_usuario = ?
");

$stmt->execute([$id_usuario]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
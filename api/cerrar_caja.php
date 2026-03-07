<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$id_usuario = $data['id_usuario'];
$observaciones = $data['observaciones'] ?? '';
$efectivo_contado = $data['efectivo_contado'];

$pdo->beginTransaction();

/* Totales */
$stmt = $pdo->prepare("
  SELECT
    IFNULL(SUM(total),0) AS total_ventas,
    IFNULL(SUM(CASE WHEN metodo_pago='efectivo' THEN total END),0) AS total_efectivo,
    IFNULL(SUM(CASE WHEN metodo_pago='tarjeta' THEN total END),0) AS total_tarjeta
  FROM ventas
  WHERE cerrada = 0 AND id_usuario = ?
");
$stmt->execute([$id_usuario]);
$resumen = $stmt->fetch(PDO::FETCH_ASSOC);

$diferencia = $efectivo_contado - $resumen['total_efectivo'];

/* Guardar cierre */
$insert = $pdo->prepare("
INSERT INTO cierres_caja
(fecha, id_usuario, total_ventas, total_efectivo, total_tarjeta, efectivo_contado, diferencia, observaciones)
VALUES (NOW(),?,?,?,?,?,?,?)
");
$insert->execute([
  $id_usuario,
  $resumen['total_ventas'],
  $resumen['total_efectivo'],
  $resumen['total_tarjeta'],
  $efectivo_contado,
  $diferencia,
  $observaciones
]);

/* Cerrar ventas */
$update = $pdo->prepare("
UPDATE ventas SET cerrada = 1 WHERE cerrada = 0 AND id_usuario = ?
");
$update->execute([$id_usuario]);

$pdo->commit();

echo json_encode([
  'success' => true,
  'message' => 'Caja cerrada correctamente'
]);
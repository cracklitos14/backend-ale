<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../config/database.php';

$sql = "SELECT 
  c.id_categoria AS id,
  c.nombre,
  c.descripcion,
  COUNT(p.id_producto) AS productos
FROM categorias c
LEFT JOIN productos p 
  ON p.id_categoria = c.id_categoria 
  AND p.estado = 1   -- 🔹 solo productos activos
GROUP BY c.id_categoria, c.nombre, c.descripcion";

$stmt = $pdo->prepare($sql);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
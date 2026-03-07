<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");

require '../config/database.php';

$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin'] ?? null;

$sql = "SELECT * FROM cierres_caja WHERE 1=1";
$params = [];

if ($inicio && $fin) {
  $sql .= " AND DATE(fecha) BETWEEN ? AND ?";
  $params[] = $inicio;
  $params[] = $fin;
}

$sql .= " ORDER BY fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
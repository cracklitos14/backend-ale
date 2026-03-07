<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../config/database.php';

$sql = "SELECT
  id_categoria AS id,
  nombre,
  descripcion
FROM categorias";


$stmt = $pdo->prepare($sql);
$stmt->execute();


echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
<?php
include_once __DIR__ . '/../../../Controller/PlaningController.php';
include_once __DIR__ . '/../../../Model/planing.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$note = $data['note'];

$db = config::getConnexion();

$sql = "UPDATE planing SET note = ? WHERE id_pl = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$note, $id]);

echo "OK";
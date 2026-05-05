<?php
session_start();
include_once __DIR__ . '/../../../Controller/PlaningController.php';

$planC = new PlaningController();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) exit;

$db = config::getConnexion();

$sql = "SELECT p.*, c.titre_c 
        FROM planing p
        JOIN chapitre c ON p.id_c = c.id_c
        WHERE p.id_u = ?";

$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);

$data = [];

while ($row = $stmt->fetch()) {

    $today = date('Y-m-d');

    $color = ($row['date_pl'] < $today)
        ? '#dc3545'  // rouge
        : '#28a745'; // vert

    $data[] = [
        "id" => $row['id_pl'],
        "title" => $row['titre_c'],
        "start" => $row['date_pl'],
        "color" => $color,
        "note" => $row['note']
    ];
}

echo json_encode($data);
<?php
session_start();

include_once __DIR__ . '/../../../Controller/PlaningController.php';

header('Content-Type: application/json');

// 🔥 lire JSON envoyé par fetch
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(["status" => "error", "msg" => "ID manquant"]);
    exit;
}

$id = (int) $data['id'];

$planingC = new PlaningController();

try {
    $planingC->deletePlaning($id);
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
}
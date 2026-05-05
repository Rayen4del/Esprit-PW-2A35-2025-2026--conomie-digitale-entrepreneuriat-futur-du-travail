<?php
session_start();

include_once __DIR__ . '/../../../Controller/ChapitreController.php';
include_once __DIR__ . '/../../../Controller/PlaningController.php';
include_once __DIR__ . '/../../../Model/planing.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) exit;

$user_id = $_SESSION['user_id'] ?? null;
$formation_id = $data['formation_id'] ?? null;
$dates = $data['dates'] ?? [];

if (!$user_id || !$formation_id || empty($dates)) {
    exit("❌ Erreur données");
}

$chapitreC = new ChapitreController();
$planC = new PlaningController();

$chapitres = $chapitreC->listChapitresByFormation($formation_id) ?? [];

$index = 0;

foreach ($chapitres as $ch) {

    $date = $dates[$index % count($dates)];

    $p = new Planing(
        null,
        $user_id,
        $ch['id_c'],
        new DateTime($date),
        "Chapitre : " . $ch['titre_c']
    );

    $planC->addPlaning($p);

    $index++;
}

echo "OK";
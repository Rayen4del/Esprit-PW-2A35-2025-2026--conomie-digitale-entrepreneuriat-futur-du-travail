<?php

include_once(__DIR__ . "/../../../Controller/Chap_contenuController.php");

$controller = new ChapContenuController();

/* ================= DATA ================= */
$chapitre_id = $_POST['chapitre_id'] ?? null;
$blocks = $_POST['blocks'] ?? [];

/* ================= VALIDATION ================= */
if (!$chapitre_id || empty($blocks)) {
    exit("Données invalides");
}

/* ================= ORDRE ================= */
$baseOrder = $controller->getLastOrder($chapitre_id);

/* ================= LOOP BLOCKS ================= */
foreach ($blocks as $i => $b) {

    $type = $b['type'] ?? '';
    $contenu = $b['contenu'] ?? '';
    $ordre = $baseOrder + $i + 1;

    if ($type == '') continue;

    /* ================= CREATE OBJECT ================= */
    $chapContenu = new ChapContenu();

    $chapContenu->setChapitreId($chapitre_id);
    $chapContenu->setType($type);
    $chapContenu->setContenu($contenu); // 🔥 déjà URL venant du JS
    $chapContenu->setOrdre($ordre);

    /* ================= INSERT ================= */
    $controller->addContenu($chapContenu);
}

/* ================= RESPONSE ================= */
echo "OK";
<?php
include_once __DIR__ . '/../../../Controller/TestController.php';
include_once __DIR__ . '/../../../Model/test.php';

$testC = new TestController();

$id = $_POST['id_t'] ?? null;

if (!$id) {
    die("ID manquant");
}

// récupérer ancien test
$old = $testC->getTestById($id);

// valeurs fallback
$id_c = $_POST['id_c'] ?? $old['id_c'];
$id_f = $_POST['id_f'] ?? $old['id_f'];
$score_min = $_POST['score_min'] ?? $old['score_min'];
$date_creation = $_POST['date_creation'] ?? $old['date_creation'];

// créer objet test
$test = new Test(
    (int)$id,
    (int)$id_c,
    (int)$id_f,
    (int)$score_min,
    $date_creation
);

// update DB
$testC->updateTest($test);

// redirection
header("Location: consultertests.php?updated=1");
exit;
?>
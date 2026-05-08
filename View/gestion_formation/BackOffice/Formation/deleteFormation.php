<?php
include_once __DIR__ . '/../../../../Controller/FormationController.php';
;

$formationC = new FormationController();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // everything handled inside controller
    $formationC->deleteFormation($id);
}

header("Location: consulterformations.php?deleted=1");
exit;
?>
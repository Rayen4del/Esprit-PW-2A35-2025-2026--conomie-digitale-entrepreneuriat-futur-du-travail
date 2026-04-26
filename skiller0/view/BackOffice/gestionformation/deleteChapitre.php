<?php
include __DIR__ . '/../../../Controller/ChapitreController.php';

$chapitreC = new ChapitreController();

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    // (optionnel) récupérer le chapitre si besoin futur logique
    $chapitre = $chapitreC->getChapitreById($id);

    // suppression du chapitre
    $chapitreC->deleteChapitre($id);
}

header("Location: consulterchapitres.php?deleted=1");
exit;
?>
<?php
include_once __DIR__ . '/../../../Controller/ChapitreController.php';
include_once __DIR__ . '/../../../Model/chapitre.php';

$chapitreC = new ChapitreController();

$id = $_POST['id_c'] ?? null;

if (!$id) {
    die("ID manquant");
}

// récupérer ancien chapitre
$old = $chapitreC->getChapitreById($id);

// valeurs fallback
$titre_c = $_POST['titre_c'] ?? $old['titre_c'];
$ordre   = $_POST['ordre'] ?? $old['ordre'];
$id_f    = $_POST['id_f'] ?? $old['id_f'];

// créer objet chapitre
$chapitre = new Chapitre(
    (int)$id,
    (int)$id_f,
    $titre_c,
    (int)$ordre
);

// update DB
$chapitreC->updateChapitre($chapitre);

// redirection
header("Location: consulterchapitres.php?updated=1");
exit;
?>
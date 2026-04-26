<?php
include(__DIR__ . '/../../config.php');
include(__DIR__ . '/EvenementController.php');

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre       = trim($_POST['titre'] ?? '');
    $type        = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $dateEvent   = trim($_POST['dateEvent'] ?? '');
    $duree       = intval($_POST['duree'] ?? 0);
    $lieu_lien   = trim($_POST['lieu_lien'] ?? '');
    $statut      = trim($_POST['statut'] ?? '');
    $nbplaces    = intval($_POST['nbplaces'] ?? 0);

    $evenement = new Evenement($titre, $type, $description, $dateEvent, $duree, $lieu_lien, $statut, $nbplaces);

    $controller = new EvenementController();
    if ($controller->addEvenement($evenement)) {
        $success = true;
    } else {
        $errors[] = "Erreur lors de l'ajout de l'événement.";
    }
}

include(__DIR__ . '/../../view/evenement/html/form_evenement.php');
?>
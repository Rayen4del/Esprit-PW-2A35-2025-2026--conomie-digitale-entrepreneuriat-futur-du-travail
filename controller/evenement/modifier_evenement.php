<?php
include(__DIR__ . '/../../config.php');
include(__DIR__ . '/EvenementController.php');

$success = false;
$errors  = [];
$event   = null;

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: /projet/controller/evenement/lister_evenements.php');
    exit;
}

$controller = new EvenementController();
$event = $controller->getById($id);

if (!$event) {
    header('Location: /projet/controller/evenement/lister_evenements.php?error=not_found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre       = trim($_POST['titre'] ?? '');
    $type        = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $dateEvent   = trim($_POST['dateEvent'] ?? '');
    $duree       = intval($_POST['duree'] ?? 0);
    $lieu_lien   = trim($_POST['lieu_lien'] ?? '');
    $statut      = trim($_POST['statut'] ?? '');
    $nbplaces    = intval($_POST['nbplaces'] ?? 0);

    $evenement = new Evenement($titre, $type, $description, $dateEvent, $duree, $lieu_lien, $statut, $nbplaces, $id);

    if ($controller->updateEvenement($evenement, $id)) {
        header('Location: /projet/controller/evenement/lister_evenements.php?updated=1');
        exit;
    } else {
        $errors[] = "Erreur lors de la modification.";
    }
}

include(__DIR__ . '/../../view/evenement/html/liste_evenements.php');
?>
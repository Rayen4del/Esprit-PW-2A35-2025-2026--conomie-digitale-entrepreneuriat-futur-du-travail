<?php
include(__DIR__ . '/../../../config.php');
include(__DIR__ . '/EvenementController.php');

$search = trim($_GET['search'] ?? '');
$statut = trim($_GET['statut'] ?? '');
$type   = trim($_GET['type']   ?? '');

$controller = new EvenementController();
$events = $controller->filtrer($search, $statut, $type);

include(__DIR__ . '/../../view/evenement/html/frontoffice/liste_evenements.php');
?>
<?php

include_once __DIR__ . '/../../../../Controller/Chap_contenuController.php';

if (!isset($_GET['id'])) {
    die("ID manquant");
}

$id = $_GET['id'];

$controller = new ChapContenuController();
$controller->deleteContenu($id);

// redirection vers retour chapitre
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

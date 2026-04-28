<?php
include __DIR__ . '/../../config.php';
include __DIR__ . '/EvenementController.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $controller = new EvenementController();
    if ($controller->supprimer($id)) {
        header('Location: /projet/view/evenement/html/backoffice/backoffice_evenements.php?deleted=1');
    } else {
        header('Location: /projet/view/evenement/html/backoffice/backoffice_evenements.php?error=delete_failed');
    }
    exit;
} else {
    header('Location: /projet/view/evenement/html/backoffice/backoffice_evenements.php?error=invalid_id');
}
exit;
?>
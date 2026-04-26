<?php
include(__DIR__ . '/../../config.php');
include(__DIR__ . '/EvenementController.php');

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $controller = new EvenementController();
    if ($controller->deleteEvenement($id)) {
        header('Location: /projet/controller/evenement/lister_evenements.php?deleted=1');
    } else {
        header('Location: /projet/controller/evenement/lister_evenements.php?error=delete_failed');
    }
} else {
    header('Location: /projet/controller/evenement/lister_evenements.php?error=invalid_id');
}
exit;
?>
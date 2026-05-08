<?php
include __DIR__ . '/../../config.php';
include __DIR__ . '/EvenementController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids'])) {
    $ids = array_map('intval', explode(',', $_POST['ids']));
    
    $controller = new EvenementController();
    $deletedCount = 0;

    foreach ($ids as $id) {
        if ($id > 0 && $controller->supprimer($id)) {
            $deletedCount++;
        }
    }

    if ($deletedCount > 0) {
        header('Location: /projet/view/evenement/html/backoffice/backoffice_evenements.php?deleted=1');
    } else {
        header('Location: /projet/view/evenement/html/backoffice/backoffice_evenements.php?error=no_delete');
    }
    exit;
}

// If accessed directly
header('Location: /projet/view/evenement/html/backoffice/backoffice_evenements.php');
exit;
?>
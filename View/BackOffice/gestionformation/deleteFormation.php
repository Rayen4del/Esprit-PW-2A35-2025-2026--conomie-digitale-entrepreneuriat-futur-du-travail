<?php
include __DIR__ . '/../../../Controller/FormationController.php';

$formationC = new FormationController();

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $formation = $formationC->getFormationById($id);

    if ($formation && !empty($formation['image'])) {

        $imagePath = __DIR__ . '/../../../' . $formation['image'];

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $formationC->deleteFormation($id);
}

header("Location: consulterformations.php?deleted=1");
exit;
?>
<?php
include_once __DIR__ . '/../../../Controller/FormationController.php';
include_once __DIR__ . '/../../../Model/formation.php';

$formationC = new FormationController();

$id = $_POST['id_f'] ?? null;
if (!$id) {
    die("ID manquant");
}

$old = $formationC->getFormationById($id);
$imagePath = $old['image'];

$newImage = $imagePath;

if (!empty($_FILES['image']['name'])) {

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imgName = time() . '_' . uniqid() . '.' . $ext;

    $tmp = $_FILES['image']['tmp_name'];

    $folder = __DIR__ . "/../../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $newPath = "uploads/" . $imgName;

    move_uploaded_file($tmp, $folder . $imgName);

    $oldFullPath = __DIR__ . "/../../../" . $imagePath;
    if (file_exists($oldFullPath)) {
        unlink($oldFullPath);
    }

    $newImage = $newPath;
}


$formation = new Formation(
    (int)$id,
    $_POST['titre'] ?? $old['titre'],
    $_POST['description'] ?? $old['description'],
    isset($old['created_by']) ? (int)$old['created_by'] : 1,
    $_POST['nom_propr'] ?? $old['nom_propr'],
    isset($old['date_c']) ? new DateTime($old['date_c']) : new DateTime(),
    isset($old['evaluation']) ? (float)$old['evaluation'] : 0,
    $newImage,
    $_POST['etat'] ?? $old['etat']
);

$formationC->updateFormation($formation);

header("Location: consulterformations.php");
exit;
?>
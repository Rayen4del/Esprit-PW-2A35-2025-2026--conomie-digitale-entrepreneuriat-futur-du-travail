<?php
include_once __DIR__ . '/../../../Controller/FormationController.php';
include_once __DIR__ . '/../../../Model/formation.php';
include_once __DIR__ . '/../../../config.php';

$formationC = new FormationController();

// ================== CHECK ID ==================
$id = $_POST['id_f'] ?? null;
if (!$id) {
    die("ID manquant");
}

// ================== GET OLD DATA ==================
$old = $formationC->getFormationById($id);
if (!$old) {
    die("Formation introuvable");
}

$imagePath = $old['image'];
$newImage = $imagePath;

// ================== UPLOAD IMAGE ==================
if (!empty($_FILES['image']['name'])) {

    $file = $_FILES['image'];

    // ✅ Vérifier erreur upload
    if ($file['error'] !== 0) {
        die("Erreur lors de l'upload");
    }

    // ✅ Extension sécurisée
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        die("Format d'image non autorisé");
    }

    // ✅ Nom unique
    $imgName = time() . '_' . uniqid() . '.' . $ext;

    // ✅ Dossier upload (CONFIG)
    $folder = UPLOAD_DIR;

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // ✅ Upload fichier
    if (!move_uploaded_file($file['tmp_name'], $folder . $imgName)) {
        die("Erreur déplacement fichier");
    }

    // ✅ Supprimer ancienne image
    if (!empty($imagePath)) {
        $oldFullPath = UPLOAD_DIR . basename($imagePath);
        if (file_exists($oldFullPath)) {
            unlink($oldFullPath);
        }
    }

    // ✅ URL pour affichage
    $newImage =  $imgName;
}

// ================== UPDATE OBJECT ==================
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

// ================== SAVE ==================
$formationC->updateFormation($formation);

// ================== REDIRECT ==================
header("Location: interfacesuperutilisateur.php");
exit;
?>
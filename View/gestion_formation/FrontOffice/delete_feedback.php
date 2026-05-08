<?php
include_once '../../../config.php';
$pdo = config::getConnexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'];

    $sql = "DELETE FROM feedback WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header("Location: interfacesuperutilisateur.php");
    exit;
}
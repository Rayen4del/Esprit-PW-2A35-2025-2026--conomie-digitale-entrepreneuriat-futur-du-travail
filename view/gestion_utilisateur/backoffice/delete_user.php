<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        $userC = new UserController();
        $userC->deleteUser($id);
        $_SESSION['success'] = "Utilisateur supprimé avec succès.";
    }
}

header('Location: dashboard.php');
exit;
?>
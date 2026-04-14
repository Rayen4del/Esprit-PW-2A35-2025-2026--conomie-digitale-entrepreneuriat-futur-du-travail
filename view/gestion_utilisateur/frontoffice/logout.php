<?php
session_start();

// Détruire toutes les variables de session
$_SESSION = [];

// Détruire la session
session_destroy();

// Supprimer les cookies s'ils existent
setcookie('user_email', '', time() - 3600, '/');
setcookie('user_password', '', time() - 3600, '/');

// Rediriger vers la page de connexion
header('Location: connexion.php');
exit;
?>

<?php
require_once(__DIR__ . '/../../session.php');

session_destroy();

header("Location: FrontOffice\interfaceutilisateur.php");
exit;
?>
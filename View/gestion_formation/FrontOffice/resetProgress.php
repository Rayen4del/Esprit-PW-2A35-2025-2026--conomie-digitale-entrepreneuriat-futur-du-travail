<?php
session_start();

include_once __DIR__ . '/../../../Controller/ProgressionController.php';

if (!isset($_SESSION['user_id'])) {
    echo "not_logged";
    exit;
}

if (!isset($_POST['formation_id'])) {
    echo "missing_id";
    exit;
}

$formation_id = (int) $_POST['formation_id'];

$progressC = new ProgressionController();
$progressC->resetProgress($_SESSION['user_id'], $formation_id);

echo "ok";
exit;
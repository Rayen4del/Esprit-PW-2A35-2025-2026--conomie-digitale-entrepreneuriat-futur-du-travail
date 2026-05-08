<?php
// Use __DIR__ to ensure include works regardless of cwd
include_once __DIR__ . '/../../../../Controller/ChapitreController.php';

header('Content-Type: text/plain; charset=utf-8');

if (isset($_GET['id_f'])) {
    try {
        $chapitreC = new ChapitreController();

        $id_f = (int) $_GET['id_f'];

        $lastOrder = $chapitreC->getLastOrderByFormation($id_f);

        $ordre = ($lastOrder !== null && $lastOrder !== false) ? ((int)$lastOrder + 1) : 1;

        echo $ordre;
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo "1"; // fallback
        exit;
    }
}
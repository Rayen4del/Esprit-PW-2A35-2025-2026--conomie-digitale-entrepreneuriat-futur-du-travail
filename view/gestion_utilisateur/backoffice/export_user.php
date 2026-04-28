<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'admin') {
    http_response_code(403);
    echo 'Acces refuse';
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';

$userId = (int)($_GET['id'] ?? 0);
$format = strtolower(trim($_GET['format'] ?? 'csv'));

if ($userId <= 0) {
    http_response_code(400);
    echo 'Identifiant utilisateur invalide';
    exit;
}

$allowedFormats = ['csv', 'pdf'];
if (!in_array($format, $allowedFormats, true)) {
    $format = 'csv';
}

$userC = new UserController();
$user = $userC->getUserForExportById($userId);

if (!$user) {
    http_response_code(404);
    echo 'Utilisateur introuvable';
    exit;
}

$timestamp = date('Ymd_His');
$userName = $user['u_Nom'] ?? ('user_' . $userId);
$safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $userName);

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="utilisateur_' . $safeName . '_' . $timestamp . '.csv"');

    $out = fopen('php://output', 'w');
    fprintf($out, "\xEF\xBB\xBF");

    $labels = [];
    foreach (array_keys($user) as $key) {
        $labels[] = $key;
    }

    fputcsv($out, $labels, ';');
    fputcsv($out, array_map(static function ($value) {
        return $value === null ? '' : (string) $value;
    }, array_values($user)), ';');

    fclose($out);
    exit;
}

function pdfEscapeUser($text)
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function normalizePdfUserText($text)
{
    if ($text === null) {
        return '';
    }

    $string = (string) $text;
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        if ($converted !== false) {
            return $converted;
        }
    }

    return $string;
}

function buildSingleUserPdf(array $lines, $title)
{
    $normalizedLines = [];
    $normalizedLines[] = 'FICHE UTILISATEUR - ' . normalizePdfUserText($title);
    $normalizedLines[] = str_repeat('=', 90);
    foreach ($lines as $line) {
        $normalizedLines[] = normalizePdfUserText($line);
    }

    $pages = [];
    $linesPerPage = 36;
    $lineHeight = 18;
    $startY = 800;

    $chunks = array_chunk($normalizedLines, $linesPerPage);
    if (empty($chunks)) {
        $chunks = [['AUCUNE DONNEE A EXPORTER']];
    }

    foreach ($chunks as $chunk) {
        $content = "";
        foreach ($chunk as $index => $line) {
            $safeLine = pdfEscapeUser($line);
            $y = $startY - ($index * $lineHeight);
            $content .= "BT\n";
            $content .= "/F1 10 Tf\n";
            $content .= "0 g\n";
            $content .= "1 0 0 1 40 " . $y . " Tm\n";
            $content .= '(' . $safeLine . ") Tj\n";
            $content .= "ET\n";
        }

        $pages[] = trim($content);
    }

    $objects = [];
    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";

    $kids = [];
    $pageObjectStart = 3;
    $fontObjectNumber = $pageObjectStart + (count($pages) * 2);

    foreach ($pages as $index => $content) {
        $pageObjNum = $pageObjectStart + ($index * 2);
        $contentObjNum = $pageObjNum + 1;
        $kids[] = $pageObjNum . ' 0 R';

        $objects[$pageObjNum] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /ProcSet [/PDF /Text] /Font << /F1 " . $fontObjectNumber . " 0 R >> >> /Contents " . $contentObjNum . " 0 R >>";
        $objects[$contentObjNum] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
    }

    $objects[$fontObjectNumber] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($kids) . " >>";

    ksort($objects);

    $pdf = "%PDF-1.4\r\n";
    $offsets = [0];

    foreach ($objects as $number => $obj) {
        $offsets[$number] = strlen($pdf);
        $pdf .= $number . " 0 obj\r\n" . $obj . "\r\nendobj\r\n";
    }

    $xrefPos = strlen($pdf);
    $maxObjectNumber = max(array_keys($objects));
    $pdf .= "xref\r\n0 " . ($maxObjectNumber + 1) . "\r\n";
    $pdf .= "0000000000 65535 f \r\n";

    for ($i = 1; $i <= $maxObjectNumber; $i++) {
        $offset = $offsets[$i] ?? 0;
        $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT) . " 00000 n \r\n";
    }

    $pdf .= "trailer\r\n<< /Size " . ($maxObjectNumber + 1) . " /Root 1 0 R >>\r\n";
    $pdf .= "startxref\r\n" . $xrefPos . "\r\n%%EOF";

    return $pdf;
}

$lines = [];
$lines[] = 'Utilisateur ID: ' . ($user['u_ID'] ?? $userId);
foreach ($user as $key => $value) {
    $lines[] = $key . ': ' . ($value === null ? '' : (string) $value);
}

if ($format === 'pdf') {
    $pdfBinary = buildSingleUserPdf($lines, $userName);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="utilisateur_' . $safeName . '_' . $timestamp . '.pdf"');
    header('Content-Length: ' . strlen($pdfBinary));
    echo $pdfBinary;
    exit;
}

http_response_code(500);
echo 'Format non supporte';

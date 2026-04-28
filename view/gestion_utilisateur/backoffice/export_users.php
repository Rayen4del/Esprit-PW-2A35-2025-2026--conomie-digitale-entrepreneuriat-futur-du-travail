<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'admin') {
    http_response_code(403);
    echo 'Acces refuse';
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';

$userC = new UserController();

$format = strtolower(trim($_GET['format'] ?? 'csv'));
$type = $_GET['type'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');
$sortDate = $_GET['sortDate'] ?? 'desc';
$sortName = $_GET['sortName'] ?? 'none';

$allowedFormats = ['csv', 'pdf'];
$allowedSortDate = ['asc', 'desc'];
$allowedSortName = ['none', 'asc', 'desc'];

if (!in_array($format, $allowedFormats, true)) {
    $format = 'csv';
}
if (!in_array($sortDate, $allowedSortDate, true)) {
    $sortDate = 'desc';
}
if (!in_array($sortName, $allowedSortName, true)) {
    $sortName = 'none';
}

$rows = $userC->getUsersForExport($type, $status, $search, $sortDate, $sortName);

$timestamp = date('Ymd_His');

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="utilisateurs_export_' . $timestamp . '.csv"');

    $out = fopen('php://output', 'w');
    fprintf($out, "\xEF\xBB\xBF");

    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]), ';');
        foreach ($rows as $row) {
            $normalized = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $normalized[] = '';
                } else {
                    $normalized[] = (string) $value;
                }
            }
            fputcsv($out, $normalized, ';');
        }
    } else {
        fputcsv($out, ['Aucune donnee'], ';');
    }

    fclose($out);
    exit;
}

function pdfEscape($text)
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function normalizePdfText($text)
{
    if ($text === null) {
        return '';
    }

    $string = (string) $text;
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $string);
        if ($converted !== false) {
            return $converted;
        }
    }
    return $string;
}

function buildSimplePdf(array $lines, $title)
{
    $pages = [];
    $maxLinesPerPage = 45;

    $titleLine = 'Export utilisateurs - ' . $title;
    array_unshift($lines, str_repeat('=', 100));
    array_unshift($lines, $titleLine);

    $chunks = array_chunk($lines, $maxLinesPerPage);
    if (empty($chunks)) {
        $chunks = [[]];
    }

    foreach ($chunks as $chunk) {
        $stream = "BT\n/F1 10 Tf\n40 800 Td\n";
        $first = true;
        foreach ($chunk as $line) {
            $line = normalizePdfText($line);
            $line = pdfEscape($line);
            if ($first) {
                $stream .= '(' . $line . ") Tj\n";
                $first = false;
            } else {
                $stream .= "0 -16 Td\n(" . $line . ") Tj\n";
            }
        }
        $stream .= "ET";
        $pages[] = $stream;
    }

    $objects = [];
    $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";

    $kids = [];
    $pageObjectStart = 3;
    $fontObjectNumber = $pageObjectStart + (count($pages) * 2);

    for ($i = 0; $i < count($pages); $i++) {
        $pageObjNum = $pageObjectStart + ($i * 2);
        $contentObjNum = $pageObjNum + 1;
        $kids[] = $pageObjNum . ' 0 R';

        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 " . $fontObjectNumber . " 0 R >> >> /Contents " . $contentObjNum . " 0 R >>";
        $content = $pages[$i];
        $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
    }

    $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

    $pagesNode = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($kids) . " >>";
    $objects[1] = $pagesNode;

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $index => $obj) {
        $objNumber = $index + 1;
        $offsets[$objNumber] = strlen($pdf);
        $pdf .= $objNumber . " 0 obj\n" . $obj . "\nendobj\n";
    }

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
    }

    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

    return $pdf;
}

$lines = [];
if (!empty($rows)) {
    $headers = array_keys($rows[0]);
    $lines[] = implode(' | ', $headers);
    $lines[] = str_repeat('-', 180);

    foreach ($rows as $row) {
        $values = [];
        foreach ($headers as $h) {
            $v = $row[$h] ?? '';
            $values[] = $h . '=' . ($v === null ? '' : (string)$v);
        }
        $lines[] = implode(' | ', $values);
        $lines[] = str_repeat('-', 180);
    }
} else {
    $lines[] = 'Aucune donnee a exporter';
}

$pdfBinary = buildSimplePdf($lines, date('d/m/Y H:i:s'));

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="utilisateurs_export_' . $timestamp . '.pdf"');
header('Content-Length: ' . strlen($pdfBinary));
echo $pdfBinary;
exit;

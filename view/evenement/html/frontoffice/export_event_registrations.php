<?php
include_once __DIR__ . '/../../../../config.php';
include_once __DIR__ . '/../../../../controller/evenement/EvenementController.php';

$controller = new EvenementController();
$eventId = intval($_GET['event_id'] ?? 0);
if ($eventId <= 0) {
    http_response_code(400);
    echo 'ID d\'événement invalide.';
    exit;
}

$event = $controller->getById($eventId);
if (!$event) {
    http_response_code(404);
    echo 'Événement introuvable.';
    exit;
}

$registrations = $controller->getRegistrationsForEvent($eventId);

function pdfEscape($text) {
    $text = utf8_decode($text);
    $search = ['\\', '(', ')'];
    $replace = ['\\\\', '\\(', '\\)'];
    return '(' . str_replace($search, $replace, $text) . ')';
}

$lines = [];
$lines[] = ['font' => 14, 'text' => 'Liste des inscriptions pour l\'événement'];
$lines[] = ['font' => 12, 'text' => 'Titre: ' . $event['Titre']];
$lines[] = ['font' => 12, 'text' => 'Date: ' . date('d/m/Y', strtotime($event['dateEvent']))];
$lines[] = ['font' => 12, 'text' => 'Type: ' . ucfirst($event['Type'])];
$lines[] = ['font' => 12, 'text' => 'Statut: ' . ucfirst($event['Statut'])];
$lines[] = ['font' => 12, 'text' => 'Places: ' . intval($event['nbplaces'])];
$lines[] = ['font' => 12, 'text' => ''];
$lines[] = ['font' => 12, 'text' => 'IDUtilisateur | DateInscription | Statut'];
$lines[] = ['font' => 12, 'text' => '------------------------------------------------------------'];

if (empty($registrations)) {
    $lines[] = ['font' => 12, 'text' => 'Aucune inscription enregistrée pour cet événement.'];
} else {
    foreach ($registrations as $reg) {
        $lines[] = ['font' => 12, 'text' => intval($reg['IDUtilisateur'])
            . ' | ' . $reg['DateInscription']
            . ' | ' . ucfirst($reg['Statut'])];
    }
}

function buildPageContent(array $lines) {
    $content = "BT /F1 14 Tf 50 800 Td ";
    $first = true;
    foreach ($lines as $line) {
        $text = pdfEscape($line['text']);
        if ($first) {
            $content .= $text . " Tj\n";
            $first = false;
        } else {
            $content .= "0 -18 Td " . $text . " Tj\n";
        }
    }
    $content .= "ET";
    return $content;
}

function createPdf(array $lines) {
    $maxLinesPerPage = 38;
    $pageChunks = array_chunk($lines, $maxLinesPerPage);

    $objects = [];
    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $pageNumbers = [];
    $fontObjNum = 3 + count($pageChunks) * 2;

    foreach ($pageChunks as $index => $pageLines) {
        $pageObjNum = 3 + $index * 2;
        $contentObjNum = $pageObjNum + 1;
        $pageNumbers[] = $pageObjNum;
        $content = buildPageContent($pageLines);
        $objects[$contentObjNum] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        $objects[$pageObjNum] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 $fontObjNum 0 R >> >> /Contents $contentObjNum 0 R >>";
    }

    $children = implode(' ', array_map(fn($num) => "$num 0 R", $pageNumbers));
    $objects[2] = "<< /Type /Pages /Kids [$children] /Count " . count($pageChunks) . " >>";
    $objects[$fontObjNum] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

    $output = "%PDF-1.4\n";
    $xref = [];
    $offset = strlen($output);
    ksort($objects);
    foreach ($objects as $objNum => $objContent) {
        $xref[$objNum] = $offset;
        $output .= $objNum . " 0 obj\n" . $objContent . "\nendobj\n";
        $offset = strlen($output);
    }

    $xrefOffset = $offset;
    $output .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $output .= str_pad($xref[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
    }
    $output .= "trailer<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

    return $output;
}

$output = createPdf($lines);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="inscriptions_event_' . $eventId . '.pdf"');
echo $output;
exit;

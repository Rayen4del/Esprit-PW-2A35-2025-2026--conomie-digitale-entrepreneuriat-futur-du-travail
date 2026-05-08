<?php
include __DIR__ . '/../../../../config.php';

$data = json_decode(file_get_contents("php://input"), true);

$db = config::getConnexion();

foreach ($data as $item) {
    $stmt = $db->prepare("UPDATE chap_contenu SET ordre_cc = :ordre WHERE id_cc = :id");
    $stmt->execute([
        'ordre' => $item['ordre'],
        'id' => $item['id']
    ]);
}

echo "OK";
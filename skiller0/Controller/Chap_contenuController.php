<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/chap_contenu.php');
class ChapContenuController {

    public function listContenus() {
        $db = config::getConnexion();
        return $db->query("SELECT * FROM chap_contenu");
    }

    public function deleteContenu($id) {
        $db = config::getConnexion();

        $req = $db->prepare("DELETE FROM chap_contenu WHERE id_cc = :id");
        return $req->execute(['id' => $id]);
    }

    public function showContenu($id) {
        $db = config::getConnexion();

        $query = $db->prepare("SELECT * FROM chap_contenu WHERE id_cc = :id");
        $query->execute(['id' => $id]);

        return $query->fetch();
    }

    public function getLastOrder($id_c) {
        $db = config::getConnexion();

        $query = $db->prepare("
            SELECT MAX(ordre_cc) as max_ordre 
            FROM chap_contenu 
            WHERE id_c = :id_c
        ");

        $query->execute(['id_c' => $id_c]);

        $result = $query->fetch();

        return $result['max_ordre'] ?? 0;
    }

    public function addContenu(ChapContenu $c) {

        $db = config::getConnexion();

        $ordre = $c->getOrdre();

        if (!$ordre) {
            $ordre = $this->getLastOrder($c->getChapitreId()) + 1;
        }

        $query = $db->prepare("
            INSERT INTO chap_contenu (id_c, type_cc, contenu, ordre_cc)
            VALUES (:id_c, :type_cc, :contenu, :ordre_cc)
        ");

        return $query->execute([
            'id_c' => $c->getChapitreId(),
            'type_cc' => $c->getType(),
            'contenu' => $c->getContenu(),
            'ordre_cc' => $ordre
        ]);
    }

    public function updateContenu(ChapContenu $c, $id) {

        $db = config::getConnexion();

        $ordre = $c->getOrdre() ?? 1;

        $query = $db->prepare("
            UPDATE chap_contenu SET 
                id_c = :id_c,
                type_cc = :type_cc,
                contenu = :contenu,
                ordre_cc = :ordre_cc
            WHERE id_cc = :id
        ");

        return $query->execute([
            'id' => $id,
            'id_c' => $c->getChapitreId(),
            'type_cc' => $c->getType(),
            'contenu' => $c->getContenu(),
            'ordre_cc' => $ordre
        ]);
    }
}
?>
<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/chap_contenu.php');

class ChapContenuController {

    public function listContenus() {
        $db = config::getConnexion();
        return $db->query("SELECT * FROM chap_contenu");
    }

    public function deleteContenu($id) {
        $db = config::getConnexion();
        $req = $db->prepare("DELETE FROM chap_contenu WHERE id_cc = :id");
        $req->bindValue(':id', $id);
        $req->execute();
    }

    public function addContenu(ChapContenu $c) {
        $db = config::getConnexion();

        $query = $db->prepare("INSERT INTO chap_contenu VALUES (NULL, :id_c, :type_cc, :contenu, :ordre_cc)");

        $query->execute([
            'id_c' => $c->getChapitreId(),
            'type_cc' => $c->getType(),
            'contenu' => $c->getContenu(),
            'ordre_cc' => $c->getOrdre()
        ]);
    }

    public function updateContenu(ChapContenu $c, $id) {
        $db = config::getConnexion();

        $query = $db->prepare(
            "UPDATE chap_contenu SET 
                id_c = :id_c,
                type_cc = :type_cc,
                contenu = :contenu,
                ordre_cc = :ordre_cc
            WHERE id_cc = :id"
        );

        $query->execute([
            'id' => $id,
            'id_c' => $c->getChapitreId(),
            'type_cc' => $c->getType(),
            'contenu' => $c->getContenu(),
            'ordre_cc' => $c->getOrdre()
        ]);
    }

    public function showContenu($id) {
        $db = config::getConnexion();
        $query = $db->prepare("SELECT * FROM chap_contenu WHERE id_cc = $id");
        $query->execute();
        return $query->fetch();
    }
}
?>
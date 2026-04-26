<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/chapitre.php');

class ChapitreController {

    public function listChapitres() {
        $sql = "SELECT * FROM chapitre";
        $db = config::getConnexion();
        return $db->query($sql);
    }

    public function deleteChapitre($id) {
        $sql = "DELETE FROM chapitre WHERE id_c = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        $req->execute();
    }
    public function addChapitre(Chapitre $chapitre) {
    $db = config::getConnexion();

    $sql = "INSERT INTO chapitre (id_f, titre_c, ordre)
            VALUES (:id_f, :titre_c, :ordre)";

    $stmt = $db->prepare($sql);

    return $stmt->execute([
        'id_f' => $chapitre->getFormationId(),
        'titre_c' => $chapitre->getTitre(),
        'ordre' => $chapitre->getOrdre()
    ]);
}

    public function updateChapitre(Chapitre $chapitre, $id) {
        $db = config::getConnexion();

        $query = $db->prepare(
            "UPDATE chapitre SET 
                id_f = :id_f,
                titre_c = :titre_c,
                ordre = :ordre
            WHERE id_c = :id"
        );

        $query->execute([
            'id' => $id,
            'id_f' => $chapitre->getFormationId(),
            'titre_c' => $chapitre->getTitre(),
            'ordre' => $chapitre->getOrdre()
        ]);
    }

    public function showChapitre($id) {
        $db = config::getConnexion();
        $query = $db->prepare("SELECT * FROM chapitre WHERE id_c = $id");
        $query->execute();
        return $query->fetch();
    }
    public function listChapitresByFormation($id_f)
{
    $db = config::getConnexion();

    $stmt = $db->prepare("SELECT * FROM chapitre WHERE id_f = ?");
    $stmt->execute([$id_f]);

    return $stmt->fetchAll();
}
}
?>
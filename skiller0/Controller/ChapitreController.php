<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/chapitre.php');

class ChapitreController {

    // =========================
    // LIST ALL
    // =========================
    public function listChapitres() {
        $sql = "SELECT * FROM chapitre";
        $db = config::getConnexion();

        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // =========================
    // GET BY ID
    // =========================
    public function getChapitreById($id)
    {
        $db = config::getConnexion();

        $stmt = $db->prepare("SELECT * FROM chapitre WHERE id_c = ?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================
    // DELETE
    // =========================
    public function deleteChapitre($id)
    {
        $db = config::getConnexion();

        $sql = "DELETE FROM chapitre WHERE id_c = :id";
        $req = $db->prepare($sql);

        $req->bindValue(':id', $id);
        $req->execute();
    }

    // =========================
    // ADD CHAPITRE
    // =========================
    public function addChapitre(Chapitre $chapitre)
    {
        $sql = "INSERT INTO chapitre (id_f, titre_c, ordre)
                VALUES (:id_f, :titre_c, :ordre)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_f' => $chapitre->getIdFormation(),
                'titre_c' => $chapitre->getTitre(),
                'ordre' => $chapitre->getOrdre()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // =========================
    // UPDATE CHAPITRE
    // =========================
    public function updateChapitre(Chapitre $chapitre)
    {
        $sql = "UPDATE chapitre SET 
                id_f = :id_f,
                titre_c = :titre_c,
                ordre = :ordre
                WHERE id_c = :id";

        $db = config::getConnexion();
        $query = $db->prepare($sql);

        $query->execute([
            'id' => $chapitre->getId(),
            'id_f' => $chapitre->getFormationId(),
            'titre_c' => $chapitre->getTitre(),
            'ordre' => $chapitre->getOrdre()
        ]);
    }

    // =========================
    // SEARCH + PAGINATION
    // =========================
    public function searchPaginated($search = "", $page = 1, $limit = 10, $sort = "ASC")
    {
        $db = config::getConnexion();

        $offset = ($page - 1) * $limit;
        $sort = strtoupper($sort);

        // COUNT
        $sqlCount = "SELECT COUNT(*) FROM chapitre
                     WHERE titre_c LIKE :search";

        $stmtCount = $db->prepare($sqlCount);
        $stmtCount->execute([
            'search' => "%" . $search . "%"
        ]);

        $total = $stmtCount->fetchColumn();
        $totalPages = ceil($total / $limit);

        // DATA
        $sql = "SELECT * FROM chapitre
                WHERE titre_c LIKE :search
                ORDER BY titre_c $sort
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':search', "%" . $search . "%");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }

    // =========================
    // LIST BY FORMATION (IMPORTANT)
    // =========================
    public function listChapitresByFormation($id_f)
    {
        $db = config::getConnexion();

        $stmt = $db->prepare("
            SELECT * FROM chapitre 
            WHERE id_f = ? 
            ORDER BY ordre ASC
        ");

        $stmt->execute([$id_f]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
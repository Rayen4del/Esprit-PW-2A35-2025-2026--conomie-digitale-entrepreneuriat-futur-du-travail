<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/chapitre.php');

class ChapitreController {
    public function getLastOrderByFormation($id_f)
{
    $db = config::getConnexion();

    $stmt = $db->prepare("
        SELECT MAX(ordre) as max_order 
        FROM chapitre 
        WHERE id_f = ?
    ");

    $stmt->execute([$id_f]);
    $result = $stmt->fetch();

    return $result['max_order'] ?? 0;
}
    // =========================
    // LIST CHAPITRES BY FORMATION ID
    // =========================
    public function listChapitresByFormation($id_f)
    {
        $db = config::getConnexion();

        $sql = "SELECT * FROM chapitre WHERE id_f = :id_f ORDER BY ordre ASC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_f', $id_f, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
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
        $db = config::getConnexion();

        try {
            // Ensure ordre is computed server-side to avoid race conditions
            $db->beginTransaction();

            $ordre = $chapitre->getOrdre();

            if (!$ordre) {
                // lock rows for this formation while determining max ordre
                $stmt = $db->prepare("SELECT MAX(ordre) as max_order FROM chapitre WHERE id_f = :id_f FOR UPDATE");
                $stmt->execute(['id_f' => $chapitre->getFormationId()]);
                $res = $stmt->fetch();
                $last = $res['max_order'] ?? 0;
                $ordre = $last + 1;
            }

            $sql = "INSERT INTO chapitre (id_f, titre_c, ordre) VALUES (:id_f, :titre_c, :ordre)";
            $query = $db->prepare($sql);
            $query->execute([
                'id_f' => $chapitre->getFormationId(),
                'titre_c' => $chapitre->getTitre(),
                'ordre' => $ordre
            ]);

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
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
    public function searchPaginated($idfor = null, $search = "", $page = 1, $limit = 10, $sort = "ASC")
{
    $db = config::getConnexion();

    $offset = ($page - 1) * $limit;
    $sort = strtoupper($sort);

    // =====================
    // BASE WHERE
    // =====================
    $where = "WHERE titre_c LIKE :search";
    $params = [
        'search' => "%" . $search . "%"
    ];

    // 👉 Ajouter filtre formation seulement si موجود
    if (!empty($idfor)) {
        $where .= " AND id_f = :id_f";
        $params['id_f'] = $idfor;
    }

    // =====================
    // COUNT
    // =====================
    $sqlCount = "SELECT COUNT(*) FROM chapitre $where";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute($params);

    $total = $stmtCount->fetchColumn();
    $totalPages = ceil($total / $limit);

    // =====================
    // DATA
    // =====================
    $sql = "SELECT * FROM chapitre 
            $where
            ORDER BY titre_c $sort
            LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);

    // bind dynamique
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

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
}
?>
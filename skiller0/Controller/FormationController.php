<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/formation.php');

class FormationController {

    public function listFormations() {
        $sql = "SELECT * FROM formation";
        $db = config::getConnexion();

        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

  public function deleteFormation($id)
    {
        $db = config::getConnexion();

        // 1. récupérer image avant suppression
        $stmt = $db->prepare("SELECT image FROM formation WHERE id_f = ?");
        $stmt->execute([$id]);
        $formation = $stmt->fetch();

        // 2. supprimer image du dossier
        if ($formation && !empty($formation['image'])) {
            $imagePath = __DIR__ . '/../../../' . $formation['image'];

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // 3. supprimer en base
        $sql = "DELETE FROM formation WHERE id_f = :id";
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        $req->execute();
    }
    public function listFormationsPaginated($limit, $offset, $search = "")
{
    $db = config::getConnexion();

    $sql = "SELECT * FROM formation 
            WHERE titre LIKE :search 
            ORDER BY id_f DESC 
            LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    // =========================
    // ADD FORMATION (UPDATED)
    // =========================
    public function addFormation(Formation $formation) {
        $sql = "INSERT INTO formation 
                VALUES (NULL, :titre, :description, :created_by, :nom_propr, :date_c, :evaluation, :image, :etat)";
        
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $formation->getTitre(),
                'description' => $formation->getDescription(),
                'created_by' => $formation->getCreatedBy(),
                'nom_propr' => $formation->getNomProprietaire(),
                'date_c' => $formation->getDateCreation() ? $formation->getDateCreation()->format('Y-m-d') : null,
                'evaluation' => $formation->getEvaluation(),
                'image' => $formation->getImage(),
                'etat' => $formation->getEtat() ?? 'actif' // ✅ DEFAULT
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
public function countFormations($search = "")
{
    $db = config::getConnexion();

    $sql = "SELECT COUNT(*) FROM formation WHERE titre LIKE :search";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':search' => "%$search%"
    ]);

    return $stmt->fetchColumn();
}
    // =========================
    // UPDATE FORMATION (UPDATED)
    // =========================
public function updateFormation(Formation $formation)
{
    $uploadDir = __DIR__ . "/../../../uploads/";

    $sql = "UPDATE formation SET 
            titre = :titre,
            description = :description,
            nom_propr = :nom_propr,
            image = :image,
            etat = :etat
            WHERE id_f = :id";

    $db = config::getConnexion();
    $query = $db->prepare($sql);

    $query->execute([
        'id' => $formation->getId(),
        'titre' => $formation->getTitre(),
        'description' => $formation->getDescription(),
        'nom_propr' => $formation->getNomProprietaire(),
        'image' => $formation->getImage(),
        'etat' => $formation->getEtat()
    ]);
}
    // =========================
    // SHOW FORMATION
    // =========================
    public function showFormation($id) {
        $sql = "SELECT * FROM formation WHERE id_f = $id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    public function getFormationById($id)
{
    $db = config::getConnexion();

    $stmt = $db->prepare("SELECT * FROM formation WHERE id_f = ?");
    $stmt->execute([$id]);

    return $stmt->fetch();
}
public function searchPaginated($search = "", $page = 1, $limit = 10, $sort = "ASC")
{
    $db = config::getConnexion();

    $offset = ($page - 1) * $limit;

    $sort = strtoupper($sort);
    
    // COUNT
    $sqlCount = "SELECT COUNT(*) FROM formation
                 WHERE titre LIKE :search OR nom_propr LIKE :search";

    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute([
        'search' => "%" . $search . "%"
    ]);

    $total = $stmtCount->fetchColumn();
    $totalPages = ceil($total / $limit);

    // DATA
    $sql = "SELECT * FROM formation 
            WHERE titre LIKE :search 
            ORDER BY titre $sort
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
}
?>
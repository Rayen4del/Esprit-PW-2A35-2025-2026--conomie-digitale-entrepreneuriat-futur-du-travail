<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/test.php');

class TestController {

    public function listTests() {
        $db = config::getConnexion();
        return $db->query("SELECT * FROM test");
    }

    public function deleteTest($id) {
        $db = config::getConnexion();
        $req = $db->prepare("DELETE FROM test WHERE id_t = :id");
        $req->bindValue(':id', $id);
        $req->execute();
    }

    public function addTest(Test $t) {
        $db = config::getConnexion();

        $query = $db->prepare("INSERT INTO test VALUES (NULL, :id_c, :id_f, :score_min, :date_creation)");

        $query->execute([
            'id_c' => $t->getChapitreId(),
            'id_f' => $t->getFormationId(),
            'score_min' => $t->getScoreMin(),
            'date_creation' => $t->getDateCreation() ? $t->getDateCreation()->format('Y-m-d') : null
        ]);
        return $db->lastInsertId();
    }

    public function updateTest(Test $t, $id) {
        $db = config::getConnexion();

        $query = $db->prepare(
            "UPDATE test SET 
                id_c = :id_c,
                id_f = :id_f,
                score_min = :score_min,
                date_creation = :date_creation
            WHERE id_t = :id"
        );

        $query->execute([
            'id' => $id,
            'id_c' => $t->getChapitreId(),
            'id_f' => $t->getFormationId(),
            'score_min' => $t->getScoreMin(),
            'date_creation' => $t->getDateCreation() ? $t->getDateCreation()->format('Y-m-d') : null
        ]);
    }

    public function showTest($id) {
        $db = config::getConnexion();
        $query = $db->prepare("SELECT * FROM test WHERE id_t = $id");
        $query->execute();
        return $query->fetch();
    }
   public function searchTests($search, $limit, $offset, $sort)
{
    $db = config::getConnexion();

    if ($search != "") {
        $sql = "SELECT * FROM test
                WHERE id_t = :id
                ORDER BY id_t $sort
                LIMIT $limit OFFSET $offset";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id' => (int)$search
        ]);
    } else {
        $sql = "SELECT * FROM test
                ORDER BY id_t $sort
                LIMIT $limit OFFSET $offset";

        $stmt = $db->prepare($sql);
        $stmt->execute();
    }

    return $stmt->fetchAll();
}
public function countTests($search = "")
{
    $db = config::getConnexion();

    $sql = "SELECT COUNT(*) FROM test
            WHERE id_t LIKE :search
               OR id_c LIKE :search
               OR id_f LIKE :search
               OR score_min LIKE :search
               OR date_creation LIKE :search";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':search' => "%$search%"
    ]);

    return $stmt->fetchColumn();
}
public function listTestsByFormation($id_f) {
    $db = config::getConnexion();

    $query = $db->prepare("SELECT * FROM test WHERE id_f = :id_f");
    $query->execute([
        ':id_f' => $id_f
    ]);

    return $query->fetchAll();
}
}
?>
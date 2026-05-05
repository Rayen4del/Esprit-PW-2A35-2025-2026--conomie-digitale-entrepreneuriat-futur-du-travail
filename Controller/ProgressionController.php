<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/progression.php');

class ProgressionController {

    public function listProgressions() {
        $sql = "SELECT * FROM progression";
        $db = config::getConnexion();

        return $db->query($sql);
    }

    public function deleteProgression($id) {
        $sql = "DELETE FROM progression WHERE id_p = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addProgression(Progression $p) {
        $sql = "INSERT INTO progression VALUES (NULL, :id_u, :id_c, :date_p)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_u' => $p->getUserId(),
                'id_c' => $p->getChapitreId(),
                'date_p' => $p->getDate() ? $p->getDate()->format('Y-m-d') : null
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateProgression(Progression $p, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                "UPDATE progression SET 
                    id_u = :id_u,
                    id_c = :id_c,
                    date_p = :date_p
                WHERE id_p = :id"
            );

            $query->execute([
                'id' => $id,
                'id_u' => $p->getUserId(),
                'id_c' => $p->getChapitreId(),
                'date_p' => $p->getDate() ? $p->getDate()->format('Y-m-d') : null
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showProgression($id) {
        $sql = "SELECT * FROM progression WHERE id_p = $id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    public function countCompletedChapitres($id_user, $id_formation) {

    $db = config::getConnexion();

    $sql = "
        SELECT COUNT(DISTINCT p.id_c) as total_done
        FROM progression p
        JOIN chapitre c ON p.id_c = c.id_c
        WHERE p.id_u = :id_u
        AND c.id_f = :id_f
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        'id_u' => $id_user,
        'id_f' => $id_formation
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC)['total_done'];
}
public function getProgressionByUser($user_id) {
    $db = config::getConnexion();

    $sql = "
        SELECT 
            f.id_f,
            f.titre,
            f.image,


            COUNT(DISTINCT c.id_c) AS total_chapitres,

            COUNT(DISTINCT p.id_c) AS chapitres_finis,

            MAX(p.date_p) AS derniere_date

        FROM formation f

        JOIN chapitre c ON c.id_f = f.id_f

        LEFT JOIN progression p 
            ON p.id_c = c.id_c 
            AND p.id_u = :user_id

        GROUP BY f.id_f, f.titre

        HAVING chapitres_finis > 0
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>
<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/progression.php');

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
}
?>
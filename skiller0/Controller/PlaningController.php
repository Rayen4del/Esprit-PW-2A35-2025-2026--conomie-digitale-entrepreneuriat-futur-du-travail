<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/planing.php');

class PlaningController {

    public function listPlanings() {
        $sql = "SELECT * FROM planing";
        $db = config::getConnexion();

        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deletePlaning($id) {
        $sql = "DELETE FROM planing WHERE id_pl = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addPlaning(Planing $p) {
        $sql = "INSERT INTO planing VALUES (NULL, :id_u, :id_c, :date_pl, :note)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_u' => $p->getUserId(),
                'id_c' => $p->getChapitreId(),
                'date_pl' => $p->getDate() ? $p->getDate()->format('Y-m-d') : null,
                'note' => $p->getNote()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updatePlaning(Planing $p, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                "UPDATE planing SET 
                    id_u = :id_u,
                    id_c = :id_c,
                    date_pl = :date_pl,
                    note = :note
                WHERE id_pl = :id"
            );

            $query->execute([
                'id' => $id,
                'id_u' => $p->getUserId(),
                'id_c' => $p->getChapitreId(),
                'date_pl' => $p->getDate() ? $p->getDate()->format('Y-m-d') : null,
                'note' => $p->getNote()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showPlaning($id) {
        $sql = "SELECT * FROM planing WHERE id_pl = $id";
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
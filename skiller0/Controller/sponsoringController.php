<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/Sponsoring.php');

class SponsoringController {

    public function listSponsoring() {
        $sql = "SELECT * FROM sponsoring";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteSponsoring($id_sp) {
        $sql = "DELETE FROM sponsoring WHERE id_sp = :id_sp";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_sp', $id_sp);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function addSponsoring(Sponsoring $sponsoring) {
        $sql = "INSERT INTO sponsoring 
                VALUES (NULL, :id_u, :nom_ent, :logo_entp, :date_deb, :date_fin, :mail_event)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_u' => $sponsoring->getIdU(),
                'nom_ent' => $sponsoring->getNomEnt(),
                'logo_entp' => $sponsoring->getLogoEntp(),
                'date_deb' => $sponsoring->getDateDeb() ? $sponsoring->getDateDeb()->format('Y-m-d') : null,
                'date_fin' => $sponsoring->getDateFin() ? $sponsoring->getDateFin()->format('Y-m-d') : null,
                'mail_event' => $sponsoring->getMailEvent()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateSponsoring(Sponsoring $sponsoring, $id_sp) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE sponsoring SET
                    id_u = :id_u,
                    nom_ent = :nom_ent,
                    logo_entp = :logo_entp,
                    date_deb = :date_deb,
                    date_fin = :date_fin,
                    mail_event = :mail_event
                WHERE id_sp = :id_sp'
            );

            $query->execute([
                'id_sp' => $id_sp,
                'id_u' => $sponsoring->getIdU(),
                'nom_ent' => $sponsoring->getNomEnt(),
                'logo_entp' => $sponsoring->getLogoEntp(),
                'date_deb' => $sponsoring->getDateDeb() ? $sponsoring->getDateDeb()->format('Y-m-d') : null,
                'date_fin' => $sponsoring->getDateFin() ? $sponsoring->getDateFin()->format('Y-m-d') : null,
                'mail_event' => $sponsoring->getMailEvent()
            ]);

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showSponsoring($id_sp) {
        $sql = "SELECT * FROM sponsoring WHERE id_sp = :id_sp";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->execute([
                'id_sp' => $id_sp
            ]);

            $sponsoring = $query->fetch();
            return $sponsoring;

        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
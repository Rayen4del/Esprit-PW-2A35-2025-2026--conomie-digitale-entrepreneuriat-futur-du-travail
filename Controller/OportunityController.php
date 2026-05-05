<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/oportunity.php';

class OportunityController {

    public function listOportunities() {
        $sql = "SELECT * FROM oportunity";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteOportunity($id) {
        $sql = "DELETE FROM oportunity WHERE ID = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            // First delete all related applications
            require_once __DIR__ . '/ApplicationController.php';
            $appCtrl = new ApplicationController();
            $appCtrl->deleteApplicationsByOpportunity($id);
            
            // Then delete the opportunity
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addOportunity(Oportunity $oportunity) {
        $sql = "INSERT INTO oportunity 
                VALUES (NULL, :titre, :type_job, :description, :localisation, :datePublication, :statut)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $oportunity->getTitre(),
                'type_job' => $oportunity->getTypeJob(),
                'description' => $oportunity->getDescription(),
                'localisation' => $oportunity->getLocalisation(),
                'datePublication' => $oportunity->getDatePublication()
                    ? $oportunity->getDatePublication()->format('Y-m-d')
                    : null,
                'statut' => $oportunity->getStatut()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateOportunity(Oportunity $oportunity, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE oportunity SET 
                    Titre = :titre,
                    Type_job = :type_job,
                    Description = :description,
                    Localisation = :localisation,
                    datePublication = :datePublication,
                    Statut = :statut
                WHERE ID = :id'
            );
            $query->execute([
                'id' => $id,
                'titre' => $oportunity->getTitre(),
                'type_job' => $oportunity->getTypeJob(),
                'description' => $oportunity->getDescription(),
                'localisation' => $oportunity->getLocalisation(),
                'datePublication' => $oportunity->getDatePublication()
                    ? $oportunity->getDatePublication()->format('Y-m-d')
                    : null,
                'statut' => $oportunity->getStatut()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showOportunity($id) {
        $sql = "SELECT * FROM oportunity WHERE ID = $id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->execute();
            $oportunity = $query->fetch();
            return $oportunity;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
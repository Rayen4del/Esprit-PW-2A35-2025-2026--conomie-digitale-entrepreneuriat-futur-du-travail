<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/gestion_opportunite/application.php';

class ApplicationController {

    public function listApplications() {
        $sql = "SELECT * FROM application";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteApplication($id) {
        $sql = "DELETE FROM application WHERE ID = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteApplicationsByOpportunity($opportunityId) {
        $sql = "DELETE FROM application WHERE idOportunity = :opportunityId";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':opportunityId', $opportunityId);
        try {
            $req->execute();
            return true;
        } catch (Exception $e) {
            throw new Exception('Error deleting applications: ' . $e->getMessage());
        }
    }

    public function addApplication(Application $application) {
        $sql = "INSERT INTO application (IDUtilisateur, idOportunity, DateCondidature, motivation, CV)
                VALUES (:idUtilisateur, :idOportunity, :dateCondidature, :motivation, :cv)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $result = $query->execute([
                'idUtilisateur' => $application->getIdUtilisateur(),
                'idOportunity' => $application->getIdOportunity(),
                'dateCondidature' => $application->getDateCondidature() 
                    ? $application->getDateCondidature()->format('Y-m-d') 
                    : date('Y-m-d'),
                'motivation' => $application->getMotivation(),
                'cv' => $application->getResource()
            ]);
            return $result;
        } catch (Exception $e) {
            throw new Exception('Error adding application: ' . $e->getMessage());
        }
    }

    public function updateApplication(Application $application, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE application SET 
                    IDUtilisateur = :idUtilisateur,
                    idOportunity = :idOportunity,
                    DateCondidature = :dateCondidature,
                    motivation = :motivation,
                    CV = :cv
                WHERE ID = :id'
            );
            $query->execute([
                'id' => $id,
                'idUtilisateur' => $application->getIdUtilisateur(),
                'idOportunity' => $application->getIdOportunity(),
                'dateCondidature' => $application->getDateCondidature() 
                    ? $application->getDateCondidature()->format('Y-m-d') 
                    : null,
                'motivation' => $application->getMotivation(),
                'cv' => $application->getResource()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showApplication($id) {
        $sql = "SELECT * FROM application WHERE ID = $id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->execute();
            $application = $query->fetch();
            return $application;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    private function allowedApplicationSortColumns() {
        return [
            'ID' => 'a.ID',
            'opportunity_title' => 'o.Titre',
            'IDUtilisateur' => 'a.IDUtilisateur',
            'DateCondidature' => 'a.DateCondidature',
            'Type_job' => 'o.Type_job'
        ];
    }

    public function listApplicationsWithDetails($search = '', $sortBy = 'DateCondidature', $sortOrder = 'DESC', $typeJob = '') {
        $allowedSortColumns = $this->allowedApplicationSortColumns();
        if (!array_key_exists($sortBy, $allowedSortColumns)) {
            $sortBy = 'DateCondidature';
        }

        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        $where = [];
        $params = [];

        $search = trim((string)$search);
        if ($search !== '') {
            $where[] = "(o.Titre LIKE :searchTitle
                OR o.Type_job LIKE :searchType
                OR a.motivation LIKE :searchMotivation
                OR CAST(a.ID AS CHAR) LIKE :searchId
                OR CAST(a.IDUtilisateur AS CHAR) LIKE :searchUser)";
            $searchTerm = '%' . $search . '%';
            $params['searchTitle'] = $searchTerm;
            $params['searchType'] = $searchTerm;
            $params['searchMotivation'] = $searchTerm;
            $params['searchId'] = $searchTerm;
            $params['searchUser'] = $searchTerm;
        }

        $typeJob = trim((string)$typeJob);
        if ($typeJob !== '') {
            $where[] = "o.Type_job = :typeJob";
            $params['typeJob'] = $typeJob;
        }

        $sql = "SELECT a.*, o.Titre as opportunity_title, o.Type_job 
                FROM application a
                LEFT JOIN oportunity o ON a.idOportunity = o.ID";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY " . $allowedSortColumns[$sortBy] . " " . $sortOrder;

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function listApplicationTypes() {
        $sql = "SELECT DISTINCT Type_job FROM oportunity WHERE Type_job IS NOT NULL AND Type_job <> '' ORDER BY Type_job ASC";
        $db = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function getApplicationsByOpportunity($opportunityId) {
        $sql = "SELECT a.* FROM application a WHERE a.idOportunity = :opportunityId";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':opportunityId' => $opportunityId]);
            return $query;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}

<?php
include_once(__DIR__ . '/../../config.php');
include_once(__DIR__ . '/../../model/Inscription.php');
include_once(__DIR__ . '/../../model/Evenement.php');

class InscriptionController {
    private string $lastError = '';
    private array $activeRegistrationStatuses = ['inscrit', 'confirmé'];

    // ─── Read ──────────────────────────────────────────────────────

    public function getAll() : array {
        $sql = "SELECT r.*, e.Titre, e.dateEvent, e.lieu_lien, e.Type
                FROM `registration` r
                JOIN `event` e ON r.IDEvent = e.ID
                ORDER BY r.DateInscription DESC";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getById($id) {
        $sql = "SELECT r.*, e.Titre, e.dateEvent, e.lieu_lien, e.Type
                FROM `registration` r
                JOIN `event` e ON r.IDEvent = e.ID
                WHERE r.ID = :id";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id, PDO::PARAM_INT);
            $req->execute();
            $row = $req->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return false;
            }
            return $row;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getByUser(int $idUtilisateur) : array {
        $sql = "SELECT r.*, e.Titre, e.dateEvent, e.lieu_lien, e.Type
                FROM `registration` r
                JOIN `event` e ON r.IDEvent = e.ID
                WHERE r.IDUtilisateur = :idUser
                ORDER BY r.DateInscription DESC";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([':idUser' => $idUtilisateur]);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getByEvent(int $eventId) : array {
        $sql = "SELECT r.ID, r.IDUtilisateur, r.DateInscription, r.Statut,
                       e.Titre as EventTitre, e.dateEvent, e.Type
                FROM `registration` r
                JOIN `event` e ON r.IDEvent = e.ID
                WHERE r.IDEvent = :eventId
                ORDER BY r.DateInscription DESC";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([':eventId' => $eventId]);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function filtrer($search = '', $statut = '', $type = '') : array {
        $sql = "SELECT r.*, e.Titre, e.dateEvent, e.lieu_lien, e.Type
                FROM `registration` r
                JOIN `event` e ON r.IDEvent = e.ID
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (e.Titre LIKE :search OR e.lieu_lien LIKE :search OR e.Type LIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($statut !== '') {
            $sql .= " AND r.Statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($type !== '') {
            $sql .= " AND e.Type = :type";
            $params[':type'] = $type;
        }
        $sql .= " ORDER BY r.DateInscription DESC";

        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute($params);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ─── Create ────────────────────────────────────────────────────

    public function addInscription(Inscription $inscription) : bool {
        if ($this->isAlreadyRegistered($inscription->getIDUtilisateur(), $inscription->getIDEvent())) {
            $this->lastError = "Inscription déjà existante.";
            return false;
        }

        $statut = $inscription->getStatut();
        if (in_array($statut, $this->activeRegistrationStatuses, true) && !$this->hasAvailablePlaces($inscription->getIDEvent())) {
            $this->lastError = "Plus de places disponibles pour cet événement.";
            return false;
        }

        $sql = "INSERT INTO `registration` (IDUtilisateur, IDEvent, DateInscription, Statut)
                VALUES (:idUser, :idEvent, :date, :statut)";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([
                ':idUser'  => $inscription->getIDUtilisateur(),
                ':idEvent' => $inscription->getIDEvent(),
                ':date'    => $inscription->getDateInscription() ?: date('Y-m-d'),
                ':statut'  => $inscription->getStatut(),
            ]);
            $this->syncEventStatusByCapacity($inscription->getIDEvent());
            $this->lastError = '';
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // ─── Update ────────────────────────────────────────────────────

    public function updateInscription(Inscription $inscription, int $id) : bool {
        $oldRegistration = $this->getById($id);
        if (!$oldRegistration) {
            $this->lastError = "Inscription introuvable.";
            return false;
        }

        $oldEventId = intval($oldRegistration['IDEvent']);
        $oldStatut  = $oldRegistration['Statut'];
        $newStatut  = $inscription->getStatut();
        $newEventId = $inscription->getIDEvent();

        $becameActive = !in_array($oldStatut, $this->activeRegistrationStatuses, true)
            && in_array($newStatut, $this->activeRegistrationStatuses, true);
        $eventChanged = $oldEventId !== $newEventId;

        if (($eventChanged || $becameActive) && in_array($newStatut, $this->activeRegistrationStatuses, true) && !$this->hasAvailablePlaces($newEventId, $id)) {
            $this->lastError = "Plus de places disponibles pour l'événement sélectionné.";
            return false;
        }

        $sql = "UPDATE `registration`
                SET IDUtilisateur = :idUser, IDEvent = :idEvent, Statut = :statut
                WHERE ID = :id";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([
                ':id'      => $id,
                ':idUser'  => $inscription->getIDUtilisateur(),
                ':idEvent' => $inscription->getIDEvent(),
                ':statut'  => $inscription->getStatut(),
            ]);
            $this->syncEventStatusByCapacity($newEventId);
            if ($oldEventId !== $newEventId) {
                $this->syncEventStatusByCapacity($oldEventId);
            }
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // ─── Delete ────────────────────────────────────────────────────

    public function deleteInscription(int $id) : bool {
        $oldRegistration = $this->getById($id);
        if (!$oldRegistration) {
            $this->lastError = "Inscription introuvable.";
            return false;
        }

        $oldEventId = intval($oldRegistration['IDEvent']);
        $sql = "DELETE FROM `registration` WHERE ID = :id";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([':id' => $id]);
            $this->syncEventStatusByCapacity($oldEventId);
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // ─── Helper Methods ────────────────────────────────────────────

    public function getLastError() : string {
        return $this->lastError;
    }

    public function isAlreadyRegistered(int $idUtilisateur, int $idEvent) : bool {
        $sql = "SELECT COUNT(*) FROM `registration` WHERE IDUtilisateur = :idUser AND IDEvent = :idEvent";
        $db  = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([':idUser' => $idUtilisateur, ':idEvent' => $idEvent]);
            return $req->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function annulerInscription(int $idUtilisateur, int $idEvent) : bool {
        $sql = "UPDATE `registration` SET Statut = 'annulé'
                WHERE IDUtilisateur = :idUser AND IDEvent = :idEvent";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([':idUser' => $idUtilisateur, ':idEvent' => $idEvent]);
            $this->syncEventStatusByCapacity($idEvent);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getActiveRegistrationsCount(int $idEvent, ?int $excludeRegistrationId = null) : int {
        $sql = "SELECT COUNT(*) FROM `registration`
                WHERE IDEvent = :idEvent
                  AND Statut IN ('inscrit', 'confirmé')";
        $params = [':idEvent' => $idEvent];

        if ($excludeRegistrationId !== null) {
            $sql .= " AND ID <> :excludeId";
            $params[':excludeId'] = $excludeRegistrationId;
        }

        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute($params);
            return intval($req->fetchColumn());
        } catch (Exception $e) {
            return 0;
        }
    }

    private function hasAvailablePlaces(int $idEvent, ?int $excludeRegistrationId = null) : bool {
        $sql = "SELECT nbplaces FROM `event` WHERE ID = :id";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([':id' => $idEvent]);
            $row = $req->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return false;
            }

            $nbplaces = intval($row['nbplaces'] ?? 0);
            $activeCount = $this->getActiveRegistrationsCount($idEvent, $excludeRegistrationId);
            return $activeCount < $nbplaces;
        } catch (Exception $e) {
            return false;
        }
    }

    private function syncEventStatusByCapacity(int $idEvent) : void {
        $sql = "SELECT nbplaces FROM `event` WHERE ID = :id";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([':id' => $idEvent]);
            $row = $req->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return;
            }

            $nbplaces = intval($row['nbplaces'] ?? 0);
            $activeCount = $this->getActiveRegistrationsCount($idEvent);
            $newStatus = $activeCount >= $nbplaces ? 'complet' : 'ouvert';

            $updateSql = "UPDATE `event` SET Statut = :statut WHERE ID = :id";
            $req = $db->prepare($updateSql);
            $req->execute([':statut' => $newStatus, ':id' => $idEvent]);
        } catch (Exception $e) {
            // Keep registration operation successful even if status sync fails.
        }
    }

    // ─── Statistics ────────────────────────────────────────────────

    public function getStatistics() : array {
        $db = config::getConnexion();
        
        // Total registrations
        $totalRegistrations = count($this->getAll());
        
        // Registrations by status
        $regStatuses = ['inscrit' => 0, 'confirmé' => 0, 'annulé' => 0];
        $regStatusSql = "SELECT Statut, COUNT(*) as count FROM `registration` GROUP BY Statut";
        try {
            $req = $db->query($regStatusSql);
            while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                if (isset($regStatuses[$row['Statut']])) {
                    $regStatuses[$row['Statut']] = intval($row['count']);
                }
            }
        } catch (Exception $e) {
            // Silently fail
        }

        // Registrations by event type
        $types = [];
        $typeSql = "SELECT e.Type, COUNT(*) as count
                     FROM `registration` r
                     JOIN `event` e ON r.IDEvent = e.ID
                     GROUP BY e.Type
                     ORDER BY count DESC";
        try {
            $req = $db->query($typeSql);
            while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                $types[$row['Type']] = intval($row['count']);
            }
        } catch (Exception $e) {
            // Silently fail
        }

        // Average registrations per event
        $totalEvents = 0;
        $avgRegistrations = 0;
        $eventCountSql = "SELECT COUNT(*) FROM `event`";
        try {
            $totalEvents = intval($db->query($eventCountSql)->fetchColumn());
            $avgRegistrations = $totalEvents > 0 ? round($totalRegistrations / $totalEvents, 2) : 0;
        } catch (Exception $e) {
            // Silently fail
        }

        // Most registered events (top 5)
        $popularEvents = [];
        $popularSql = "
            SELECT e.ID, e.Titre, e.Type, e.dateEvent,
                   (SELECT COUNT(*) FROM `registration` r WHERE r.IDEvent = e.ID AND r.Statut IN ('inscrit', 'confirmé')) as reg_count
            FROM `event` e
            ORDER BY reg_count DESC
            LIMIT 5
        ";
        try {
            $req = $db->query($popularSql);
            while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                $popularEvents[] = $row;
            }
        } catch (Exception $e) {
            // Silently fail
        }

        return [
            'totalRegistrations' => $totalRegistrations,
            'registrationsByStatus' => $regStatuses,
            'registrationsByType' => $types,
            'avgRegistrationsPerEvent' => $avgRegistrations,
            'popularEvents' => $popularEvents
        ];
    }
}

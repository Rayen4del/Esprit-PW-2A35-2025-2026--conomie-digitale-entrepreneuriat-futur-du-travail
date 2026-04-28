<?php
include_once(__DIR__ . '/../../config.php');
include_once(__DIR__ . '/../../model/Evenement.php');

class EvenementController {
    private string $lastError = '';
    private array $activeRegistrationStatuses = ['inscrit', 'confirmé'];

    // ─── Read ──────────────────────────────────────────────────────

    public function getAll() : array {
        $sql = "SELECT e.*,
                       (SELECT COUNT(*)
                        FROM `registration` r
                        WHERE r.IDEvent = e.ID
                          AND r.Statut IN ('inscrit', 'confirmé')) AS inscrits_count
                FROM `event` e
                ORDER BY e.dateEvent DESC";
        $db  = config::getConnexion();
        try {
            $stmt = $db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                $nbplaces = intval($row['nbplaces'] ?? 0);
                $inscrits = intval($row['inscrits_count'] ?? 0);
                $row['places_restantes'] = max(0, $nbplaces - $inscrits);
            }
            return $rows;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Alias so existing calls to listEvenements() still work
    public function listEvenements() : array {
        return $this->getAll();
    }

    public function getById($id) {
        $sql = "SELECT e.*,
                       (SELECT COUNT(*)
                        FROM `registration` r
                        WHERE r.IDEvent = e.ID
                          AND r.Statut IN ('inscrit', 'confirmé')) AS inscrits_count
                FROM `event` e
                WHERE e.ID = :id";
        $db  = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id, PDO::PARAM_INT);
            $req->execute();
            $row = $req->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return false;
            }
            $nbplaces = intval($row['nbplaces'] ?? 0);
            $inscrits = intval($row['inscrits_count'] ?? 0);
            $row['places_restantes'] = max(0, $nbplaces - $inscrits);
            return $row;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function filtrer($search = '', $statut = '', $type = '', $limit = null, $offset = null) : array {
        $sql    = "SELECT e.*,
                       (SELECT COUNT(*)
                        FROM `registration` r
                        WHERE r.IDEvent = e.ID
                          AND r.Statut IN ('inscrit', 'confirmé')) AS inscrits_count
                FROM `event` e
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (Titre LIKE :search OR Description LIKE :search OR lieu_lien LIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($statut !== '') {
            $sql .= " AND Statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($type !== '') {
            $sql .= " AND Type = :type";
            $params[':type'] = $type;
        }
        $sql .= " ORDER BY dateEvent DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit);
            if ($offset !== null) {
                $sql .= " OFFSET " . intval($offset);
            }
        }

        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute($params);
            $rows = $req->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                $nbplaces = intval($row['nbplaces'] ?? 0);
                $inscrits = intval($row['inscrits_count'] ?? 0);
                $row['places_restantes'] = max(0, $nbplaces - $inscrits);
            }
            return $rows;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function countFiltered($search = '', $statut = '', $type = '') : int {
        $sql    = "SELECT COUNT(*) FROM `event` WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (Titre LIKE :search OR Description LIKE :search OR lieu_lien LIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($statut !== '') {
            $sql .= " AND Statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($type !== '') {
            $sql .= " AND Type = :type";
            $params[':type'] = $type;
        }

        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute($params);
            return intval($req->fetchColumn());
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ─── Create ────────────────────────────────────────────────────

    public function addEvenement(Evenement $evenement) : bool {
        $sql = "INSERT INTO `event` (Titre, Type, Description, dateEvent, duree, lieu_lien, Statut, nbplaces)
                VALUES (:titre, :type, :description, :dateEvent, :duree, :lieu_lien, :statut, :nbplaces)";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':titre'       => $evenement->getTitre(),
                ':type'        => $evenement->getType(),
                ':description' => $evenement->getDescription(),
                ':dateEvent'   => $evenement->getDateEvent(),
                ':duree'       => $evenement->getDuree(),
                ':lieu_lien'   => $evenement->getLieuLien(),
                ':statut'      => $evenement->getStatut(),
                ':nbplaces'    => $evenement->getNbplaces(),
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ─── Update ────────────────────────────────────────────────────

    public function updateEvenement(Evenement $evenement, $id) : bool {
        $sql = "UPDATE `event` SET
                    Titre       = :titre,
                    Type        = :type,
                    Description = :description,
                    dateEvent   = :dateEvent,
                    duree       = :duree,
                    lieu_lien   = :lieu_lien,
                    Statut      = :statut,
                    nbplaces    = :nbplaces
                WHERE ID = :id";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':titre'       => $evenement->getTitre(),
                ':type'        => $evenement->getType(),
                ':description' => $evenement->getDescription(),
                ':dateEvent'   => $evenement->getDateEvent(),
                ':duree'       => $evenement->getDuree(),
                ':lieu_lien'   => $evenement->getLieuLien(),
                ':statut'      => $evenement->getStatut(),
                ':nbplaces'    => $evenement->getNbplaces(),
                ':id'          => $id,
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ─── Delete ────────────────────────────────────────────────────

    public function deleteEvenement($id) : bool {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();

            $deleteRegistrations = "DELETE FROM `registration` WHERE IDEvent = :id";
            $req = $db->prepare($deleteRegistrations);
            $req->execute([':id' => $id]);

            $deleteEvent = "DELETE FROM `event` WHERE ID = :id";
            $req = $db->prepare($deleteEvent);
            $req->execute([':id' => $id]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // Alias for backward compatibility
    public function supprimer($id) : bool {
        return $this->deleteEvenement($id);
    }

    // ─── Registration ──────────────────────────────────────────────

    public function inscrire(int $idUtilisateur, int $idEvent, string $statut = 'inscrit') : bool {
        if ($this->isAlreadyRegistered($idUtilisateur, $idEvent)) {
            $this->lastError = "Inscription déjà existante.";
            return false;
        }

        if (in_array($statut, $this->activeRegistrationStatuses, true) && !$this->hasAvailablePlaces($idEvent)) {
            $this->lastError = "Plus de places disponibles pour cet événement.";
            return false;
        }

        $sql = "INSERT INTO `registration` (IDUtilisateur, IDEvent, DateInscription, Statut)
                VALUES (:idUser, :idEvent, :date, :statut)";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([
                ':idUser'  => $idUtilisateur,
                ':idEvent' => $idEvent,
                ':date'    => date('Y-m-d'),
                ':statut'  => $statut,
            ]);
            $this->syncEventStatusByCapacity($idEvent);
            $this->lastError = '';
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

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

    public function getRegistrationsByUser(int $idUtilisateur) : array {
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

    public function getAllRegistrations() : array {
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
            return [];
        }
    }

    public function getRegistrationsForEvent(int $eventId) : array {
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

    public function filtrerRegistrations($search = '', $statut = '', $type = '') : array {
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
            return [];
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

    public function updateRegistration(int $id, int $idUtilisateur, int $idEvent, string $statut) : bool {
        $oldRegistration = $this->getRegistrationById($id);
        if (!$oldRegistration) {
            $this->lastError = "Inscription introuvable.";
            return false;
        }

        $oldEventId = intval($oldRegistration['IDEvent']);
        $oldStatut  = $oldRegistration['Statut'];

        $becameActive = !in_array($oldStatut, $this->activeRegistrationStatuses, true)
            && in_array($statut, $this->activeRegistrationStatuses, true);
        $eventChanged = $oldEventId !== $idEvent;

        if (($eventChanged || $becameActive) && in_array($statut, $this->activeRegistrationStatuses, true) && !$this->hasAvailablePlaces($idEvent, $id)) {
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
                ':idUser'  => $idUtilisateur,
                ':idEvent' => $idEvent,
                ':statut'  => $statut,
            ]);
            $this->syncEventStatusByCapacity($idEvent);
            if ($oldEventId !== $idEvent) {
                $this->syncEventStatusByCapacity($oldEventId);
            }
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function deleteRegistration(int $id) : bool {
        $oldRegistration = $this->getRegistrationById($id);
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

    private function getRegistrationById(int $id) {
        $sql = "SELECT * FROM `registration` WHERE ID = :id";
        $db = config::getConnexion();

        try {
            $req = $db->prepare($sql);
            $req->execute([':id' => $id]);
            return $req->fetch(PDO::FETCH_ASSOC);
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
        $event = $this->getById($idEvent);
        if (!$event) {
            return false;
        }

        $nbplaces = intval($event['nbplaces'] ?? 0);
        $activeCount = $this->getActiveRegistrationsCount($idEvent, $excludeRegistrationId);
        return $activeCount < $nbplaces;
    }

    private function syncEventStatusByCapacity(int $idEvent) : void {
        $event = $this->getById($idEvent);
        if (!$event) {
            return;
        }

        $nbplaces = intval($event['nbplaces'] ?? 0);
        $activeCount = $this->getActiveRegistrationsCount($idEvent);
        $newStatus = $activeCount >= $nbplaces ? 'complet' : 'ouvert';

        $sql = "UPDATE `event` SET Statut = :statut WHERE ID = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute([':statut' => $newStatus, ':id' => $idEvent]);
        } catch (Exception $e) {
            // Keep registration operation successful even if status sync fails.
        }
    }

    // ─── Statistics ────────────────────────────────────────────────

    public function getStatistics() : array {
        $db = config::getConnexion();
        
        // Total events and registrations
        $totalEvents = $this->countFiltered();
        $totalRegistrations = count($this->getAllRegistrations());
        
        // Events by status
        $statuses = ['ouvert' => 0, 'ferme' => 0, 'complet' => 0];
        $statusSql = "SELECT Statut, COUNT(*) as count FROM `event` GROUP BY Statut";
        try {
            $req = $db->query($statusSql);
            while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                if (isset($statuses[$row['Statut']])) {
                    $statuses[$row['Statut']] = intval($row['count']);
                }
            }
        } catch (Exception $e) {
            // Silently fail
        }

        // Events by type
        $types = [];
        $typeSql = "SELECT Type, COUNT(*) as count FROM `event` GROUP BY Type ORDER BY count DESC";
        try {
            $req = $db->query($typeSql);
            while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                $types[$row['Type']] = intval($row['count']);
            }
        } catch (Exception $e) {
            // Silently fail
        }

        // Upcoming vs Past events
        $upcomingCount = 0;
        $pastCount = 0;
        $upcomingSql = "SELECT COUNT(*) as count FROM `event` WHERE dateEvent >= CURDATE()";
        $pastSql = "SELECT COUNT(*) as count FROM `event` WHERE dateEvent < CURDATE()";
        try {
            $upcomingCount = intval($db->query($upcomingSql)->fetchColumn());
            $pastCount = intval($db->query($pastSql)->fetchColumn());
        } catch (Exception $e) {
            // Silently fail
        }

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

        // Average registrations per event
        $avgRegistrations = $totalEvents > 0 ? round($totalRegistrations / $totalEvents, 2) : 0;

        // Most popular events (top 5)
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
            'totalEvents' => $totalEvents,
            'totalRegistrations' => $totalRegistrations,
            'eventsByStatus' => $statuses,
            'eventsByType' => $types,
            'upcomingEvents' => $upcomingCount,
            'pastEvents' => $pastCount,
            'registrationsByStatus' => $regStatuses,
            'avgRegistrationsPerEvent' => $avgRegistrations,
            'popularEvents' => $popularEvents
        ];
    }
}

<?php
// controller/gestion_utilisateur/UserController.php
require_once __DIR__ . '/../../model/gestion_utilisateur/config.php';
require_once __DIR__ . '/../../model/gestion_utilisateur/User.php';

class UserController {
    
    // Lister tous les utilisateurs
    public function listUsers() {
        $sql = "SELECT u.*, p.Bio, p.Photo, p.Localisation 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                ORDER BY u.ID DESC";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
    
    // Filtrer les utilisateurs
    public function filterUsers($type = null, $statut = null) {
        $sql = "SELECT u.*, p.Bio, p.Photo 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                WHERE 1=1";
        
        if ($type && $type !== 'all') {
            $sql .= " AND u.Type = '$type'";
        }
        if ($statut && $statut !== 'all') {
            $sql .= " AND u.Statut = '$statut'";
        }
        $sql .= " ORDER BY u.ID DESC";
        
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Compter les utilisateurs filtrés
    public function countFilteredUsers($type = null, $statut = null, $search = null) {
        $sql = "SELECT COUNT(*) 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                WHERE 1=1";

        $params = [];

        if ($type && $type !== 'all') {
            $sql .= " AND u.Type = :type";
            $params['type'] = $type;
        }

        if ($statut && $statut !== 'all') {
            $sql .= " AND u.Statut = :statut";
            $params['statut'] = $statut;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (u.Nom LIKE :search OR u.Email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return (int) $query->fetchColumn();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Filtrer les utilisateurs avec pagination
    public function filterUsersPaginated($type = null, $statut = null, $limit = 10, $offset = 0, $search = null, $sortDate = 'desc', $sortName = 'none') {
        $sql = "SELECT u.*, p.Bio, p.Photo 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                WHERE 1=1";

        $params = [];

        if ($type && $type !== 'all') {
            $sql .= " AND u.Type = :type";
            $params['type'] = $type;
        }

        if ($statut && $statut !== 'all') {
            $sql .= " AND u.Statut = :statut";
            $params['statut'] = $statut;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (u.Nom LIKE :search OR u.Email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $orderParts = [];

        if ($sortName === 'asc') {
            $orderParts[] = 'u.Nom ASC';
        } elseif ($sortName === 'desc') {
            $orderParts[] = 'u.Nom DESC';
        }

        if ($sortDate === 'asc') {
            $orderParts[] = 'u.created_at ASC';
        } else {
            $orderParts[] = 'u.created_at DESC';
        }

        if (empty($orderParts)) {
            $orderParts[] = 'u.ID DESC';
        }

        $sql .= " ORDER BY " . implode(', ', $orderParts) . " LIMIT :limit OFFSET :offset";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue(':' . $key, $value);
            }

            $query->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $query->execute();
            return $query;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Récupérer toutes les données utilisateurs + profil pour export (CSV/PDF)
    public function getUsersForExport($type = null, $statut = null, $search = null, $sortDate = 'desc', $sortName = 'none') {
        $db = config::getConnexion();

        $uCols = $this->getTableColumns('utilisateur');
        $pCols = $this->getTableColumns('profil');

        $selectParts = [];
        foreach ($uCols as $col) {
            $selectParts[] = "u.`$col` AS `u_$col`";
        }
        foreach ($pCols as $col) {
            $selectParts[] = "p.`$col` AS `p_$col`";
        }

        $sql = "SELECT " . implode(', ', $selectParts) . "
                FROM utilisateur u
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur
                WHERE 1=1";

        $params = [];

        if ($type && $type !== 'all') {
            $sql .= " AND u.Type = :type";
            $params['type'] = $type;
        }

        if ($statut && $statut !== 'all') {
            $sql .= " AND u.Statut = :statut";
            $params['statut'] = $statut;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (u.Nom LIKE :search OR u.Email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $orderParts = [];

        if ($sortName === 'asc') {
            $orderParts[] = 'u.Nom ASC';
        } elseif ($sortName === 'desc') {
            $orderParts[] = 'u.Nom DESC';
        }

        if ($sortDate === 'asc') {
            $orderParts[] = 'u.created_at ASC';
        } else {
            $orderParts[] = 'u.created_at DESC';
        }

        if (empty($orderParts)) {
            $orderParts[] = 'u.ID DESC';
        }

        $sql .= " ORDER BY " . implode(', ', $orderParts);

        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Récupérer un utilisateur par ID pour export (toutes les colonnes)
    public function getUserForExportById($id) {
        $db = config::getConnexion();

        $uCols = $this->getTableColumns('utilisateur');
        $pCols = $this->getTableColumns('profil');

        $selectParts = [];
        foreach ($uCols as $col) {
            $selectParts[] = "u.`$col` AS `u_$col`";
        }
        foreach ($pCols as $col) {
            $selectParts[] = "p.`$col` AS `p_$col`";
        }

        $sql = "SELECT " . implode(', ', $selectParts) . "
                FROM utilisateur u
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur
                WHERE u.ID = :id
                LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    private function getTableColumns($tableName) {
        $db = config::getConnexion();
        $columns = [];

        try {
            $query = $db->query("SHOW COLUMNS FROM `$tableName`");
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['Field'])) {
                    $columns[] = $row['Field'];
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $columns;
    }
    
    // Supprimer un utilisateur
    public function deleteUser($id) {
        $sql = "DELETE FROM utilisateur WHERE ID = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
    
    // Ajouter un utilisateur
    public function addUser(User $user) {
        $sql = "INSERT INTO utilisateur (Nom, Email, MDP, Type, Statut, created_at) 
                VALUES (:nom, :email, :mdp, :type, :statut, NOW())";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'mdp' => password_hash($user->getMdp(), PASSWORD_DEFAULT),
                'type' => $user->getType(),
                'statut' => $user->getStatut()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }
    
    // Mettre à jour un utilisateur
    public function updateUser(User $user, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE utilisateur SET 
                    Nom = :nom,
                    Email = :email,
                    Type = :type,
                    Statut = :statut
                WHERE ID = :id'
            );
            $query->execute([
                'id' => $id,
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'type' => $user->getType(),
                'statut' => $user->getStatut()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    // Mettre à jour le mot de passe
    public function updatePassword($id, $newPassword) {
        try {
            $db = config::getConnexion();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = $db->prepare('UPDATE utilisateur SET MDP = :mdp WHERE ID = :id');
            $query->execute(['id' => $id, 'mdp' => $hashedPassword]);
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Afficher un utilisateur par ID
    public function showUser($id) {
        $sql = "SELECT u.*, p.Bio, p.Photo, p.Localisation 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                WHERE u.ID = $id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        try {
            $query->execute();
            $user = $query->fetch();
            return $user;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    
    // Authentifier un utilisateur
    public function authenticate($email, $password) {
        $sql = "SELECT * FROM utilisateur WHERE Email = :email";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        
        if ($user && password_verify($password, $user['MDP'])) {
            if ($user['Statut'] === 'suspendu') {
                return ['success' => false, 'message' => 'Compte suspendu'];
            }
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }
    
    // Vérifier si email existe
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM utilisateur WHERE Email = :email";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['email' => $email]);
        return $query->fetchColumn() > 0;
    }
    
    // Changer le statut
    public function changeStatus($id, $statut) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare('UPDATE utilisateur SET Statut = :statut WHERE ID = :id');
            $query->execute(['id' => $id, 'statut' => $statut]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Statistiques
    public function getStats() {
        $db = config::getConnexion();
        $stats = [];
        
        $query = $db->query("SELECT COUNT(*) FROM utilisateur");
        $stats['total'] = $query->fetchColumn();
        
        $query = $db->query("SELECT Type, COUNT(*) as count FROM utilisateur GROUP BY Type");
        $stats['by_type'] = $query->fetchAll();
        
        $query = $db->query("SELECT Statut, COUNT(*) as count FROM utilisateur GROUP BY Statut");
        $stats['by_status'] = $query->fetchAll();
        
        return $stats;
    }
}
?>
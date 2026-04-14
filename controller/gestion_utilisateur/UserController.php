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
<?php
require_once __DIR__ . '/../config.php';

class FavoritesController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    /**
     * Ajouter une opportunite aux favoris
     */
    public function addFavorite($userId, $opportunityId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO favoris (IDUtilisateur, idOportunity) 
                VALUES (:userId, :oppId)
            ");
            $stmt->execute([
                ':userId' => $userId,
                ':oppId' => $opportunityId
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur ajout favori : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retirer une opportunite des favoris
     */
    public function removeFavorite($userId, $opportunityId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM favoris 
                WHERE IDUtilisateur = :userId AND idOportunity = :oppId
            ");
            $stmt->execute([
                ':userId' => $userId,
                ':oppId' => $opportunityId
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur retrait favori : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifier si une opportunite est favorite
     */
    public function isFavorited($userId, $opportunityId) {
        try {
            $stmt = $this->db->prepare("
                SELECT ID FROM favoris 
                WHERE IDUtilisateur = :userId AND idOportunity = :oppId 
                LIMIT 1
            ");
            $stmt->execute([
                ':userId' => $userId,
                ':oppId' => $opportunityId
            ]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log('Erreur verification favori : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recuperer toutes les opportunites favorites d un utilisateur
     */
    public function getUserFavorites($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    f.ID as fav_id,
                    f.dateFav,
                    o.* 
                FROM favoris f
                JOIN oportunity o ON f.idOportunity = o.ID
                WHERE f.IDUtilisateur = :userId
                ORDER BY f.dateFav DESC
            ");
            $stmt->execute([':userId' => $userId]);
            return $stmt;
        } catch (Exception $e) {
            error_log('Erreur recuperation favoris utilisateur : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Basculer un favori en AJAX
     */
    public function toggleFavorite($userId, $opportunityId) {
        header('Content-Type: application/json');
        
        if (!$userId || !$opportunityId) {
            echo json_encode(['success' => false, 'message' => 'Parametres manquants']);
            exit;
        }

        $isFavorited = $this->isFavorited($userId, $opportunityId);
        
        if ($isFavorited) {
            $result = $this->removeFavorite($userId, $opportunityId);
            echo json_encode([
                'success' => $result,
                'favorited' => false,
                'message' => $result ? 'Retire des favoris' : 'Erreur lors du retrait du favori'
            ]);
        } else {
            $result = $this->addFavorite($userId, $opportunityId);
            echo json_encode([
                'success' => $result,
                'favorited' => true,
                'message' => $result ? 'Ajoute aux favoris' : 'Erreur lors de l ajout du favori'
            ]);
        }
        exit;
    }
}
?>

<?php
require_once __DIR__ . '/../config.php';

class FavoritesController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    /**
     * Add an opportunity to favorites
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
            error_log('Add favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove an opportunity from favorites
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
            error_log('Remove favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if an opportunity is favorited by user
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
            error_log('Check favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all favorited opportunities for a user with details
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
            error_log('Get user favorites error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle AJAX favorite toggle
     */
    public function toggleFavorite($userId, $opportunityId) {
        header('Content-Type: application/json');
        
        if (!$userId || !$opportunityId) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }

        $isFavorited = $this->isFavorited($userId, $opportunityId);
        
        if ($isFavorited) {
            $result = $this->removeFavorite($userId, $opportunityId);
            echo json_encode([
                'success' => $result,
                'favorited' => false,
                'message' => $result ? 'Removed from favorites' : 'Error removing favorite'
            ]);
        } else {
            $result = $this->addFavorite($userId, $opportunityId);
            echo json_encode([
                'success' => $result,
                'favorited' => true,
                'message' => $result ? 'Added to favorites' : 'Error adding favorite'
            ]);
        }
        exit;
    }
}
?>

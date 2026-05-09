<?php
// model/blog/SavedPost.php

require_once __DIR__ . '/../../config.php';

class SavedPost
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // Save a post for user
    public function save($userId, $postId)
    {
        $sql = "INSERT INTO saved_posts (user_id, post_id) 
                VALUES (:user_id, :post_id)
                ON DUPLICATE KEY UPDATE saved_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':post_id' => $postId
        ]);
    }

    // Unsave a post
    public function unsave($userId, $postId)
    {
        $sql = "DELETE FROM saved_posts WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':post_id' => $postId
        ]);
    }

    // Check if post is saved
    public function isSaved($userId, $postId)
    {
        $sql = "SELECT COUNT(*) as count FROM saved_posts 
                WHERE user_id = :user_id AND post_id = :post_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':post_id' => $postId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (bool)$result['count'];
    }

    // Get saved posts for user
    public function getSavedPosts($userId)
    {
        $sql = "SELECT p.*, u.Nom AS auteur, sp.saved_at
                FROM saved_posts sp
                JOIN post p ON sp.post_id = p.ID
                JOIN utilisateur u ON p.idUtilisateur = u.ID
                WHERE sp.user_id = :user_id
                ORDER BY sp.saved_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count saved posts
    public function countSavedPosts($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM saved_posts WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Delete a saved post
    public function delete($id)
    {
        $sql = "DELETE FROM saved_posts WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
?>

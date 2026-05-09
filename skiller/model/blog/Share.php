<?php
// model/blog/Share.php

require_once __DIR__ . '/../../config.php';

class Share
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // Generate a unique share token
    private function generateShareToken()
    {
        return bin2hex(random_bytes(16));
    }

    // Create a shareable link for a post
    public function createShare($postId, $userId)
    {
        // Check if share already exists
        $sql = "SELECT * FROM post_shares WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return $existing['share_token'];
        }

        // Create new share
        $token = $this->generateShareToken();
        $sql = "INSERT INTO post_shares (post_id, user_id, share_token)
                VALUES (:post_id, :user_id, :share_token)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
            ':share_token' => $token
        ]);

        return $token;
    }

    // Get post by share token
    public function getPostByToken($token)
    {
        $sql = "SELECT p.*, u.Nom AS auteur, ps.created_at as shared_at
                FROM post_shares ps
                JOIN post p ON ps.post_id = p.ID
                JOIN utilisateur u ON p.idUtilisateur = u.ID
                WHERE ps.share_token = :token";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get share URL
    public function getShareUrl($token)
    {
        $base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
        return rtrim($base, '/') . '/view/gestion_blog/front_office/posts/shared.php?token=' . $token;
    }

    // Get full share URL
    public function getFullShareUrl($token)
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . $this->getShareUrl($token);
    }

    // Get share info
    public function getShare($shareId)
    {
        $sql = "SELECT * FROM post_shares WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $shareId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete a share
    public function deleteShare($shareId)
    {
        $sql = "DELETE FROM post_shares WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $shareId]);
    }
}
?>

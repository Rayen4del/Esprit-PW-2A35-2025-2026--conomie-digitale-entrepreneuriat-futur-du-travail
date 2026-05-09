<?php
// model/blog/Notification.php

require_once __DIR__ . '/../../config.php';

class Notification
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // Create a notification
    public function create($userId, $actorId, $type, $postId, $message)
    {
        $sql = "INSERT INTO notifications (user_id, actor_id, type, post_id, message, is_read)
                VALUES (:user_id, :actor_id, :type, :post_id, :message, 0)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':actor_id' => $actorId,
            ':type' => $type,
            ':post_id' => $postId,
            ':message' => $message
        ]);
        
        return $this->pdo->lastInsertId();
    }

    // Get unread notifications for a user
    public function getUnread($userId)
    {
        $sql = "SELECT n.*, u.Nom AS actor_name, p.Titre AS post_title
                FROM notifications n
                JOIN utilisateur u ON n.actor_id = u.ID
                JOIN post p ON n.post_id = p.ID
                WHERE n.user_id = :user_id AND n.is_read = 0
                ORDER BY n.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all notifications for a user
    public function getAll($userId, $limit = 50)
    {
        $sql = "SELECT n.*, u.Nom AS actor_name, p.Titre AS post_title
                FROM notifications n
                JOIN utilisateur u ON n.actor_id = u.ID
                JOIN post p ON n.post_id = p.ID
                WHERE n.user_id = :user_id
                ORDER BY n.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count unread notifications
    public function countUnread($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Mark as read
    public function markAsRead($notificationId)
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $notificationId]);
    }

    // Mark all as read for user
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    // Delete old notifications (older than 30 days)
    public function deleteOld()
    {
        $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
}
?>

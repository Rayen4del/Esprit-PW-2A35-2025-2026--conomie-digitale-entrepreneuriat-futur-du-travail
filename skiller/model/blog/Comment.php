<?php
require_once __DIR__ . '/../../config.php';

class Comment
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // ─── CREATE ───────────────────────────────────────────────

    public function create($idUtilisateur, $idPost, $contenu)
    {
        $sql = "INSERT INTO commentaire (IDUtilisateur, IDPost, Contenu, DateCom)
                VALUES (:idUtilisateur, :idPost, :contenu, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idUtilisateur' => $idUtilisateur,
            ':idPost'        => $idPost,
            ':contenu'       => $contenu
        ]);
        return $this->pdo->lastInsertId();
    }

    // ─── READ ALL COMMENTS FOR A POST ─────────────────────────

    public function getByPost($idPost)
    {
        $sql = "SELECT c.*, u.Nom AS auteur
                FROM commentaire c
                JOIN utilisateur u ON c.IDUtilisateur = u.ID
                WHERE c.IDPost = :idPost
                ORDER BY c.DateCom ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idPost' => $idPost]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── READ ALL COMMENTS (admin view) ───────────────────────

    public function getAll($search = '')
    {
        $sql = "SELECT c.*, u.Nom AS auteur, p.Titre AS post_titre
                FROM commentaire c
                JOIN utilisateur u ON c.IDUtilisateur = u.ID
                JOIN post p ON c.IDPost = p.ID
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (c.Contenu LIKE :search OR u.Nom LIKE :search OR p.Titre LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY c.DateCom DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── READ ONE ─────────────────────────────────────────────

    public function getById($id)
    {
        $sql = "SELECT c.*, u.Nom AS auteur, p.Titre AS post_titre
                FROM commentaire c
                JOIN utilisateur u ON c.IDUtilisateur = u.ID
                JOIN post p ON c.IDPost = p.ID
                WHERE c.ID = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ─── READ BY USER ─────────────────────────────────────────

    public function getByUser($idUtilisateur)
    {
        $sql = "SELECT c.*, p.Titre AS post_titre
                FROM commentaire c
                JOIN post p ON c.IDPost = p.ID
                WHERE c.IDUtilisateur = :idUtilisateur
                ORDER BY c.DateCom DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── UPDATE ───────────────────────────────────────────────

    public function update($id, $contenu)
    {
        $sql = "UPDATE commentaire 
                SET Contenu = :contenu 
                WHERE ID = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':contenu' => $contenu,
            ':id'      => $id
        ]);
    }

    // ─── DELETE ───────────────────────────────────────────────

    public function delete($id)
    {
        $sql = "DELETE FROM commentaire WHERE ID = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // ─── DELETE ALL COMMENTS FOR A POST ───────────────────────

    public function deleteByPost($idPost)
    {
        $sql = "DELETE FROM commentaire WHERE IDPost = :idPost";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':idPost' => $idPost]);
    }

    // ─── COUNT COMMENTS BY USER ───────────────────────────────

    public function countByUser($idUtilisateur)
    {
        $sql = "SELECT COUNT(*) as total FROM commentaire WHERE IDUtilisateur = :idUtilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // ─── NEW METHODS FOR FRONT OFFICE (using your table structure) ───

    public function getByPostId($postId)
    {
        $sql = "SELECT c.*, u.Nom AS auteur
                FROM commentaire c
                LEFT JOIN utilisateur u ON c.IDUtilisateur = u.ID
                WHERE c.IDPost = :post_id
                ORDER BY c.DateCom ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':post_id' => $postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastComment($postId, $userId)
    {
        $sql = "SELECT c.*, u.Nom AS auteur
                FROM commentaire c
                LEFT JOIN utilisateur u ON c.IDUtilisateur = u.ID
                WHERE c.IDPost = :post_id AND c.IDUtilisateur = :user_id
                ORDER BY c.ID DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createComment($postId, $userId, $content)
    {
        $sql = "INSERT INTO commentaire (IDPost, IDUtilisateur, Contenu, DateCom) 
                VALUES (:post_id, :user_id, :content, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
            ':content' => $content
        ]);
    }

    // ─── COMMENT LIKES METHODS ───────────────────────────────────

    public function likeComment($userId, $commentId)
    {
        $sql = "INSERT INTO comment_likes (user_id, comment_id, created_at) VALUES (:user_id, :comment_id, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':comment_id' => $commentId]);
    }

    public function unlikeComment($userId, $commentId)
    {
        $sql = "DELETE FROM comment_likes WHERE user_id = :user_id AND comment_id = :comment_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':comment_id' => $commentId]);
    }

    public function hasUserLikedComment($userId, $commentId)
    {
        $sql = "SELECT COUNT(*) FROM comment_likes WHERE user_id = :user_id AND comment_id = :comment_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':comment_id' => $commentId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getCommentLikeCount($commentId)
    {
        $sql = "SELECT COUNT(*) FROM comment_likes WHERE comment_id = :comment_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':comment_id' => $commentId]);
        return (int)$stmt->fetchColumn();
    }
    public function countByPost($postId)
{
    $sql = "SELECT COUNT(*) as total FROM commentaire WHERE IDPost = :post_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':post_id' => $postId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}
}
?>
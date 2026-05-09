<?php
require_once __DIR__ . '/../../config.php';

class Post
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // ─────────────────────────────────────────────
    // CREATE POST (SAFE FOR TESTING)
    // ─────────────────────────────────────────────
    public function create(
    $titre,
    $contenu,
    $categorie = null,
    $image = null,
    $media = null,
    $statut = 'publié',
    $idUtilisateur = null,
    $scheduledDate = null
) {
    if ($idUtilisateur === null) $idUtilisateur = 1;

    $sql = "INSERT INTO post 
            (Titre, Contenu, Categorie, Image, media, Statut, idUtilisateur, DatePublication)
            VALUES 
            (:titre, :contenu, :categorie, :image, :media, :statut, :idUtilisateur, 
             " . ($statut === 'planifié' && $scheduledDate ? ":datePublication" : "NOW()") . ")";

    $stmt = $this->pdo->prepare($sql);

    $stmt->bindValue(':titre', $titre);
    $stmt->bindValue(':contenu', $contenu);
    $stmt->bindValue(':categorie', $categorie);
    $stmt->bindValue(':image', $image);
    $stmt->bindValue(':media', $media);
    $stmt->bindValue(':statut', $statut);
    $stmt->bindValue(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);

    if ($statut === 'planifié' && $scheduledDate) {
        $stmt->bindValue(':datePublication', $scheduledDate);
    }

    $stmt->execute();
    return $this->pdo->lastInsertId();
}

    // ─────────────────────────────────────────────
    // GET ALL POSTS
    // ─────────────────────────────────────────────
    public function getAll($search = '', $categorie = '', $statut = '')
    {
        $sql = "SELECT p.*, u.Nom AS auteur
                FROM post p
                JOIN utilisateur u ON p.idUtilisateur = u.ID
                WHERE p.deleted_at IS NULL";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.Titre LIKE :search OR p.Contenu LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if (!empty($categorie)) {
            $sql .= " AND p.Categorie = :categorie";
            $params[':categorie'] = $categorie;
        }

        if (!empty($statut)) {
            $sql .= " AND p.Statut = :statut";
            $params[':statut'] = $statut;
        }

        $sql .= " ORDER BY p.DatePublication DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────
    // GET BY ID
    // ─────────────────────────────────────────────
    public function getById($id)
    {
        $sql = "SELECT p.*, u.Nom AS auteur
                FROM post p
                JOIN utilisateur u ON p.idUtilisateur = u.ID
                WHERE p.ID = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    // ─────────────────────────────────────────────
    // GET BY USER
    // ─────────────────────────────────────────────
    public function getByUser($idUtilisateur)
    {
        $sql = "SELECT * FROM post
                WHERE idUtilisateur = :idUtilisateur AND deleted_at IS NULL
                ORDER BY DatePublication DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur]);

        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────
    // UPDATE POST
    // ─────────────────────────────────────────────
    public function update($id, $titre, $contenu, $categorie, $image = null, $media = null, $statut = null)
    {
        $sql = "UPDATE post
                SET Titre = :titre,
                    Contenu = :contenu,
                    Categorie = :categorie,
                    Image = :image,
                    media = :media";

        $params = [
            ':titre' => $titre,
            ':contenu' => $contenu,
            ':categorie' => $categorie,
            ':image' => $image,
            ':media' => $media,
            ':id' => $id
        ];

        if ($statut !== null) {
            $sql .= ", Statut = :statut";
            $params[':statut'] = $statut;
        }

        $sql .= " WHERE ID = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // ─────────────────────────────────────────────
    // UPDATE STATUS
    // ─────────────────────────────────────────────
    public function updateStatut($id, $statut)
    {
        $sql = "UPDATE post SET Statut = :statut WHERE ID = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':statut' => $statut,
            ':id' => $id
        ]);
    }


    // ─────────────────────────────────────────────
    // SOFT DELETE (For Front Office Users)
    // ─────────────────────────────────────────────
    public function softDelete($id)
    {
        $sql = "UPDATE post SET deleted_at = NOW() WHERE ID = :id AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Alias for backward compatibility
    public function delete($id)
    {
        return $this->softDelete($id);
    }

     // ─────────────────────────────────────────────
    // HARD DELETE - Cleans ALL related data
    // ─────────────────────────────────────────────
public function hardDelete($id)
{
    try {
        $this->pdo->beginTransaction();

        echo "🔄 Starting hardDelete for post ID: $id<br>";

        // 1. Comment likes
        $this->pdo->prepare("DELETE FROM comment_likes 
            WHERE comment_id IN (SELECT ID FROM commentaire WHERE IDPost = ?)")->execute([$id]);
        echo "✅ comment_likes deleted<br>";

        // 2. Comments
        $this->pdo->prepare("DELETE FROM commentaire WHERE IDPost = ?")->execute([$id]);
        echo "✅ comments deleted<br>";

        // 3. Post likes
        $this->pdo->prepare("DELETE FROM post_likes WHERE post_id = ?")->execute([$id]);
        echo "✅ post_likes deleted<br>";

        // 4. Post shares
        $this->pdo->prepare("DELETE FROM post_shares WHERE post_id = ?")->execute([$id]);
        echo "✅ post_shares deleted<br>";

        // 5. Saved posts
        $this->pdo->prepare("DELETE FROM saved_posts WHERE post_id = ?")->execute([$id]);
        echo "✅ saved_posts deleted<br>";

        // 6. Notifications - Safe version
        $this->pdo->prepare("DELETE FROM notifications WHERE post_id = ?")->execute([$id]);
        echo "✅ notifications deleted<br>";

        // 7. Delete the post itself
        $result = $this->pdo->prepare("DELETE FROM post WHERE ID = ?")->execute([$id]);
        echo "✅ Final post deletion: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";

        $this->pdo->commit();
        echo "<h3 style='color:green;'>🎉 Hard delete completed successfully!</h3>";

        return $result;

    } catch (Exception $e) {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        
        echo "<h3 style='color:red;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</h3>";
        return false;
    }
}
    // ─────────────────────────────────────────────
    // GET DELETED POSTS
    // ─────────────────────────────────────────────
    public function getDeleted()
    {
        $sql = "SELECT p.*, u.Nom AS auteur
                FROM post p
                JOIN utilisateur u ON p.idUtilisateur = u.ID
                WHERE p.deleted_at IS NOT NULL
                ORDER BY p.deleted_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────
    // RESTORE POST
    // ─────────────────────────────────────────────
    public function restore($id)
    {
        $sql = "UPDATE post SET deleted_at = NULL WHERE ID = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // ─────────────────────────────────────────────
    // GET CATEGORIES
    // ─────────────────────────────────────────────
    public function getCategories()
    {
        $sql = "SELECT DISTINCT Categorie FROM post WHERE Categorie IS NOT NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────
    // COUNT COMMENTS
    // ─────────────────────────────────────────────
    public function getCommentCount($idPost)
    {
        $sql = "SELECT COUNT(*) as total FROM commentaire WHERE IDPost = :idPost";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idPost' => $idPost]);

        $row = $stmt->fetch();

        return $row['total'];
    }
    // Add these methods to your Post.php model

// Add these methods to your Post.php model

public function likePost($userId, $postId) {
    $sql = "INSERT INTO post_likes (user_id, post_id, created_at) VALUES (:user_id, :post_id, NOW())";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
}

public function unlikePost($userId, $postId) {
    $sql = "DELETE FROM post_likes WHERE user_id = :user_id AND post_id = :post_id";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
}

public function hasUserLikedPost($userId, $postId) {
    $sql = "SELECT COUNT(*) FROM post_likes WHERE user_id = :user_id AND post_id = :post_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
    return $stmt->fetchColumn() > 0;
}

public function getPostLikeCount($postId) {
    $sql = "SELECT COUNT(*) FROM post_likes WHERE post_id = :post_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':post_id' => $postId]);
    return (int)$stmt->fetchColumn();
}
}

?>
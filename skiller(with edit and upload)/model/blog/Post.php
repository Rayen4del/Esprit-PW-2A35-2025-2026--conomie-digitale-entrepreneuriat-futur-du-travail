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
        $statut = 'pending',
        $idUtilisateur = null
    ) {
        // 🔥 TESTING FALLBACK USER (IMPORTANT FOR YOUR PRESENTATION)
        if ($idUtilisateur === null) {
            $idUtilisateur = 1; // <-- TEST USER ID
        }

        $sql = "INSERT INTO post 
                (Titre, Contenu, Categorie, Image, media, Statut, idUtilisateur, DatePublication)
                VALUES
                (:titre, :contenu, :categorie, :image, :media, :statut, :idUtilisateur, NOW())";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':titre', $titre);
        $stmt->bindValue(':contenu', $contenu);
        $stmt->bindValue(':categorie', $categorie);
        $stmt->bindValue(':image', $image);
        $stmt->bindValue(':media', $media);
        $stmt->bindValue(':statut', $statut);
        $stmt->bindValue(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);

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
                WHERE 1=1";

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
                WHERE idUtilisateur = :idUtilisateur
                ORDER BY DatePublication DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur]);

        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────
    // UPDATE POST
    // ─────────────────────────────────────────────
    public function update($id, $titre, $contenu, $categorie, $image = null, $media = null)
    {
        $sql = "UPDATE post
                SET Titre = :titre,
                    Contenu = :contenu,
                    Categorie = :categorie,
                    Image = :image,
                    media = :media
                WHERE ID = :id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':titre', $titre);
        $stmt->bindValue(':contenu', $contenu);
        $stmt->bindValue(':categorie', $categorie);
        $stmt->bindValue(':image', $image);
        $stmt->bindValue(':media', $media);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
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
    // DELETE POST
    // ─────────────────────────────────────────────
    public function delete($id)
    {
        $sql = "DELETE FROM post WHERE ID = :id";

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
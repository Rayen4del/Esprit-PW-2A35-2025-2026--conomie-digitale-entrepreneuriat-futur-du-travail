<?php
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';

$postModel     = new Post();
$currentUserId = 1;
$search        = trim($_GET['q'] ?? '');
$posts         = $postModel->getAll($search);

foreach ($posts as &$post) {
    $post['like_count'] = $postModel->getPostLikeCount($post['ID']);
    $post['user_liked'] = $postModel->hasUserLikedPost($currentUserId, $post['ID']);
}
unset($post);

if (empty($posts)) {
    echo '<div class="text-center py-5">
            <i class="bx bx-search bx-lg text-muted mb-3"></i>
            <p class="text-muted">Aucune publication trouvée pour "' . htmlspecialchars($search) . '"</p>
          </div>';
    exit;
}

foreach ($posts as $post):
    $postId    = $post['ID'];
    $postMedia = $post['media'] ?? $post['Media'] ?? '';
    $mediaExt  = $postMedia ? strtolower(pathinfo($postMedia, PATHINFO_EXTENSION)) : '';
    $isVideo   = in_array($mediaExt, ['mp4','webm','ogg','mov']);
    $isOwner   = $post['idUtilisateur'] == $currentUserId;
?>
<div class="card post-card" data-post-id="<?= $postId ?>">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar"><span class="avatar-initial rounded-circle bg-label-info">U</span></div>
                <div>
                    <strong><?= htmlspecialchars($post['auteur'] ?? 'Anonyme') ?></strong>
                    <small class="text-muted d-block"><?= date('M d, Y \a\t H:i', strtotime($post['DatePublication'] ?? 'now')) ?></small>
                </div>
            </div>
            <?php if ($isOwner): ?>
            <div class="dropdown">
                <button class="btn p-0" data-bs-toggle="dropdown"><i class="bx bx-dots-horizontal-rounded bx-sm"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="editPost(<?= $postId ?>); return false;"><i class="bx bx-edit me-2"></i>Modifier</a></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deletePost(<?= $postId ?>); return false;"><i class="bx bx-trash me-2"></i>Supprimer</a></li>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <h5 class="fw-bold post-title"><?= htmlspecialchars($post['Titre'] ?? '') ?></h5>
        <div class="post-content"><?= $post['Contenu'] ?? '' ?></div>

        <?php if (!empty($postMedia)): ?>
            <?php if ($isVideo): ?>
                <video controls class="post-media-video">
                    <source src="../../uploads/posts/<?= htmlspecialchars($postMedia) ?>"
                            type="video/<?= $mediaExt === 'mov' ? 'mp4' : $mediaExt ?>">
                </video>
            <?php else: ?>
                <img src="../../uploads/posts/<?= htmlspecialchars($postMedia) ?>" class="post-media-img" alt="Post image">
            <?php endif; ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between text-muted border-top pt-3 mt-2">
            <div onclick="toggleLike(<?= $postId ?>)" class="like-btn" data-post-id="<?= $postId ?>">
                <i class="bx <?= $post['user_liked'] ? 'bxs-heart' : 'bx-heart' ?>"
                   style="color:<?= $post['user_liked'] ? '#dc3545' : '' ?>"></i>
                <span class="like-count"><?= $post['like_count'] ?></span> J'aime
            </div>
            <div onclick="toggleComments(<?= $postId ?>)" style="cursor:pointer">
                <i class="bx bx-message-rounded-dots me-1"></i> Commentaires
            </div>
            <div style="cursor:pointer"><i class="bx bx-share-alt me-1"></i> Partager</div>
        </div>
    </div>

    <div id="comments-<?= $postId ?>" class="comment-section" style="display:none">
        <div id="comments-list-<?= $postId ?>">
            <p class="text-muted small text-center">Chargement des commentaires...</p>
        </div>
        <form onsubmit="submitComment(event, <?= $postId ?>)" class="mt-3">
            <div class="mb-2">
                <select name="status" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                    <option value="publié">📢 Publier maintenant</option>
                    <option value="planifié">⏰ Planifier le commentaire</option>
                </select>
                <input type="datetime-local" name="scheduled_date" class="form-control form-control-sm d-none" style="width: auto; display: inline-block; margin-left: 8px;">
            </div>
            <div class="d-flex gap-2">
                <input type="text" name="content" class="form-control" placeholder="Écrire un commentaire...">
                <button type="submit" class="btn btn-primary">Envoyer</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
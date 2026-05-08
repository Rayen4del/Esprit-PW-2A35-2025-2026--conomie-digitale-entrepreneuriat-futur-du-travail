<?php
// view/gestion_blog/backoffice/posts/view.php
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';

$postModel = new Post();
$commentModel = new Comment();

$postId = (int)($_GET['id'] ?? 0);
$post = $postModel->getById($postId);
$comments = $commentModel->getByPost($postId);

if (!$post) {
    echo '<div class="alert alert-danger"><i class="bx bx-error me-2"></i>Post not found.</div>';
    exit;
}
?>

<div class="post-detail">
    <!-- Post Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="avatar avatar-lg">
            <span class="avatar-initial rounded-circle bg-label-primary">U</span>
        </div>
        <div class="flex-grow-1">
            <h5 class="mb-1"><?= htmlspecialchars($post['Titre'] ?? 'Untitled') ?></h5>
            <div class="d-flex align-items-center gap-3 text-muted small">
                <span><i class="bx bx-user me-1"></i><?= htmlspecialchars($post['auteur'] ?? 'Anonymous') ?></span>
                <span><i class="bx bx-calendar me-1"></i><?= date('M d, Y \a\t H:i', strtotime($post['DatePublication'] ?? 'now')) ?></span>
                <span><i class="bx bx-tag me-1"></i><?= htmlspecialchars($post['Categorie'] ?? 'Uncategorized') ?></span>
            </div>
        </div>
        <div class="badge bg-label-info">
            <?= htmlspecialchars($post['Statut'] ?? 'publié') ?>
        </div>
    </div>

    <!-- Post Content -->
    <div class="post-content mb-4">
        <div class="ql-editor" style="border: none; padding: 0;">
            <?= $post['Contenu'] ?? '' ?>
        </div>
    </div>

    <!-- Media -->
    <?php if (!empty($post['media'])): ?>
        <?php
        $mediaPath = '../../uploads/posts/' . $post['media'];
        $ext = strtolower(pathinfo($post['media'], PATHINFO_EXTENSION));
        $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
        ?>
        <div class="post-media mb-4">
            <?php if ($isVideo): ?>
                <video controls class="w-100 rounded" style="max-height: 400px;">
                    <source src="<?= $mediaPath ?>" type="video/<?= $ext === 'mov' ? 'mp4' : $ext ?>">
                    Your browser does not support video.
                </video>
            <?php else: ?>
                <img src="<?= $mediaPath ?>" class="w-100 rounded" style="max-height: 400px; object-fit: cover;" alt="Post media">
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Comments Section -->
    <div class="comments-section">
        <h6 class="mb-3">
            <i class="bx bx-comment-dots me-2"></i>
            Comments (<?= count($comments) ?>)
        </h6>

        <?php if (empty($comments)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bx bx-message-square-x bx-lg mb-2"></i>
                <p>No comments yet.</p>
            </div>
        <?php else: ?>
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item d-flex gap-3 mb-3 p-3 bg-light rounded">
                        <div class="avatar">
                            <span class="avatar-initial rounded-circle bg-label-secondary">C</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="small"><?= htmlspecialchars($comment['auteur'] ?? 'Anonymous') ?></strong>
                                <small class="text-muted">
                                    <i class="bx bx-time-five me-1"></i>
                                    <?= date('M d, H:i', strtotime($comment['DateCom'] ?? 'now')) ?>
                                </small>
                                <?php if (!empty($comment['emotion'])): ?>
                                    <span class="badge bg-label-primary small">
                                        <i class="bx bx-smile me-1"></i>
                                        <?= htmlspecialchars($comment['emotion']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="comment-text">
                                <?= htmlspecialchars($comment['Contenu'] ?? '') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.post-detail .ql-editor {
    font-size: 16px;
    line-height: 1.6;
}
.post-detail .avatar {
    width: 40px;
    height: 40px;
}
.post-detail .comment-item {
    border-left: 3px solid #696cff;
}
</style>
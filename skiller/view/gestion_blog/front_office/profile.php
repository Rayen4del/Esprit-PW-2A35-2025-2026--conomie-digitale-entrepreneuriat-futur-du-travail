<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../model/blog/Post.php';
require_once __DIR__ . '/../../../model/blog/SavedPost.php';

$currentUserId = 1;
$postModel = new Post();
$savedPostModel = new SavedPost();

$myPosts = $postModel->getByUser($currentUserId);
$savedPosts = $savedPostModel->getSavedPosts($currentUserId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil - Skiller</title>
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4"><i class="bx bx-user me-2"></i>Mon profil</h2>
    <h4>Mes publications</h4>
    <?php if (empty($myPosts)): ?>
        <div class="alert alert-info">Vous n'avez encore rien publié.</div>
    <?php else: ?>
        <ul class="list-group mb-4">
            <?php foreach ($myPosts as $post): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($post['Titre']) ?></strong>
                    <span class="text-muted">(<?= date('M d, Y', strtotime($post['DatePublication'])) ?>)</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <h4>Publications sauvegardées</h4>
    <?php if (empty($savedPosts)): ?>
        <div class="alert alert-info">Aucune publication sauvegardée pour le moment.</div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($savedPosts as $post): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($post['Titre']) ?></strong>
                    <span class="text-muted">(<?= date('M d, Y', strtotime($post['saved_at'])) ?>)</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="posts/index.php" class="btn btn-secondary mt-4">Retour au fil</a>
</div>
</body>
</html>

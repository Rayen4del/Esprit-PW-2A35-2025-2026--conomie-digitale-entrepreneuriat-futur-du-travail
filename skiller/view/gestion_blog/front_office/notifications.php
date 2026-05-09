<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../model/blog/Notification.php';

$currentUserId = 1;
$notificationModel = new Notification();
$notifications = $notificationModel->getAll($currentUserId, 100);
$notificationModel->markAllAsRead($currentUserId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Skiller</title>
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4"><i class="bx bx-bell me-2"></i>Notifications</h2>
    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">Aucune notification pour le moment.</div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($notifications as $notif): ?>
                <li class="list-group-item d-flex align-items-center gap-3">
                    <div class="avatar"><span class="avatar-initial rounded-circle bg-label-primary">N</span></div>
                    <div class="flex-grow-1">
                        <div>
                            <strong><?= htmlspecialchars($notif['actor_name']) ?></strong>
                            <?php if ($notif['type'] === 'like'): ?>a aimé votre publication<?php endif; ?>
                            <?php if ($notif['type'] === 'comment'): ?>a commenté votre publication<?php endif; ?>
                            <?php if ($notif['type'] === 'share'): ?>a partagé votre publication<?php endif; ?>
                            <span class="text-muted">on "<?= htmlspecialchars($notif['post_title']) ?>"</span>
                        </div>
                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></small>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="posts/index.php" class="btn btn-secondary mt-4">Retour au fil</a>
</div>
</body>
</html>

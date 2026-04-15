<?php
// Front Office - Social Feed (Clean Version)
// Location: view/gestion_blog/front_office/posts/index.php

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';

$postModel    = new Post();
$commentModel = new Comment();

$currentUserId = 1;

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $postId = (int)$_POST['delete_post'];
    
    // Get post to delete media file
    $post = $postModel->getById($postId);
    if ($post && !empty($post['media'])) {
        $mediaPath = __DIR__ . '/../uploads/posts/' . $post['media'];
        if (file_exists($mediaPath)) {
            unlink($mediaPath);
        }
    }
    
    // Delete the post
    $result = $postModel->delete($postId);
    
    // Redirect back to same page with success parameter
    if ($result) {
        header("Location: index.php?deleted=success");
    } else {
        header("Location: index.php?deleted=error");
    }
    exit;
}

// Handle post like
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like_post') {
    $postId = (int)$_POST['post_id'];
    $userId = (int)$_POST['user_id'];
    
    // Check if user already liked
    $alreadyLiked = $postModel->hasUserLikedPost($userId, $postId);
    
    if ($alreadyLiked) {
        $postModel->unlikePost($userId, $postId);
        $liked = false;
    } else {
        $postModel->likePost($userId, $postId);
        $liked = true;
    }
    
    // Get new like count
    $likeCount = $postModel->getPostLikeCount($postId);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $likeCount]);
    exit;
}

$search = $_GET['search'] ?? '';
$posts  = $postModel->getAll($search);

// Add like info to each post
foreach ($posts as &$post) {
    $post['like_count'] = $postModel->getPostLikeCount($post['ID']);
    $post['user_liked'] = $postModel->hasUserLikedPost($currentUserId, $post['ID']);
}

// Handle new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {
    $titre   = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $media   = null;

    if (!empty($_FILES['media']['name'])) {
        $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','mp4','webm'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('post_') . '.' . $ext;
            $dest = __DIR__ . '/../uploads/posts/' . $filename;
            if (move_uploaded_file($_FILES['media']['tmp_name'], $dest)) {
                $media = $filename;
            }
        }
    }

    if ($titre && $contenu) {
        $postModel->create($titre, $contenu, null, null, $media, 'publié', null);
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feed - Skiller</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/css/demo.css" />

    <style>
        .post-card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 28px; position: relative; }
        .post-media { max-height: 480px; object-fit: cover; border-radius: 12px; width: 100%; }
        .create-post-box { border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .comment-section { background: #f8f9fa; border-radius: 12px; padding: 18px; }
        .avatar { width: 42px; height: 42px; }
        .like-btn { transition: transform 0.2s ease; cursor: pointer; }
        .like-btn:hover { transform: scale(1.05); }
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 9999;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
            font-size: 14px;
        }
        .toast-success { background: #28a745; color: white; }
        .toast-error { background: #dc3545; color: white; }
        .toast-info { background: #17a2b8; color: white; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="#" class="app-brand-link">
                    <span class="app-brand-logo demo"><i class='bx bx-code-alt' style="font-size:2.2rem;color:#696cff"></i></span>
                    <span class="app-brand-text demo menu-text fw-bolder ms-2">Skiller</span>
                </a>
            </div>
            <ul class="menu-inner py-1">
                <li class="menu-item active"><a href="index.php" class="menu-link"><i class="bx bx-home"></i><div>Feed</div></a></li>
                <li class="menu-item"><a href="#" class="menu-link"><i class="bx bx-user"></i><div>My Profile</div></a></li>
                <li class="menu-item"><a href="#" class="menu-link"><i class="bx bx-bell"></i><div>Notifications</div></a></li>
            </ul>
        </aside>

        <div class="layout-page">
            <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached bg-navbar-theme">
                <div class="navbar-nav-right d-flex align-items-center w-100">
                    <div class="input-group" style="max-width: 420px;">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                            <i class="bx bx-plus me-1"></i> Create Post
                        </button>
                        <div class="avatar avatar-online">
                            <span class="avatar-initial rounded-circle bg-label-primary">U</span>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <div class="card create-post-box mb-4">
                        <div class="card-body">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="avatar"><span class="avatar-initial rounded-circle bg-label-primary">U</span></div>
                                <input type="text" class="form-control" placeholder="What skill did you learn today?" 
                                       style="border-radius: 50px; cursor: pointer;" 
                                       data-bs-toggle="modal" data-bs-target="#createPostModal">
                            </div>
                        </div>
                    </div>

                    <?php if (empty($posts)): ?>
                        <div class="text-center py-5">
                            <i class="bx bx-news bx-lg text-muted mb-3"></i>
                            <p class="text-muted">No posts yet. Be the first to share!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                        <div class="card post-card" data-post-id="<?= $post['ID'] ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar"><span class="avatar-initial rounded-circle bg-label-info">U</span></div>
                                        <div>
                                            <strong><?= htmlspecialchars($post['auteur'] ?? 'Anonymous') ?></strong>
                                            <small class="text-muted d-block"><?= date('M d, Y \a\t H:i', strtotime($post['DatePublication'] ?? 'now')) ?></small>
                                        </div>
                                    </div>

                                    <?php if ($post['idUtilisateur'] == $currentUserId): ?>
                                    <div class="dropdown">
                                        <button class="btn p-0" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded bx-sm"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="editPost(<?= $post['ID'] ?>); return false;"><i class="bx bx-edit me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deletePost(<?= $post['ID'] ?>); return false;"><i class="bx bx-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <h5 class="fw-bold"><?= htmlspecialchars($post['Titre'] ?? '') ?></h5>
                                <p class="mb-3"><?= nl2br(htmlspecialchars($post['Contenu'] ?? '')) ?></p>

                                <?php if (!empty($post['media'])): ?>
                                    <?php 
                                    $ext = strtolower(pathinfo($post['media'], PATHINFO_EXTENSION));
                                    if (in_array($ext, ['mp4','webm','ogg'])): ?>
                                        <video controls class="post-media mb-3">
                                            <source src="../uploads/posts/<?= htmlspecialchars($post['media']) ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <img src="../uploads/posts/<?= htmlspecialchars($post['media']) ?>" class="post-media mb-3" alt="">
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between text-muted border-top pt-3">
                                    <div onclick="toggleLike(<?= $post['ID'] ?>)" class="like-btn" data-post-id="<?= $post['ID'] ?>" style="cursor:pointer;">
                                        <i class="bx <?= $post['user_liked'] ? 'bxs-heart' : 'bx-heart' ?>" style="color: <?= $post['user_liked'] ? '#dc3545' : '' ?>"></i> 
                                        <span class="like-count"><?= $post['like_count'] ?></span> Likes
                                    </div>
                                    <div onclick="toggleComments(<?= $post['ID'] ?>)" style="cursor:pointer;">
                                        <i class="bx bx-message-rounded-dots me-1"></i> Comment
                                    </div>
                                    <div><i class="bx bx-share-alt me-1"></i> Share</div>
                                </div>
                            </div>

                            <div id="comments-<?= $post['ID'] ?>" class="comment-section" style="display:none;">
                                <p class="text-muted small mb-2">Comments coming soon...</p>
                                <form action="<?= BASE_URL ?>controller/blog/CommentController.php" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="action" value="frontCreate">
                                    <input type="hidden" name="post_id" value="<?= $post['ID'] ?>">
                                    <input type="hidden" name="id_user" value="<?= $currentUserId ?>">
                                    <input type="text" name="contenu" class="form-control" placeholder="Write a comment..." required>
                                    <button type="submit" class="btn btn-primary">Send</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Post Modal -->
<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_post">
                <div class="modal-body">
                    <input type="text" name="titre" class="form-control mb-3" placeholder="Post title" required>
                    <textarea name="contenu" class="form-control mb-3" rows="5" placeholder="Share what you learned..." required></textarea>
                    <div class="mb-3">
                        <label class="form-label">Add Image or Video</label>
                        <input type="file" name="media" class="form-control" accept="image/*,video/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Post Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- External JS File -->
<script src="js/feed.js"></script>
<!-- Comments JS -->
<script src="../comments/js/comments.js"></script>
</body>
</html>
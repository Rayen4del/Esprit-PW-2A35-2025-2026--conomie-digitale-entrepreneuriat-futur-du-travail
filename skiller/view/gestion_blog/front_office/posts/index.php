<?php
// Front Office - Social Feed
// Location: view/gestion_blog/front_office/posts/index.php

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';
require_once __DIR__ . '/../../../../model/blog/Notification.php';
require_once __DIR__ . '/../../../../model/blog/SavedPost.php';
require_once __DIR__ . '/../../../../model/blog/Share.php';

$postModel        = new Post();
$commentModel     = new Comment();
$notificationModel = new Notification();
$savedPostModel   = new SavedPost();
$shareModel       = new Share();

$currentUserId = 1;
function sendJson(array $data): void {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
// ── DELETE POST (SOFT DELETE + Clean Media) ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_post') {
    $postId = (int)($_POST['post_id'] ?? 0);
    
    if (!$postId) {
        sendJson(['success' => false, 'message' => 'Invalid post ID']);
    }

    // Check ownership
    $post = $postModel->getById($postId);
    if (!$post || $post['idUtilisateur'] != $currentUserId) {
        sendJson(['success' => false, 'message' => 'Unauthorized']);
    }

    // Delete media file if exists
    if (!empty($post['media'])) {
        $mediaPath = __DIR__ . '/../../uploads/posts/' . $post['media'];
        if (file_exists($mediaPath)) {
            unlink($mediaPath);
        }
    }

    // Perform soft delete
    $result = $postModel->softDelete($postId);

    sendJson([
        'success' => (bool)$result,
        'message' => $result ? 'Post deleted successfully' : 'Failed to delete post'
    ]);
}

// ── LIKE TOGGLE ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'like_post') {
    $postId = (int)$_POST['post_id'];
    $userId = (int)$_POST['user_id'];
    $post = $postModel->getById($postId);
    
    if ($postModel->hasUserLikedPost($userId, $postId)) {
        $postModel->unlikePost($userId, $postId);
        $liked = false;
    } else {
        $postModel->likePost($userId, $postId);
        $liked = true;
        
        // Create notification if not the post owner
        if ($post && $post['idUtilisateur'] != $userId) {
            $notificationModel->create(
                $post['idUtilisateur'],
                $userId,
                'like',
                $postId,
                'liked your post'
            );
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $postModel->getPostLikeCount($postId)]);
    exit;
}

// ── CREATE POST ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_post') {
    $titre   = trim($_POST['titre']   ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $status  = trim($_POST['status']  ?? 'publié');
    $scheduled_date = null;
    $media   = null;

    // Handle media upload
    if (!empty($_FILES['media']['name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov'];
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/../../uploads/posts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = uniqid('post_') . '.' . $ext;
            if (move_uploaded_file($_FILES['media']['tmp_name'], $uploadDir . $filename)) {
                $media = $filename;
            }
        }
    }

    // Handle scheduled date
    if ($status === 'planifié') {
        $scheduled_date = trim($_POST['scheduled_date'] ?? '');
        if (empty($scheduled_date)) {
            $status = 'brouillon'; // fallback
        }
    }

    if (empty($titre) || empty($contenu)) {
        header('Location: index.php?msg=error');
        exit;
    }

    $result = $postModel->create(
        $titre, 
        $contenu, 
        null, 
        null, 
        $media, 
        $status, 
        $currentUserId, 
        $scheduled_date
    );

    if ($result) {
        header('Location: index.php?msg=created');
    } else {
        header('Location: index.php?msg=error');
    }
    exit;
}

// ── EDIT POST ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_post') {
    $id      = (int)($_POST['id']     ?? 0);
    $titre   = trim($_POST['titre']   ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $media   = null;

    if (!empty($_FILES['media']['name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov'];
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/../../uploads/posts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = uniqid('post_') . '.' . $ext;
            if (move_uploaded_file($_FILES['media']['tmp_name'], $uploadDir . $filename)) {
                $media = $filename;
            }
        }
    }

    if ($id && $titre && $contenu) {
        if (!$media) {
            $existing = $postModel->getById($id);
            $media    = $existing['media'] ?? null;
        }
        $postModel->update($id, $titre, $contenu, null, null, $media);
        header('Location: index.php?msg=updated');
        exit;
    }
}

// ── SAVE POST ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_post') {
    $postId = (int)$_POST['post_id'];
    $userId = (int)$_POST['user_id'];
    
    if ($savedPostModel->isSaved($userId, $postId)) {
        $savedPostModel->unsave($userId, $postId);
        $saved = false;
    } else {
        $savedPostModel->save($userId, $postId);
        $saved = true;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'saved' => $saved]);
    exit;
}

// ── SHARE POST ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'share_post') {
    $postId = (int)$_POST['post_id'];
    $userId = (int)$_POST['user_id'];
    
    $token = $shareModel->createShare($postId, $userId);
    $fullUrl = $shareModel->getFullShareUrl($token);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'url' => $fullUrl, 'token' => $token]);
    exit;
}

// ── LOAD POSTS ──────────────────────────────────────────────────
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

if ($filter === 'drafts') {
    $posts = $postModel->getByUser($currentUserId);
    $posts = array_filter($posts, fn($p) => ($p['Statut'] ?? '') === 'brouillon');
} else {
    $posts = $postModel->getAll($search, '', 'publié'); // Only show published posts
}

foreach ($posts as &$post) {
    $post['like_count'] = $postModel->getPostLikeCount($post['ID']);
    $post['user_liked'] = $postModel->hasUserLikedPost($currentUserId, $post['ID']);
    $post['user_saved'] = $savedPostModel->isSaved($currentUserId, $post['ID']);
}
unset($post);
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $filter === 'drafts' ? 'Mes brouillons' : 'Fil' ?> - Skiller</title>

    <link rel="stylesheet" href="../../assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />

    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .ql-container { font-size: 14px; }
        .ql-editor { min-height: 200px; }
    </style>

    <style>
        .post-card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 28px; }
        .post-media-img { width: 100%; max-height: 480px; object-fit: cover; border-radius: 12px; margin-bottom: 12px; }
        .post-media-video { width: 100%; max-height: 480px; border-radius: 12px; margin-bottom: 12px; background: #000; }
        .create-post-box { border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .comment-section { background: #f8f9fa; border-radius: 12px; padding: 18px; }
        .avatar { width: 42px; height: 42px; }
        .like-btn { cursor: pointer; transition: transform 0.2s ease; }
        .like-btn:hover { transform: scale(1.05); }
        .char-counter { font-size: .78rem; }
        .media-preview-wrap { position: relative; display: inline-block; margin-top: 8px; }
        .media-preview-wrap img,
        .media-preview-wrap video { max-height: 160px; border-radius: 8px; border: 1px solid #dee2e6; }
        .media-preview-wrap .remove-media {
            position: absolute; top: -8px; right: -8px;
            background: #dc3545; color: #fff; border: none;
            border-radius: 50%; width: 22px; height: 22px;
            font-size: 12px; line-height: 22px; text-align: center;
            cursor: pointer; padding: 0;
        }
    </style>
</head>
<body>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <!-- SIDEBAR -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="#" class="app-brand-link">
                    <span class="app-brand-logo demo"><i class='bx bx-code-alt' style="font-size:2.2rem;color:#696cff"></i></span>
                    <span class="app-brand-text demo menu-text fw-bolder ms-2">Skiller</span>
                </a>
            </div>
            <ul class="menu-inner py-1">
                <li class="menu-item <?= $filter !== 'drafts' ? 'active' : '' ?>"><a href="index.php" class="menu-link"><i class="bx bx-home"></i><div>Fil</div></a></li>
                <li class="menu-item <?= $filter === 'drafts' ? 'active' : '' ?>"><a href="index.php?filter=drafts" class="menu-link"><i class="bx bx-edit"></i><div>Mes brouillons</div></a></li>
                <li class="menu-item"><a href="../profile.php" class="menu-link"><i class="bx bx-user"></i><div>Mon profil</div></a></li>
                <li class="menu-item"><a href="../notifications.php" class="menu-link"><i class="bx bx-bell"></i><div>Notifications</div></a></li>
            </ul>
        </aside>

        <div class="layout-page">

            <!-- NAVBAR -->
            <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached bg-navbar-theme">
                <div class="navbar-nav-right d-flex align-items-center w-100">
                    <div class="input-group" style="max-width:420px">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher des publications..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <span class="fw-semibold text-muted">
                            <i class='bx <?= $filter === 'drafts' ? 'bx-edit' : 'bx-home' ?> me-1'></i>
                            <?= $filter === 'drafts' ? 'Mes brouillons' : 'Fil' ?>
                        </span>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                            <i class="bx bx-plus me-1"></i> Créer une publication
                        </button>
                        <div class="avatar avatar-online">
                            <span class="avatar-initial rounded-circle bg-label-primary">U</span>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- Quick create bar -->
                    <div class="card create-post-box mb-4">
                        <div class="card-body">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="avatar"><span class="avatar-initial rounded-circle bg-label-primary">U</span></div>
                                <input type="text" class="form-control" placeholder="Quelle compétence avez-vous apprise aujourd'hui ?"
                                       style="border-radius:50px;cursor:pointer"
                                       data-bs-toggle="modal" data-bs-target="#createPostModal" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- POSTS FEED -->
                    <div id="posts-feed">
                    <?php if (empty($posts)): ?>
                        <div class="text-center py-5">
                            <i class="bx bx-news bx-lg text-muted mb-3"></i>
                            <p class="text-muted">Aucune publication pour le moment. Soyez le premier à partager !</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                        <?php
                            $postId      = $post['ID'];
                            $postMedia   = $post['media'] ?? $post['Media'] ?? '';
                            $mediaExt    = $postMedia ? strtolower(pathinfo($postMedia, PATHINFO_EXTENSION)) : '';
                            $isVideo     = in_array($mediaExt, ['mp4', 'webm', 'ogg', 'mov']);
                            $isOwner     = $post['idUtilisateur'] == $currentUserId;
                        ?>
                        <div class="card post-card" data-post-id="<?= $postId ?>">
                            <div class="card-body">

                                <!-- Post header -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar"><span class="avatar-initial rounded-circle bg-label-info">U</span></div>
                                        <div>
                                            <strong><?= htmlspecialchars($post['auteur'] ?? 'Anonymous') ?></strong>
                                            <small class="text-muted d-block"><?= date('M d, Y \a\t H:i', strtotime($post['DatePublication'] ?? 'now')) ?></small>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded bx-sm"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if ($isOwner): ?>
                                            <li><a class="dropdown-item" href="#" onclick="editPost(<?= $postId ?>); return false;"><i class="bx bx-edit me-2"></i>Modifier</a></li>
<li><a class="dropdown-item text-danger" href="#" onclick="deletePost(<?= $postId ?>); return false;">
    <i class="bx bx-trash me-2"></i>Supprimer
</a></li>                                            <?php endif; ?>
                                            <li><a class="dropdown-item" href="#" onclick="toggleSave(<?= $postId ?>); return false;">
                                                <i class="bx <?= $post['user_saved'] ? 'bxs-bookmark' : 'bx-bookmark' ?> me-2"></i>
                                                <?= $post['user_saved'] ? 'Retirer' : 'Enregistrer' ?>
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Post content -->
                                <h5 class="fw-bold post-title"><?= htmlspecialchars($post['Titre'] ?? '') ?></h5>
                                <div class="post-content"><?= $post['Contenu'] ?? '' ?></div>

                                <!-- Media -->
                                <?php if (!empty($postMedia)): ?>
                                    <?php if ($isVideo): ?>
                                        <video controls class="post-media-video">
                                            <source src="../../uploads/posts/<?= htmlspecialchars($postMedia) ?>"
                                                    type="video/<?= $mediaExt === 'mov' ? 'mp4' : $mediaExt ?>">
                                            Your browser does not support video.
                                        </video>
                                    <?php else: ?>
                                        <img src="../../uploads/posts/<?= htmlspecialchars($postMedia) ?>"
                                             class="post-media-img" alt="Post image">
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Actions -->
                                <div class="d-flex justify-content-between text-muted border-top pt-3 mt-2">
                                    <div onclick="toggleLike(<?= $postId ?>)" class="like-btn" data-post-id="<?= $postId ?>">
                                        <i class="bx <?= $post['user_liked'] ? 'bxs-heart' : 'bx-heart' ?>"
                                           style="color:<?= $post['user_liked'] ? '#dc3545' : '' ?>"></i>
                                        <span class="like-count"><?= $post['like_count'] ?></span> J'aime
                                    </div>
                                    <div onclick="toggleComments(<?= $postId ?>)" style="cursor:pointer">
                                        <i class="bx bx-message-rounded-dots me-1"></i> Commentaires
                                    </div>
                                    <div onclick="sharePost(<?= $postId ?>)" class="share-btn" style="cursor:pointer">
                                        <i class="bx bx-share-alt me-1"></i> Partager
                                    </div>
                                    <div onclick="toggleSave(<?= $postId ?>)" class="save-btn" data-post-id="<?= $postId ?>" style="cursor:pointer">
                                        <i class="bx <?= $post['user_saved'] ? 'bxs-bookmark' : 'bx-bookmark' ?>"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments section -->
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
                    <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── CREATE POST MODAL ── -->
<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-edit-alt me-2"></i>Créer une nouvelle publication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createPostForm" action="index.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Titre</label>
                        <input type="text" id="createPostTitre" name="titre" class="form-control" placeholder="Titre de la publication" maxlength="50">
                        <div class="d-flex justify-content-end mt-1">
                            <span id="createTitreCounter" class="char-counter text-muted">0/50</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold">Contenu</label>
                            <button type="button" id="speakBtn" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-mic"></i> 
                                <span id="speakBtnText">Commencer à parler</span>
                            </button>
                        </div>
                        <div id="createPostEditor" style="background:white; min-height: 220px;"></div>
                        <input type="hidden" id="createPostContenu" name="contenu">
                        <small id="speechStatus" class="text-muted d-block mt-1"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Statut</label>
                        <select id="postStatus" name="status" class="form-select">
                            <option value="publié">📢 Publier maintenant</option>
                            <option value="brouillon">📝 Enregistrer en brouillon</option>
                            <option value="planifié">⏰ Planifier la publication</option>
                        </select>
                    </div>
                    <div class="mb-3" id="scheduledDateContainer" style="display: none;">
                        <label class="form-label fw-semibold">Date et heure de publication</label>
                        <input type="datetime-local" id="scheduledDate" name="scheduled_date" class="form-control">
                        <small class="text-muted">Quand cette publication doit-elle être publiée ?</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bx bx-image-add me-1"></i> Image ou vidéo
                        </label>
                        <input type="file" id="createMediaInput" name="media" class="form-control" accept="image/*,video/*">
                        <small class="text-muted">JPG, PNG, GIF, WebP, MP4, WebM, MOV</small>
                        <div id="createMediaPreview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-send me-1"></i>Publier maintenant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── EDIT POST MODAL ── -->
<div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la publication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPostForm" action="index.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_post">
                <input type="hidden" name="id" id="editPostId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Titre</label>
                        <input type="text" id="editPostTitre" name="titre" class="form-control" placeholder="Titre de la publication" maxlength="50">
                        <div class="d-flex justify-content-end mt-1">
                            <span id="editTitreCounter" class="char-counter text-muted">0/50</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contenu</label>
                        <div id="editPostEditor" style="background:white;"></div>
                        <input type="hidden" id="editPostContenu" name="contenu">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bx bx-image-add me-1"></i> Remplacer l'image/la vidéo
                            <small class="text-muted fw-normal">(laisser vide pour conserver l'actuelle)</small>
                        </label>
                        <!-- Current media preview -->
                        <div id="editCurrentMedia" class="mb-2"></div>
                        <input type="file" id="editMediaInput" name="media" class="form-control" accept="image/*,video/*">
                        <!-- New file preview -->
                        <div id="editMediaPreview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize Quill editors
    let quillCreateEditor = new Quill('#createPostEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'color': [] }, { 'background': [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    let quillEditEditor = new Quill('#editPostEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'color': [] }, { 'background': [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    // Handle create post form
    document.getElementById('createPostForm').addEventListener('submit', function(e) {
        const content = quillCreateEditor.root.innerHTML;
        document.getElementById('createPostContenu').value = content;
    });

    // Handle status change
    document.getElementById('postStatus').addEventListener('change', function() {
        const status = this.value;
        const scheduledContainer = document.getElementById('scheduledDateContainer');
        const submitBtn = document.querySelector('#createPostForm .btn-primary');
        
        if (status === 'planifié') {
            scheduledContainer.style.display = 'block';
            submitBtn.innerHTML = '<i class="bx bx-calendar me-1"></i>Planifier la publication';
        } else {
            scheduledContainer.style.display = 'none';
            if (status === 'brouillon') {
                submitBtn.innerHTML = '<i class="bx bx-save me-1"></i>Enregistrer le brouillon';
            } else {
                submitBtn.innerHTML = '<i class="bx bx-send me-1"></i>Publier maintenant';
            }
        }
    });

    // Handle edit post form
    document.getElementById('editPostForm').addEventListener('submit', function(e) {
        const content = quillEditEditor.root.innerHTML;
        document.getElementById('editPostContenu').value = content;
    });

    // When edit modal is shown, populate the editor
    document.getElementById('editPostModal').addEventListener('show.bs.modal', function(e) {
        const editPostContenuValue = document.getElementById('editPostContenu').value;
        if (editPostContenuValue) {
            quillEditEditor.root.innerHTML = editPostContenuValue;
        }
    });

    // Reset create editor when modal is hidden
    document.getElementById('createPostModal').addEventListener('hide.bs.modal', function() {
        quillCreateEditor.setContents([]);
    });
    
    // Global variables for comments and uploads
    const COMMENT_CONTROLLER = '../comments/index.php';
    const UPLOADS_URL        = '../../uploads/posts/';
</script>
<script src="js/feed.js"></script>
<script src="../comments/js/emotion.js?v=2"></script>
<script src="../comments/js/comments.js?v=2"></script>

<!-- AJAX Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('searchInput');
    const feed  = document.getElementById('posts-feed');
    if (!input || !feed) return;
    
    let debounce;

    input.addEventListener('input', function() {
        clearTimeout(debounce);
        const q = this.value.trim();
        
        // Show loading state
        feed.style.opacity = '0.6';
        
        debounce = setTimeout(function() {
            fetch('search.php?q=' + encodeURIComponent(q))
                .then(r => r.text())
                .then(html => {
                    feed.innerHTML = html;
                    feed.style.opacity = '1';
                })
                .catch(() => {
                    feed.style.opacity = '1';
                });
        }, 350);
    });
});
</script>
<script src="js/speech-to-text.js"></script>
</body>
</html>
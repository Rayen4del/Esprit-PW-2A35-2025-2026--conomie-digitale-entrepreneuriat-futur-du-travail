<?php
// Front Office - Social Feed
// Location: view/gestion_blog/front_office/posts/index.php

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';

$postModel    = new Post();
$commentModel = new Comment();

$currentUserId = 1;

// ── DELETE POST ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $postId = (int)$_POST['delete_post'];
    $post   = $postModel->getById($postId);
    if ($post && !empty($post['media'])) {
        $mediaPath = __DIR__ . '/../../uploads/posts/' . $post['media'];
        if (file_exists($mediaPath)) unlink($mediaPath);
    }
    $result = $postModel->delete($postId);
    header('Location: index.php?deleted=' . ($result ? 'success' : 'error'));
    exit;
}

// ── LIKE TOGGLE ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'like_post') {
    $postId = (int)$_POST['post_id'];
    $userId = (int)$_POST['user_id'];
    if ($postModel->hasUserLikedPost($userId, $postId)) {
        $postModel->unlikePost($userId, $postId);
        $liked = false;
    } else {
        $postModel->likePost($userId, $postId);
        $liked = true;
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $postModel->getPostLikeCount($postId)]);
    exit;
}

// ── CREATE POST ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_post') {
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

    if ($titre && $contenu) {
        $postModel->create($titre, $contenu, null, null, $media, 'publié', null);
        header('Location: index.php');
        exit;
    }
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

// ── LOAD POSTS ──────────────────────────────────────────────────
$search = $_GET['search'] ?? '';
$posts  = $postModel->getAll($search);

foreach ($posts as &$post) {
    $post['like_count'] = $postModel->getPostLikeCount($post['ID']);
    $post['user_liked'] = $postModel->hasUserLikedPost($currentUserId, $post['ID']);
}
unset($post);
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feed - Skiller</title>

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
<script>
(function() {
    const input    = document.getElementById('searchInput');
    const feed     = document.querySelector('.container-xxl .flex-grow-1'); // posts container
    // We need a dedicated wrapper — add id="posts-feed" to your posts loop wrapper div
    let debounce;

    input.addEventListener('input', function() {
        clearTimeout(debounce);
        const q = this.value.trim();
        debounce = setTimeout(function() {
            fetch('search.php?q=' + encodeURIComponent(q))
                .then(r => r.text())
                .then(html => {
                    document.getElementById('posts-feed').innerHTML = html;
                })
                .catch(() => {});
        }, 350);
    });
})();
</script>
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
                <li class="menu-item active"><a href="index.php" class="menu-link"><i class="bx bx-home"></i><div>Feed</div></a></li>
                <li class="menu-item"><a href="#" class="menu-link"><i class="bx bx-user"></i><div>My Profile</div></a></li>
                <li class="menu-item"><a href="#" class="menu-link"><i class="bx bx-bell"></i><div>Notifications</div></a></li>
            </ul>
        </aside>

        <div class="layout-page">

            <!-- NAVBAR -->
            <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached bg-navbar-theme">
                <div class="navbar-nav-right d-flex align-items-center w-100">
                    <div class="input-group" style="max-width:420px">
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

                    <!-- Quick create bar -->
                    <div class="card create-post-box mb-4">
                        <div class="card-body">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="avatar"><span class="avatar-initial rounded-circle bg-label-primary">U</span></div>
                                <input type="text" class="form-control" placeholder="What skill did you learn today?"
                                       style="border-radius:50px;cursor:pointer"
                                       data-bs-toggle="modal" data-bs-target="#createPostModal" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- POSTS FEED -->
                    <?php if (empty($posts)): ?>
                        <div class="text-center py-5">
                            <i class="bx bx-news bx-lg text-muted mb-3"></i>
                            <p class="text-muted">No posts yet. Be the first to share!</p>
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
                                    <?php if ($isOwner): ?>
                                    <div class="dropdown">
                                        <button class="btn p-0" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded bx-sm"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="editPost(<?= $postId ?>); return false;"><i class="bx bx-edit me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deletePost(<?= $postId ?>); return false;"><i class="bx bx-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
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
                                        <span class="like-count"><?= $post['like_count'] ?></span> Likes
                                    </div>
                                    <div onclick="toggleComments(<?= $postId ?>)" style="cursor:pointer">
                                        <i class="bx bx-message-rounded-dots me-1"></i> Comment
                                    </div>
                                    <div style="cursor:pointer"><i class="bx bx-share-alt me-1"></i> Share</div>
                                </div>
                            </div>

                            <!-- Comments section -->
                            <div id="comments-<?= $postId ?>" class="comment-section" style="display:none">
                                <div id="comments-list-<?= $postId ?>">
                                    <p class="text-muted small text-center">Loading comments...</p>
                                </div>
                                <form onsubmit="submitComment(event, <?= $postId ?>)" class="d-flex gap-2 mt-3">
                                    <input type="text" name="content" class="form-control" placeholder="Write a comment...">
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

<!-- ── CREATE POST MODAL ── -->
<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-edit-alt me-2"></i>Create New Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createPostForm" action="index.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" id="createPostTitre" name="titre" class="form-control" placeholder="Post title" maxlength="50">
                        <div class="d-flex justify-content-end mt-1">
                            <span id="createTitreCounter" class="char-counter text-muted">0/50</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Content</label>
                        <div id="createPostEditor" style="background:white;"></div>
                        <input type="hidden" id="createPostContenu" name="contenu">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bx bx-image-add me-1"></i> Add Image or Video
                            <small class="text-muted fw-normal">(jpg, png, gif, webp, mp4, webm, mov)</small>
                        </label>
                        <input type="file" id="createMediaInput" name="media" class="form-control" accept="image/*,video/*">
                        <!-- Live preview -->
                        <div id="createMediaPreview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-send me-1"></i>Post Now</button>
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
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPostForm" action="index.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_post">
                <input type="hidden" name="id" id="editPostId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" id="editPostTitre" name="titre" class="form-control" placeholder="Post title" maxlength="50">
                        <div class="d-flex justify-content-end mt-1">
                            <span id="editTitreCounter" class="char-counter text-muted">0/50</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Content</label>
                        <div id="editPostEditor" style="background:white;"></div>
                        <input type="hidden" id="editPostContenu" name="contenu">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bx bx-image-add me-1"></i> Replace Image/Video
                            <small class="text-muted fw-normal">(leave empty to keep current)</small>
                        </label>
                        <!-- Current media preview -->
                        <div id="editCurrentMedia" class="mb-2"></div>
                        <input type="file" id="editMediaInput" name="media" class="form-control" accept="image/*,video/*">
                        <!-- New file preview -->
                        <div id="editMediaPreview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save Changes</button>
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
<script src="../comments/js/comments.js"></script>
</body>
</html>
<?php
// Front Office - Comment Handler
// Location: view/gestion_blog/front_office/comments/index.php
ini_set('display_errors', 0);
error_reporting(0);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error['message'] . ' in ' . $error['file'] . ' line ' . $error['line']]);
    }
});

ob_start();

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';

$commentModel  = new Comment();
$postModel     = new Post();
$currentUserId = 1;

// ── CREATE ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_comment') {
    $postId  = (int)($_POST['post_id']  ?? 0);
    $userId  = (int)($_POST['user_id']  ?? 0);
    $content = trim($_POST['content']   ?? '');

    if ($postId && $userId && $content) {
        $result = $commentModel->create($userId, $postId, $content);
        header('Content-Type: application/json');
        if ($result) {
            $comment = $commentModel->getLastComment($postId, $userId);
            echo json_encode(['success' => true, 'message' => 'Comment added successfully', 'comment' => $comment]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
    exit;
}

// ── EDIT ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_comment') {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $userId    = (int)($_POST['user_id']    ?? 0);
    $content   = trim($_POST['content']     ?? '');

    header('Content-Type: application/json');

    if (!$commentId || !$userId || !$content) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Make sure the comment belongs to this user
    $comment = $commentModel->getById($commentId);
    if (!$comment || $comment['IDUtilisateur'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $result = $commentModel->update($commentId, $content);
    echo json_encode([
        'success' => (bool)$result,
        'message' => $result ? 'Comment updated' : 'Update failed',
        'content' => $content
    ]);
    exit;
}

// ── DELETE ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_comment') {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $userId    = (int)($_POST['user_id']    ?? 0);

    $comment = $commentModel->getById($commentId);
    header('Content-Type: application/json');
    if ($comment && $comment['IDUtilisateur'] == $userId) {
        $result = $commentModel->delete($commentId);
        echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Comment deleted' : 'Delete failed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

// ── LIKE ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'like_comment') {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $userId    = (int)($_POST['user_id']    ?? 0);

    if ($commentModel->hasUserLikedComment($userId, $commentId)) {
        $commentModel->unlikeComment($userId, $commentId);
        $liked = false;
    } else {
        $commentModel->likeComment($userId, $commentId);
        $liked = true;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $commentModel->getCommentLikeCount($commentId)]);
    exit;
}

// ── GET COMMENTS ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get_comments') {
    $postId   = (int)($_GET['post_id'] ?? 0);
    $comments = $commentModel->getByPostId($postId);

    foreach ($comments as &$comment) {
        $comment['like_count'] = $commentModel->getCommentLikeCount($comment['ID']);
        $comment['user_liked'] = $commentModel->hasUserLikedComment($currentUserId, $comment['ID']);
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'comments' => $comments]);
    exit;
}

// ── FALLBACK ────────────────────────────────────────────────────
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'No action specified']);
?>
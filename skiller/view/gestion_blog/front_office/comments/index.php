<?php
// Front Office - Comment Handler
ini_set('display_errors', 0);
error_reporting(0);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error['message']]);
    }
});

ob_start();

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';

$commentModel  = new Comment();
$postModel     = new Post();
$currentUserId = (int)($_REQUEST['user_id'] ?? 1); // Fix: use actual user

function sendJson(array $data): void {
    while (ob_get_level()) ob_end_clean(); // Fix: flush buffer before output
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ── CREATE ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_comment') {
    
    $postId   = (int)($_POST['post_id']  ?? 0);
    $userId   = (int)($_POST['user_id']  ?? 0);
    $content  = trim($_POST['content']   ?? '');
    $emotion  = trim($_POST['emotion']   ?? '');
    $status   = trim($_POST['status']    ?? 'publié');
    $scheduledDate = trim($_POST['scheduled_date'] ?? '');

    // === DEBUG LOGS ===
    error_log("CREATE COMMENT - Received emotion: '" . $emotion . "'");
    error_log("CREATE COMMENT - All POST data: " . print_r($_POST, true));

    if (!$postId || !$userId || !$content) {
        sendJson(['success' => false, 'message' => 'Missing required fields']);
    }

    $emotion = $emotion ? strtolower(trim($emotion)) : null;

    try {
        $result = $commentModel->create($userId, $postId, $content, $emotion, $status, $scheduledDate ?: null);
        
        if ($result) {
            $comment = $commentModel->getLastComment($postId, $userId);
            sendJson([
                'success' => true, 
                'message' => 'Commentaire ajouté avec succès', 
                'comment' => $comment,
                'saved_emotion' => $emotion   // debug
            ]);
        } else {
            sendJson(['success' => false, 'message' => 'Échec de l\'ajout du commentaire']);
        }
    } catch (Exception $e) {
        error_log("CREATE COMMENT ERROR: " . $e->getMessage());
        sendJson(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
    }
}

// ── EDIT ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_comment') {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $userId    = (int)($_POST['user_id']    ?? 0);
    $content   = trim($_POST['content']     ?? '');

    if (!$commentId || !$userId || !$content) {
        sendJson(['success' => false, 'message' => 'Champs requis manquants']);
    }

    $comment = $commentModel->getById($commentId);
    if (!$comment || $comment['IDUtilisateur'] != $userId) {
        sendJson(['success' => false, 'message' => 'Non autorisé']);
    }

    $result = $commentModel->update($commentId, $content);
    sendJson([
        'success' => (bool)$result,
        'message' => $result ? 'Commentaire mis à jour' : 'Échec de la mise à jour',
        'content' => $content
    ]);
}

// ── DELETE ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_comment') {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $userId    = (int)($_POST['user_id']    ?? 0);

    $comment = $commentModel->getById($commentId);
    if ($comment && $comment['IDUtilisateur'] == $userId) {
        $result = $commentModel->delete($commentId);
        sendJson(['success' => (bool)$result, 'message' => $result ? 'Commentaire supprimé' : 'Échec de la suppression']);
    }
    sendJson(['success' => false, 'message' => 'Non autorisé']);
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

    sendJson(['success' => true, 'liked' => $liked, 'count' => $commentModel->getCommentLikeCount($commentId)]);
}

// ── GET COMMENTS ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get_comments') {
    $postId   = (int)($_GET['post_id'] ?? 0);

    if (!$postId) {
        sendJson(['success' => false, 'message' => 'post_id manquant']);
    }

    try {
        $comments = $commentModel->getByPostId($postId);
        $userId   = (int)($_GET['user_id'] ?? $currentUserId); // Fix: pass user_id in GET too

        foreach ($comments as &$comment) {
            $comment['like_count'] = $commentModel->getCommentLikeCount($comment['ID']);
            $comment['user_liked'] = $commentModel->hasUserLikedComment($userId, $comment['ID']);
        }

        sendJson(['success' => true, 'comments' => $comments]);
    } catch (Exception $e) {
        sendJson(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
    }
}

// ── FALLBACK ────────────────────────────────────────────────────
sendJson(['success' => false, 'message' => 'Aucune action spécifiée']);
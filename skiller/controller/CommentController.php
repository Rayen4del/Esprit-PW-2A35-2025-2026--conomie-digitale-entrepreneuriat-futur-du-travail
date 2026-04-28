<?php
// controller/Commentcontroller.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/blog/Comment.php';

$commentModel = new Comment();
$currentUserId = 1;

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // Handle create comment
    case 'create_comment':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postId  = (int)$_POST['post_id'];
            $userId  = (int)$_POST['user_id'];
            $content = trim($_POST['content'] ?? '');

            if ($postId && $userId && $content) {
                $result = $commentModel->createComment($postId, $userId, $content);

                header('Content-Type: application/json');
                if ($result) {
                    $comment = $commentModel->getLastComment($postId, $userId);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Comment added successfully',
                        'comment' => $comment
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            }
            exit;
        }
        break;

    // Handle delete comment (front office)
    case 'delete_comment':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commentId = (int)$_POST['comment_id'];
            $userId    = (int)$_POST['user_id'];

            $comment = $commentModel->getById($commentId);
            if ($comment && $comment['IDUtilisateur'] == $userId) {
                $result = $commentModel->delete($commentId);
                header('Content-Type: application/json');
                echo json_encode(['success' => $result, 'message' => $result ? 'Comment deleted' : 'Delete failed']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit;
        }
        break;

    // Handle comment like
    case 'like_comment':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commentId = (int)$_POST['comment_id'];
            $userId    = (int)$_POST['user_id'];

            $alreadyLiked = $commentModel->hasUserLikedComment($userId, $commentId);

            if ($alreadyLiked) {
                $commentModel->unlikeComment($userId, $commentId);
                $liked = false;
            } else {
                $commentModel->likeComment($userId, $commentId);
                $liked = true;
            }

            $likeCount = $commentModel->getCommentLikeCount($commentId);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'liked' => $liked, 'count' => $likeCount]);
            exit;
        }
        break;

    // Handle get comments for a post
    case 'get_comments':
        $postId   = (int)($_REQUEST['post_id'] ?? 0);
        $comments = $commentModel->getByPostId($postId);

        foreach ($comments as &$comment) {
            $comment['like_count'] = $commentModel->getCommentLikeCount($comment['ID']);
            $comment['user_liked'] = $commentModel->hasUserLikedComment($currentUserId, $comment['ID']);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'comments' => $comments]);
        exit;

    // ── ADMIN: update comment status (approved / pending / rejected) ──
    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commentId = (int)$_POST['comment_id'];
            $allowed   = ['approved', 'pending', 'rejected'];
            $status    = $_POST['status'] ?? '';

            if (!$commentId || !in_array($status, $allowed)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
                exit;
            }

            $stmt   = Config::getConnexion()->prepare(
                "UPDATE commentaire SET Statut = ? WHERE ID = ?"
            );
            $result = $stmt->execute([$status, $commentId]);

            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        }
        break;

    // ── ADMIN: hard-delete a comment from the backoffice ──
    case 'backDelete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commentId = (int)$_POST['comment_id'];

            if (!$commentId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
                exit;
            }

            $stmt   = Config::getConnexion()->prepare(
                "DELETE FROM commentaire WHERE ID = ?"
            );
            $result = $stmt->execute([$commentId]);

            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        }
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}
?>
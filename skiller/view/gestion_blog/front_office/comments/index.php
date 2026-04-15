<?php
// Front Office - Comment Handler
// Location: view/gestion_blog/front_office/comments/index.php

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';

$commentModel = new Comment();
$postModel = new Post();
$currentUserId = 1;

// Handle create comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_comment') {
    $postId = (int)$_POST['post_id'];
    $userId = (int)$_POST['user_id'];
    $content = trim($_POST['content'] ?? '');
    
    if ($postId && $userId && $content) {
        $result = $commentModel->create($postId, $userId, $content);
        
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

// Handle delete comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
    $commentId = (int)$_POST['comment_id'];
    $userId = (int)$_POST['user_id'];
    
    $comment = $commentModel->getById($commentId);
    if ($comment && $comment['id_utilisateur'] == $userId) {
        $result = $commentModel->delete($commentId);
        header('Content-Type: application/json');
        echo json_encode(['success' => $result, 'message' => $result ? 'Comment deleted' : 'Delete failed']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

// Handle comment like
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like_comment') {
    $commentId = (int)$_POST['comment_id'];
    $userId = (int)$_POST['user_id'];
    
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

// Handle get comments for a post
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_comments') {
    $postId = (int)$_GET['post_id'];
    $comments = $commentModel->getByPostId($postId);
    
    foreach ($comments as &$comment) {
        $comment['like_count'] = $commentModel->getCommentLikeCount($comment['ID']);
        $comment['user_liked'] = $commentModel->hasUserLikedComment($currentUserId, $comment['ID']);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'comments' => $comments]);
    exit;
}

// If no action specified
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'No action specified']);
?>
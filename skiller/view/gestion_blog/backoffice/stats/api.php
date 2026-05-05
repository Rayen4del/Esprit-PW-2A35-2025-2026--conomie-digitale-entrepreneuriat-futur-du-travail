<?php
// API endpoint for engagement stats
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';

$commentModel = new Comment();
$stats = $commentModel->getPostEngagementStats();

// Calculate summary stats
$totalPosts = count($stats);
$totalComments = array_sum(array_column($stats, 'comment_count'));
$totalLikes = array_sum(array_column($stats, 'total_likes'));
$avgRatio = $totalComments > 0 ? round($totalLikes / $totalComments, 1) : 0;

// Top engaged post
$topPost = !empty($stats) ? $stats[0] : null;

echo json_encode([
    'stats' => $stats,
    'summary' => [
        'totalPosts' => $totalPosts,
        'totalComments' => $totalComments,
        'totalLikes' => $totalLikes,
        'avgRatio' => $avgRatio,
        'topPost' => $topPost
    ],
    'timestamp' => date('Y-m-d H:i:s')
]);
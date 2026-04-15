<?php
// view/gestion_blog/backoffice/comments/index.php
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';

$commentModel = new Comment();
$comments = $commentModel->getAll();

// Stats
$total = count($comments);
$pending = $approved = $rejected = 0;
foreach ($comments as $c) {
    $s = strtolower($c['Statut'] ?? 'pending');
    if ($s === 'pending' || empty($s)) $pending++;
    elseif ($s === 'approved') $approved++;
    else $rejected++;
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Comments Management — Admin | Skiller</title>

    <!-- Correct asset paths from backoffice/comments/ -->
    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/css/demo.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>view/gestion_blog/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <style>
        .stats-strip .card { border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .comment-text { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="/skiller" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <i class='bx bx-code-alt' style="font-size:2rem;color:#696cff"></i>
                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Skiller</span>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Blog</span></li>
                    <li class="menu-item">
                        <a href="../../index.php" class="menu-link">
                            <i class="menu-icon bx bx-news"></i>
                            <div>Posts</div>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="index.php" class="menu-link">
                            <i class="menu-icon bx bx-comment-dots"></i>
                            <div>Comments</div>
                        </a>
                    </li>
                    <li class="menu-header small text-uppercase mt-2"><span class="menu-header-text">Navigation</span></li>
                    <li class="menu-item">
                        <a href="/skiller" class="menu-link">
                            <i class="menu-icon bx bx-home-circle"></i>
                            <div>Dashboard</div>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main Content -->
            <div class="layout-page">
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>
                    <div class="navbar-nav-right d-flex align-items-center">
                        <span class="fw-semibold text-muted">Admin Panel</span>
                    </div>
                </nav>

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <h4 class="fw-bold py-3 mb-4">Comments Management</h4>

                        <!-- Stats Cards -->
                        <div class="row g-3 mb-4 stats-strip">
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-label-primary p-3"><i class="bx bx-message-dots bx-lg"></i></div>
                                            <div class="ms-3">
                                                <h4 class="mb-0"><?= $total ?></h4>
                                                <span class="text-muted">Total Comments</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Repeat similar cards for Pending, Approved, Rejected -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-label-warning p-3"><i class="bx bx-time bx-lg"></i></div>
                                            <div class="ms-3">
                                                <h4 class="mb-0"><?= $pending ?></h4>
                                                <span class="text-muted">Pending</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-label-success p-3"><i class="bx bx-check-circle bx-lg"></i></div>
                                            <div class="ms-3">
                                                <h4 class="mb-0"><?= $approved ?></h4>
                                                <span class="text-muted">Approved</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-label-danger p-3"><i class="bx bx-x-circle bx-lg"></i></div>
                                            <div class="ms-3">
                                                <h4 class="mb-0"><?= $rejected ?></h4>
                                                <span class="text-muted">Rejected</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search + Filter + Table -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                                            <input type="text" id="searchInput" class="form-control" placeholder="Search comments or author...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="statusFilter" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-primary w-100" onclick="filterTable()">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">All Comments</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="commentsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Post Title</th>
                                            <th>Author</th>
                                            <th>Comment</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($comments)): ?>
                                            <tr><td colspan="7" class="text-center py-5">No comments found.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($comments as $i => $c): ?>
                                            <tr>
                                                <td><?= $i+1 ?></td>
                                                <td><?= htmlspecialchars($c['post_titre'] ?? 'Deleted') ?></td>
                                                <td><?= htmlspecialchars($c['auteur'] ?? 'Anonymous') ?></td>
                                                <td class="comment-text"><?= htmlspecialchars($c['Contenu'] ?? '') ?></td>
                                                <td>
                                                    <?php 
                                                    $st = strtolower($c['Statut'] ?? 'pending');
                                                    $cls = ($st === 'approved') ? 'bg-label-success' : (($st === 'pending' || !$st) ? 'bg-label-warning' : 'bg-label-danger');
                                                    ?>
                                                    <span class="badge <?= $cls ?>"><?= ucfirst($st ?: 'Pending') ?></span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($c['DateCom'] ?? 'now')) ?></td>
                                                <td>
                                                    <button onclick="deleteComment(<?= $c['ID'] ?>)" class="btn btn-sm btn-outline-danger">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Files - Correct paths -->
    <script src="../../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../assets/vendor/js/menu.js"></script>
    <script src="../../../assets/js/main.js"></script>

    <script>
    function deleteComment(id) {
        if (confirm('Delete this comment?')) {
            window.location.href = '../../../../controller/blog/CommentController.php?action=backDelete&id=' + id;
        }
    }

    function filterTable() {
        // Simple filter logic (same as before)
        const search = document.getElementById('searchInput').value.toLowerCase();
        const statusF = document.getElementById('statusFilter').value.toLowerCase();
        document.querySelectorAll('#commentsTable tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            const badge = row.querySelector('.badge');
            const st = badge ? badge.textContent.toLowerCase() : '';
            row.style.display = (text.includes(search) && (!statusF || st.includes(statusF))) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
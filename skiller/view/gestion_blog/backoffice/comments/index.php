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

    <link rel="stylesheet" href="../../assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <style>
        .stats-strip .card { border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .comment-text { max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .action-btns { display: flex; gap: 4px; flex-wrap: nowrap; }

        /* Toast notification */
        #toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .toast-msg {
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            color: #fff;
            font-size: 0.875rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }
        .toast-msg.success { background: #71dd37; color: #333; }
        .toast-msg.error   { background: #ff3e1d; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="../../index.php" class="app-brand-link">
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
                    <li class="menu-item">
    <a href="../stats/index.php" class="menu-link">
        <i class="menu-icon bx bx-bar-chart-alt-2"></i>
        <div>Engagement Stats</div>
    </a>
</li>
                    <li class="menu-header small text-uppercase mt-2"><span class="menu-header-text">Navigation</span></li>
                    <li class="menu-item">
                        <a href="../../index.php" class="menu-link">
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
                                                <h4 class="mb-0" id="stat-total"><?= $total ?></h4>
                                                <span class="text-muted">Total Comments</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-label-warning p-3"><i class="bx bx-time bx-lg"></i></div>
                                            <div class="ms-3">
                                                <h4 class="mb-0" id="stat-pending"><?= $pending ?></h4>
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
                                                <h4 class="mb-0" id="stat-approved"><?= $approved ?></h4>
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
                                                <h4 class="mb-0" id="stat-rejected"><?= $rejected ?></h4>
                                                <span class="text-muted">Rejected</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search + Filter -->
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
                                </div>
                            </div>
                        </div>

                        <!-- Comments Table -->
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
                                            <?php foreach ($comments as $i => $c):
                                                $st = strtolower($c['Statut'] ?? 'pending');
                                                if (empty($st)) $st = 'pending';
                                                $cls = ($st === 'approved') ? 'bg-label-success' : ($st === 'pending' ? 'bg-label-warning' : 'bg-label-danger');
                                            ?>
                                            <tr id="row-<?= $c['ID'] ?>">
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($c['post_titre'] ?? 'Deleted') ?></td>
                                                <td><?= htmlspecialchars($c['auteur'] ?? 'Anonymous') ?></td>
                                                <td class="comment-text" title="<?= htmlspecialchars($c['Contenu'] ?? '') ?>">
                                                    <?= htmlspecialchars($c['Contenu'] ?? '') ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $cls ?>" id="badge-<?= $c['ID'] ?>">
                                                        <?= ucfirst($st) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($c['DateCom'] ?? 'now')) ?></td>
                                                <td>
                                                    <div class="action-btns">
                                                        <button onclick="window.commentsManager.updateStatus(<?= $c['ID'] ?>, 'approved')" class="btn btn-sm btn-outline-success" title="Approve" <?= $st === 'approved' ? 'disabled' : '' ?>>
                                                            <i class="bx bx-check"></i>
                                                        </button>
                                                        <button onclick="window.commentsManager.updateStatus(<?= $c['ID'] ?>, 'rejected')" class="btn btn-sm btn-outline-warning" title="Reject" <?= $st === 'rejected' ? 'disabled' : '' ?>>
                                                            <i class="bx bx-x"></i>
                                                        </button>
                                                        <button onclick="window.commentsManager.updateStatus(<?= $c['ID'] ?>, 'pending')" class="btn btn-sm btn-outline-secondary" title="Set Pending" <?= $st === 'pending' ? 'disabled' : '' ?>>
                                                            <i class="bx bx-time"></i>
                                                        </button>
                                                        <button onclick="window.commentsManager.deleteComment(<?= $c['ID'] ?>)" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
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

    <!-- Toast container -->
    <div id="toast-container"></div>

    <!-- JS Files -->
    <script src="../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    
    <!-- Complete override to prevent menu.js errors -->
    <script>
    // Full mock of all required objects before loading menu.js
    window.config = {
        colors: {
            primary: '#696cff',
            secondary: '#8592a3',
            success: '#71dd37',
            info: '#03c3ec',
            warning: '#ffab00',
            danger: '#ff3e1d',
            dark: '#233446',
            black: '#000',
            white: '#fff',
            body: '#f4f5fb',
            headingColor: '#566a7f',
            axisColor: '#a1acb8',
            borderColor: '#eceef1'
        }
    };
    
    window.Helpers = {
        isSmallScreen: function() { return window.innerWidth < 1200; },
        toggleCollapsed: function() { 
            var body = document.querySelector('body');
            if (body) body.classList.toggle('layout-menu-collapsed');
        },
        setAutoUpdate: function() {},
        initPasswordToggle: function() {},
        initSpeechToText: function() {},
        scrollToActive: function() {},
        mainMenu: null
    };
    
    // Override the Menu class completely to prevent errors
    window.Menu = function(element, options) {
        this.element = element;
        this.options = options;
        console.log('Menu initialized (mocked)');
        return this;
    };
    </script>
    
    <script src="../../assets/vendor/js/menu.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <!-- Comments Management - All in one script -->
    <script>
    // Single script with no external dependencies
    (function() {
        // Controller URL - using relative path
        const CONTROLLER_URL = "../../../controller/CommentController.php";
        
        // Create a namespace for our functions
        window.commentsManager = {
            updateStatus: function(id, newStatus) {
                fetch(CONTROLLER_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=update_status&comment_id=' + id + '&status=' + newStatus
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        // Update badge
                        var badge = document.getElementById('badge-' + id);
                        var clsMap = { 
                            approved: 'bg-label-success', 
                            pending: 'bg-label-warning', 
                            rejected: 'bg-label-danger' 
                        };
                        badge.className = 'badge ' + clsMap[newStatus];
                        badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                        
                        // Update buttons
                        var row = document.getElementById('row-' + id);
                        var buttons = row.querySelectorAll('button');
                        buttons.forEach(function(btn) {
                            var onclickAttr = btn.getAttribute('onclick');
                            if (onclickAttr && onclickAttr.indexOf('updateStatus') !== -1) {
                                var match = onclickAttr.match(/updateStatus\(\d+,\s*'(\w+)'\)/);
                                if (match && match[1] === newStatus) {
                                    btn.disabled = true;
                                } else if (match) {
                                    btn.disabled = false;
                                }
                            }
                        });
                        
                        window.commentsManager.recalcStats();
                        window.commentsManager.showToast('Status updated to ' + newStatus, 'success');
                    } else {
                        window.commentsManager.showToast(data.message || 'Update failed', 'error');
                    }
                })
                .catch(function() { window.commentsManager.showToast('Network error', 'error'); });
            },
            
            deleteComment: function(id) {
                if (!confirm('Delete this comment permanently?')) return;
                
                fetch(CONTROLLER_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=backDelete&comment_id=' + id
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        var row = document.getElementById('row-' + id);
                        if (row) row.remove();
                        window.commentsManager.recalcStats();
                        window.commentsManager.showToast('Comment deleted', 'success');
                    } else {
                        window.commentsManager.showToast(data.message || 'Delete failed', 'error');
                    }
                })
                .catch(function() { window.commentsManager.showToast('Network error', 'error'); });
            },
            
            filterTable: function() {
                var searchInput = document.getElementById('searchInput');
                var statusFilter = document.getElementById('statusFilter');
                
                if (!searchInput || !statusFilter) return;
                
                var search = searchInput.value.toLowerCase();
                var statusF = statusFilter.value.toLowerCase();
                
                var rows = document.querySelectorAll('#commentsTable tbody tr[id^="row-"]');
                rows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    var badge = row.querySelector('.badge');
                    var st = badge ? badge.textContent.trim().toLowerCase() : '';
                    var matchesSearch = text.indexOf(search) !== -1;
                    var matchesStatus = !statusF || st === statusF;
                    
                    row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                });
            },
            
            recalcStats: function() {
                var total = 0, pending = 0, approved = 0, rejected = 0;
                
                var rows = document.querySelectorAll('#commentsTable tbody tr[id^="row-"]');
                rows.forEach(function(row) {
                    total++;
                    var badge = row.querySelector('.badge');
                    var st = badge ? badge.textContent.trim().toLowerCase() : 'pending';
                    if (st === 'pending') pending++;
                    else if (st === 'approved') approved++;
                    else if (st === 'rejected') rejected++;
                });
                
                var statTotal = document.getElementById('stat-total');
                var statPending = document.getElementById('stat-pending');
                var statApproved = document.getElementById('stat-approved');
                var statRejected = document.getElementById('stat-rejected');
                
                if (statTotal) statTotal.textContent = total;
                if (statPending) statPending.textContent = pending;
                if (statApproved) statApproved.textContent = approved;
                if (statRejected) statRejected.textContent = rejected;
            },
            
            showToast: function(msg, type) {
                type = type || 'success';
                var container = document.getElementById('toast-container');
                if (!container) return;
                
                var toast = document.createElement('div');
                toast.className = 'toast-msg ' + type;
                toast.textContent = msg;
                container.appendChild(toast);
                
                setTimeout(function() {
                    if (toast.remove) toast.remove();
                }, 3000);
            }
        };
        
        // Initialize event listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            var searchInput = document.getElementById('searchInput');
            var statusFilter = document.getElementById('statusFilter');
            
            if (searchInput) {
                searchInput.addEventListener('input', window.commentsManager.filterTable);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', window.commentsManager.filterTable);
            }
        });
    })();
    </script>
</body>
</html>
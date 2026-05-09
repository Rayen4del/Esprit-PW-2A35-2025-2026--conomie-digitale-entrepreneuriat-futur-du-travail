<?php
// view/gestion_blog/backoffice/stats/index.php
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';

$commentModel = new Comment();
$stats        = $commentModel->getPostEngagementStats();

$totalPosts    = count($stats);
$totalComments = array_sum(array_column($stats, 'comment_count'));
$totalLikes    = array_sum(array_column($stats, 'total_likes'));
$avgRatio      = $totalComments > 0 ? round($totalLikes / $totalComments, 1) : 0;

// Top engaged post
$topPost = !empty($stats) ? $stats[0] : null;
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Statistiques d'engagement — Admin | Skiller</title>

    <link rel="stylesheet" href="../../assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <style>
        .stats-strip .card { border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .ratio-bar-wrap { background: #eceef1; border-radius: 20px; height: 8px; min-width: 80px; overflow: hidden; }
        .ratio-bar-fill { height: 100%; border-radius: 20px; transition: width 0.6s ease; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .post-title-cell { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .engagement-badge { font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
        .chart-card { border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
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
                        <div>Publications</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../comments/index.php" class="menu-link">
                        <i class="menu-icon bx bx-comment-dots"></i>
                        <div>Commentaires</div>
                    </a>
                </li>
                <li class="menu-item active">
                    <a href="index.php" class="menu-link">
                        <i class="menu-icon bx bx-bar-chart-alt-2"></i>
                        <div>Statistiques d'engagement</div>
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
                    <span class="fw-semibold text-muted">Panneau d'administration</span>
                </div>
            </nav>

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <h4 class="fw-bold py-3 mb-4">
                        <i class="bx bx-bar-chart-alt-2 me-2" style="color:#696cff"></i>Statistiques d'engagement des publications
                        <small class="text-muted fw-normal ms-2" style="font-size:12px;">
                            <i class="bx bx-time-five"></i> Dernière mise à jour : <span id="lastUpdated">-</span>
                        </small>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="fetchStats()" id="refreshBtn">
                            <i class="bx bx-refresh"></i> Actualiser
                        </button>
                    </h4>

                    <!-- Summary Cards -->
                    <div class="row g-3 mb-4 stats-strip">
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-label-primary p-3"><i class="bx bx-news bx-lg"></i></div>
                                        <div class="ms-3">
                                            <h4 class="mb-0" id="totalPosts"><?= $totalPosts ?></h4>
                                            <span class="text-muted">Publications totales</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-label-info p-3"><i class="bx bx-comment-dots bx-lg"></i></div>
                                        <div class="ms-3">
                                            <h4 class="mb-0" id="totalComments"><?= $totalComments ?></h4>
                                            <span class="text-muted">Commentaires totaux</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-label-danger p-3"><i class="bx bxs-heart bx-lg"></i></div>
                                        <div class="ms-3">
                                            <h4 class="mb-0" id="totalLikes"><?= $totalLikes ?></h4>
                                            <span class="text-muted">Likes totaux</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-label-success p-3"><i class="bx bx-trending-up bx-lg"></i></div>
                                        <div class="ms-3">
                                            <h4 class="mb-0" id="avgRatio"><?= $avgRatio ?>x</h4>
                                            <span class="text-muted">Ratio moyen Like/Commentaire</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-4 mb-4">
                        <!-- Bar chart: likes vs comments -->
                        <div class="col-xl-7">
                            <div class="card chart-card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">Likes vs Commentaires — top 8 publications</h5>
                                    <div class="d-flex gap-3" style="font-size:12px;">
                                        <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#696cff;margin-right:4px;"></span>Likes</span>
                                        <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#03c3ec;margin-right:4px;"></span>Comments</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div style="position:relative;height:280px;">
                                        <canvas id="barChart" role="img" aria-label="Grouped bar chart of likes and comments per post"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Horizontal ratio chart -->
                        <div class="col-xl-5">
                            <div class="card chart-card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Ratio Like / Commentaire — top 8</h5>
                                </div>
                                <div class="card-body">
                                    <div style="position:relative;height:280px;">
                                        <canvas id="ratioChart" role="img" aria-label="Horizontal bar chart of like to comment ratio per post"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full Table -->
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">All posts — engagement breakdown</h5>
                            <input type="text" id="tableSearch" class="form-control form-control-sm w-auto"
                                   placeholder="Rechercher des publications..." style="min-width:200px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="statsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Titre du post</th>
                                        <th class="text-center"><i class="bx bx-comment-dots"></i> Commentaires</th>
                                        <th class="text-center"><i class="bx bxs-heart text-danger"></i> Likes</th>
                                        <th>Barre de ratio</th>
                                        <th class="text-center">Engagement</th>
                                    </tr>
                                </thead>
                                <tbody id="statsBody" data-stats="<?= htmlspecialchars(json_encode($stats)) ?>">
                                    <?php
                                    $maxLikes = !empty($stats) ? max(array_column($stats, 'total_likes')) : 1;
                                    $maxLikes = max($maxLikes, 1);
                                    foreach ($stats as $i => $s):
                                        $ratio   = $s['comment_count'] > 0
                                                    ? round($s['total_likes'] / $s['comment_count'], 1)
                                                    : (int)$s['total_likes'];
                                        $pct     = round(($s['total_likes'] / $maxLikes) * 100);
                                        $level   = $ratio >= 3 ? 'high' : ($ratio >= 1 ? 'med' : 'low');
                                        $badgeCls = $level === 'high' ? 'bg-label-success'
                                                  : ($level === 'med'  ? 'bg-label-warning' : 'bg-label-secondary');
                                        $badgeTxt = $level === 'high' ? 'High' : ($level === 'med' ? 'Medium' : 'Low');
                                        $barColor = $level === 'high' ? '#71dd37'
                                                  : ($level === 'med'  ? '#ffab00' : '#a1acb8');
                                    ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td class="post-title-cell" title="<?= htmlspecialchars($s['Titre']) ?>">
                                            <?= htmlspecialchars($s['Titre']) ?>
                                        </td>
                                        <td class="text-center fw-semibold"><?= $s['comment_count'] ?></td>
                                        <td class="text-center fw-semibold"><?= $s['total_likes'] ?></td>
                                        <td style="min-width:130px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <small class="text-muted" style="min-width:32px;"><?= $ratio ?>x</small>
                                                <div class="ratio-bar-wrap flex-grow-1">
                                                    <div class="ratio-bar-fill" style="width:<?= $pct ?>%;background:<?= $barColor ?>;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge engagement-badge <?= $badgeCls ?>"><?= $badgeTxt ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($stats)): ?>
                                        <tr><td colspan="6" class="text-center py-5 text-muted">Aucune donnée de publication trouvée.</td></tr>
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

<!-- JS -->
<script src="../../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../../assets/vendor/libs/popper/popper.js"></script>
<script src="../../assets/vendor/js/bootstrap.js"></script>
<script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script>
window.config = {
    colors: { primary:'#696cff',secondary:'#8592a3',success:'#71dd37',info:'#03c3ec',
              warning:'#ffab00',danger:'#ff3e1d',dark:'#233446',black:'#000',white:'#fff',
              body:'#f4f5fb',headingColor:'#566a7f',axisColor:'#a1acb8',borderColor:'#eceef1' }
};
window.Helpers = {
    isSmallScreen: function(){ return window.innerWidth < 1200; },
    toggleCollapsed: function(){ document.querySelector('body')?.classList.toggle('layout-menu-collapsed'); },
    setAutoUpdate:function(){}, initPasswordToggle:function(){},
    initSpeechToText:function(){}, scrollToActive:function(){}, mainMenu:null
};
window.Menu = function(el, opts){ this.element=el; this.options=opts; return this; };
</script>
<script src="../../assets/vendor/js/menu.js"></script>
<script src="../../assets/js/main.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
let barChart = null;
let ratioChart = null;
const REFRESH_INTERVAL = 10000; // 10 seconds

function renderSummary(data) {
    document.getElementById('totalPosts').textContent = data.totalPosts;
    document.getElementById('totalComments').textContent = data.totalComments;
    document.getElementById('totalLikes').textContent = data.totalLikes;
    document.getElementById('avgRatio').textContent = data.avgRatio + 'x';
    document.getElementById('lastUpdated').textContent = data.timestamp || new Date().toLocaleTimeString();
}

function renderTable(stats) {
    const tbody = document.getElementById('statsBody');
    const maxLikes = stats.length ? Math.max(...stats.map(s => s.total_likes), 1) : 1;
    
    if (!stats.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No post data found.</td></tr>';
        return;
    }
    
    tbody.innerHTML = stats.map((s, i) => {
        const ratio = s.comment_count > 0 ? +(s.total_likes / s.comment_count).toFixed(1) : +s.total_likes;
        const pct = Math.round((s.total_likes / maxLikes) * 100);
        const level = ratio >= 3 ? 'high' : ratio >= 1 ? 'med' : 'low';
        const badgeCls = level === 'high' ? 'bg-label-success' : level === 'med' ? 'bg-label-warning' : 'bg-label-secondary';
        const badgeTxt = level === 'high' ? 'High' : level === 'med' ? 'Medium' : 'Low';
        const barColor = level === 'high' ? '#71dd37' : level === 'med' ? '#ffab00' : '#a1acb8';
        
        return `<tr>
            <td>${i + 1}</td>
            <td class="post-title-cell" title="${s.Titre}">${s.Titre}</td>
            <td class="text-center fw-semibold">${s.comment_count}</td>
            <td class="text-center fw-semibold">${s.total_likes}</td>
            <td style="min-width:130px;">
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted" style="min-width:32px;">${ratio}x</small>
                    <div class="ratio-bar-wrap flex-grow-1">
                        <div class="ratio-bar-fill" style="width:${pct}%;background:${barColor};"></div>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <span class="badge engagement-badge ${badgeCls}">${badgeTxt}</span>
            </td>
        </tr>`;
    }).join('');
}

function renderCharts(stats) {
    const shorten = t => t.length > 20 ? t.slice(0,18) + '…' : t;
    
    const top8bar = [...stats].sort((a,b) => b.total_likes - a.total_likes).slice(0,8);
    const top8ratio = [...stats]
        .map(s => ({...s, ratio: s.comment_count > 0 ? +(s.total_likes/s.comment_count).toFixed(1) : +s.total_likes}))
        .sort((a,b) => b.ratio - a.ratio)
        .slice(0,8);

    // Destroy existing charts
    if (barChart) barChart.destroy();
    if (ratioChart) ratioChart.destroy();

    // Bar chart
    barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: top8bar.map(s => shorten(s.Titre)),
            datasets: [
                { label:'Likes', data: top8bar.map(s => s.total_likes), backgroundColor:'#696cff', borderRadius:4 },
                { label:'Commentaires', data: top8bar.map(s => s.comment_count), backgroundColor:'#03c3ec', borderRadius:4 }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{
                x:{ ticks:{ font:{size:11}, autoSkip:false, maxRotation:30 }, grid:{ display:false } },
                y:{ ticks:{ font:{size:11} }, grid:{ color:'#eceef1' } }
            }
        }
    });

    // Ratio chart
    ratioChart = new Chart(document.getElementById('ratioChart'), {
        type:'bar',
        data:{
            labels: top8ratio.map(s => shorten(s.Titre)),
            datasets:[{
                label:'Likes par commentaire',
                data: top8ratio.map(s => s.ratio),
                backgroundColor: top8ratio.map(s => s.ratio >= 3 ? '#71dd37' : s.ratio >= 1 ? '#ffab00' : '#a1acb8'),
                borderRadius:4
            }]
        },
        options:{
            indexAxis:'y',
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{
                x:{ ticks:{ font:{size:11} }, grid:{ color:'#eceef1' } },
                y:{ ticks:{ font:{size:11} }, grid:{ display:false } }
            }
        }
    });
}

async function fetchStats() {
    const btn = document.getElementById('refreshBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Loading...';
    
    try {
        const response = await fetch('api.php');
        const data = await response.json();
        
        renderSummary(data.summary);
        renderTable(data.stats);
        renderCharts(data.stats);
    } catch (err) {
        console.error('Failed to fetch stats:', err);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-refresh"></i> Refresh';
    }
}

// Initial load
fetchStats();

// Auto-refresh every 30 seconds
setInterval(fetchStats, REFRESH_INTERVAL);

// Table search (works with dynamic content)
document.getElementById('tableSearch').addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#statsBody tr').forEach(function(row){
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>
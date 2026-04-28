<?php
// skiller/view/gestion_blog/index.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/blog/Post.php';
require_once __DIR__ . '/../../model/blog/Comment.php';

$postModel = new Post();
$commentModel = new Comment();

// Handle search & filter from GET params
$search   = $_GET['search']   ?? '';
$category = $_GET['category'] ?? '';
$status   = $_GET['status']   ?? '';

$posts      = $postModel->getAll($search, $category, $status);
$categories = $postModel->getCategories();

// Flash message
$flash = null;
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $flash = ['type' => 'success', 'msg' => 'Post deleted successfully!'];
    if ($_GET['msg'] === 'error') $flash = ['type' => 'danger', 'msg' => 'An error occurred!'];
    if ($_GET['msg'] === 'status_updated') $flash = ['type' => 'success', 'msg' => 'Status updated successfully!'];
}

function getCommentCountForPost($commentModel, $postId) {
    if (!$postId) return 0;
    try {
        return $commentModel->countByPost($postId);
    } catch (Exception $e) {
        return 0;
    }
}

// Define base URL dynamically
$scriptName = $_SERVER['SCRIPT_NAME'];
$baseUrl = rtrim(dirname(dirname(dirname($scriptName))), '/'); // Go up 3 levels from view/gestion_blog/index.php
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Blog Posts — Admin | Skiller</title>

  <link rel="stylesheet" href="./assets/vendor/fonts/boxicons.css" />
  <link rel="stylesheet" href="./assets/vendor/css/core.css" />
  <link rel="stylesheet" href="./assets/vendor/css/theme-default.css" />
  <link rel="stylesheet" href="./assets/css/demo.css" />
  <link rel="stylesheet" href="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <style>
    .post-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: 8px; }
    .post-thumb-placeholder { width: 52px; height: 52px; border-radius: 8px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #bbb; font-size: 1.4rem; }
    .table-hover tbody tr:hover { background: #f8f9fa; }
    .action-btns .btn { padding: 4px 10px; font-size: 0.8rem; }
    .filter-bar { background: #fff; border-radius: 12px; padding: 16px 20px; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 24px; }
    .stats-strip .card { border-radius: 12px; border: none; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
  </style>
</head>
<!-- PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
function exportPostsPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape' });

    // Header bar
    doc.setFillColor(105, 108, 255);
    doc.rect(0, 0, 297, 22, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Skiller — Blog Posts Export', 14, 14);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.text('Generated: ' + new Date().toLocaleString(), 220, 14);

    // Collect rows from the table already rendered on the page
    const rows = [];
    document.querySelectorAll('table tbody tr').forEach(function(tr) {
        const tds = tr.querySelectorAll('td');
        if (tds.length < 7) return; // skip empty/colspanned rows

        const status  = tds[4].querySelector('select')
                        ? tds[4].querySelector('select option:checked').text.replace(/^.{1,3}\s/, '').trim()
                        : tds[4].textContent.trim();
        const comments = tds[5].textContent.trim();

        rows.push([
            tds[0].textContent.trim(),                          // #
            tds[1].querySelector('.fw-semibold')
                  ?.textContent.trim() || tds[1].textContent.trim().slice(0,40), // title
            tds[2].textContent.trim(),                          // author
            tds[3].textContent.trim(),                          // category
            status,                                             // status
            comments,                                           // comments
            tds[6].textContent.trim()                           // date
        ]);
    });

    doc.autoTable({
        startY: 28,
        head: [['#', 'Title', 'Author', 'Category', 'Status', 'Comments', 'Date']],
        body: rows,
        styles: { fontSize: 9, cellPadding: 4 },
        headStyles: { fillColor: [105, 108, 255], textColor: 255, fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [245, 245, 255] },
        columnStyles: {
            0: { cellWidth: 10 },
            1: { cellWidth: 70 },
            2: { cellWidth: 35 },
            3: { cellWidth: 30 },
            4: { cellWidth: 28 },
            5: { cellWidth: 25, halign: 'center' },
            6: { cellWidth: 28 }
        },
        didDrawPage: function(data) {
            // Footer on each page
            doc.setFontSize(8);
            doc.setTextColor(150);
            doc.text(
                'Page ' + doc.internal.getCurrentPageInfo().pageNumber,
                data.settings.margin.left,
                doc.internal.pageSize.height - 6
            );
        }
    });

    doc.save('skiller-posts-' + new Date().toISOString().slice(0,10) + '.pdf');
}
</script>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="<?= $baseUrl ?>" class="app-brand-link">
            <span class="app-brand-logo demo"><i class='bx bx-code-alt' style="font-size:2rem;color:#696cff"></i></span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Skiller</span>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
          <li class="menu-header small text-uppercase"><span class="menu-header-text">Blog</span></li>
          <li class="menu-item active"><a href="index.php" class="menu-link"><i class="menu-icon bx bx-news"></i><div>Posts</div></a></li>
          <li class="menu-item"><a href="backoffice/comments/index.php" class="menu-link"><i class="menu-icon bx bx-comment-dots"></i><div>Comments</div></a></li>
          <li class="menu-item">
    <a href="backoffice/stats/index.php" class="menu-link">
        <i class="menu-icon bx bx-bar-chart-alt-2"></i>
        <div>Engagement Stats</div>
    </a>
</li>
          <li class="menu-header small text-uppercase mt-2"><span class="menu-header-text">Navigation</span></li>
          <li class="menu-item"><a href="<?= $baseUrl ?>" class="menu-link"><i class="menu-icon bx bx-home-circle"></i><div>Dashboard</div></a></li>
        </ul>
      </aside>

      <div class="layout-page">
        <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)"><i class="bx bx-menu bx-sm"></i></a>
          </div>
          <div class="navbar-nav-right d-flex align-items-center">
            <span class="fw-semibold text-muted"><i class='bx bx-shield-quarter me-1' style="color:#696cff"></i> Admin Panel</span>
            <div class="ms-auto">
              <a href="../front_office/posts/index.php" class="btn btn-sm btn-primary"><i class="bx bx-globe me-1"></i> Switch to Front Office</a>
            </div>
          </div>
        </nav>

        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <div><h4 class="fw-bold mb-1">Blog Posts</h4><p class="text-muted mb-0">Moderate, archive, or delete posts from here.</p></div>
            </div>

            <?php if ($flash): ?>
              <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4"><?= htmlspecialchars($flash['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row stats-strip mb-4 g-3">
              <?php $total = count($posts); $published = count(array_filter($posts, fn($p) => ($p['statut'] ?? $p['Statut'] ?? '') === 'publié')); $drafts = count(array_filter($posts, fn($p) => ($p['statut'] ?? $p['Statut'] ?? '') === 'brouillon')); $archived = count(array_filter($posts, fn($p) => ($p['statut'] ?? $p['Statut'] ?? '') === 'archivé')); ?>
              <div class="col-6 col-md-3"><div class="card p-3"><div class="d-flex align-items-center gap-3"><div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class='bx bx-news'></i></span></div><div><div class="fw-bold fs-5"><?= $total ?></div><div class="text-muted">Total Posts</div></div></div></div></div>
              <div class="col-6 col-md-3"><div class="card p-3"><div class="d-flex align-items-center gap-3"><div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class='bx bx-check-circle'></i></span></div><div><div class="fw-bold fs-5"><?= $published ?></div><div class="text-muted">Published</div></div></div></div></div>
              <div class="col-6 col-md-3"><div class="card p-3"><div class="d-flex align-items-center gap-3"><div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class='bx bx-time-five'></i></span></div><div><div class="fw-bold fs-5"><?= $drafts ?></div><div class="text-muted">Drafts</div></div></div></div></div>
              <div class="col-6 col-md-3"><div class="card p-3"><div class="d-flex align-items-center gap-3"><div class="avatar"><span class="avatar-initial rounded bg-label-secondary"><i class='bx bx-archive'></i></span></div><div><div class="fw-bold fs-5"><?= $archived ?></div><div class="text-muted">Archived</div></div></div></div></div>
            </div>

            <div class="filter-bar">
              <form method="GET" action="" class="row g-2 align-items-end">
                <div class="col-12 col-md-4"><label class="form-label mb-1 small fw-semibold">Search</label><div class="input-group input-group-sm"><span class="input-group-text"><i class='bx bx-search'></i></span><input type="text" name="search" class="form-control" placeholder="Title or content…" value="<?= htmlspecialchars($search) ?>"></div></div>
                <div class="col-6 col-md-3"><label class="form-label mb-1 small fw-semibold">Category</label><select name="category" class="form-select form-select-sm"><option value="">All categories</option><?php foreach ($categories as $cat): ?><option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option><?php endforeach; ?></select></div>
                <div class="col-6 col-md-2"><label class="form-label mb-1 small fw-semibold">Status</label><select name="status" class="form-select form-select-sm"><option value="">All statuses</option><option value="publié" <?= $status === 'publié' ? 'selected' : '' ?>>Published</option><option value="brouillon" <?= $status === 'brouillon' ? 'selected' : '' ?>>Draft</option><option value="archivé" <?= $status === 'archivé' ? 'selected' : '' ?>>Archived</option></select></div>
                <div class="col-12 col-md-3 d-flex gap-2"><button type="submit" class="btn btn-primary btn-sm w-100"><i class='bx bx-filter-alt me-1'></i> Filter</button><?php if ($search || $category || $status): ?><a href="index.php" class="btn btn-outline-secondary btn-sm w-100"><i class='bx bx-x me-1'></i> Clear</a><?php endif; ?></div>
              </form>
            </div>

            <div class="card">
<div class="card-header d-flex align-items-center justify-content-between py-3">
    <h6 class="mb-0">All Posts</h6>
    <div class="d-flex align-items-center gap-2">
        <small class="text-muted"><?= count($posts) ?> result(s)</small>
        <button onclick="exportPostsPDF()" class="btn btn-sm btn-outline-danger">
            <i class='bx bxs-file-pdf me-1'></i> Export PDF
        </button>
    </div>
</div>              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light"><tr><th>#</th><th>Post</th><th>Author</th><th>Category</th><th>Status</th><th>Comments</th><th>Date</th><th class="text-center">Actions</th></tr></thead>
                  <tbody>
                    <?php if (empty($posts)): ?>
                      <tr><td colspan="8" class="text-center py-5 text-muted">No posts found.</td></tr>
                    <?php else: ?>
                      <?php foreach ($posts as $i => $post): 
                        $postId = $post['ID'] ?? $post['id'] ?? 0;
                        $postTitle = $post['titre'] ?? $post['Titre'] ?? 'Untitled';
                        $postContent = $post['contenu'] ?? $post['Contenu'] ?? '';
                        $postAuthor = $post['auteur'] ?? $post['Auteur'] ?? 'Unknown';
                        $postCategory = $post['categorie'] ?? $post['Categorie'] ?? '';
                        $postStatus = $post['statut'] ?? $post['Statut'] ?? 'brouillon';
                        $postMedia = $post['media'] ?? $post['Media'] ?? '';
                        $postDate = $post['date_creation'] ?? $post['DateCreation'] ?? $post['DatePublication'] ?? 'now';
                        $commentCount = getCommentCountForPost($commentModel, $postId);
                      ?>
                      <tr>
                        <td class="text-muted small"><?= $i + 1 ?></td>
                        <td><div class="d-flex align-items-center gap-3"><?php if (!empty($postMedia)): ?><img src="./uploads/posts/<?= htmlspecialchars($postMedia) ?>" class="post-thumb"><?php else: ?><div class="post-thumb-placeholder"><i class='bx bx-image-alt'></i></div><?php endif; ?><div><div class="fw-semibold"><?= htmlspecialchars($postTitle) ?></div><small class="text-muted"><?= htmlspecialchars(substr(strip_tags($postContent), 0, 60)) ?>…</small></div></div></td>
                        <td><span class="fw-semibold"><?= htmlspecialchars($postAuthor) ?></span></td>
                        <td><?php if (!empty($postCategory)): ?><span class="badge bg-label-info"><?= htmlspecialchars($postCategory) ?></span><?php else: ?><span class="text-muted small">—</span><?php endif; ?></td>
                        <td>
                          <form method="POST" action="<?= $baseUrl ?>/controller/PostController.php" class="d-inline">
                            <input type="hidden" name="action" value="backChangeStatus">
                            <input type="hidden" name="id" value="<?= $postId ?>">
                            <select name="statut" class="form-select form-select-sm" style="width:120px;font-size:.78rem" onchange="this.form.submit()">
                              <option value="publié" <?= $postStatus === 'publié' ? 'selected' : '' ?>>✅ Published</option>
                              <option value="brouillon" <?= $postStatus === 'brouillon' ? 'selected' : '' ?>>🕐 Draft</option>
                              <option value="archivé" <?= $postStatus === 'archivé' ? 'selected' : '' ?>>📦 Archived</option>
                            </select>
                          </form>
                        </td>
                        <td><a href="backoffice/posts/view.php?id=<?= $postId ?>" class="badge bg-label-secondary text-decoration-none"><i class='bx bx-comment me-1'></i> <?= $commentCount ?></a></td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($postDate)) ?></td>
                        <td class="text-center">
                          <div class="d-flex justify-content-center gap-1 action-btns">
                            <a href="backoffice/posts/view.php?id=<?= $postId ?>" class="btn btn-sm btn-icon btn-outline-secondary"><i class='bx bx-show'></i></a>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $postId ?>" data-title="<?= htmlspecialchars($postTitle) ?>"><i class='bx bx-trash'></i></button>
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

  <!-- DELETE MODAL -->
  <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title text-danger"><i class='bx bx-error-circle me-2'></i>Delete Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to permanently delete <strong id="deletePostTitle"></strong>? This will also remove all its comments and media.</p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="<?= $baseUrl ?>/controller/PostController.php" class="d-inline">
            <input type="hidden" name="action" value="backDelete">
            <input type="hidden" name="id" id="deletePostId">
            <button type="submit" class="btn btn-danger btn-sm"><i class='bx bx-trash me-1'></i> Yes, delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="./assets/vendor/libs/jquery/jquery.js"></script>
  <script src="./assets/vendor/libs/popper/popper.js"></script>
  <script src="./assets/vendor/js/bootstrap.js"></script>
  <script src="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  
  <script>
    window.config = { colors: { primary: '#696cff' } };
    window.Helpers = { isSmallScreen: function() { return window.innerWidth < 1200; }, toggleCollapsed: function() {}, setAutoUpdate: function() {}, initPasswordToggle: function() {}, initSpeechToText: function() {}, scrollToActive: function() {}, mainMenu: null };
    window.Menu = function() { return this; };
  </script>
  
  <script src="./assets/vendor/js/menu.js"></script>
  <script src="./assets/js/main.js"></script>

  <script>
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function(e) {
      const btn = e.relatedTarget;
      document.getElementById('deletePostId').value = btn.getAttribute('data-id');
      document.getElementById('deletePostTitle').textContent = '"' + btn.getAttribute('data-title') + '"';
    });
  </script>
</body>
</html>
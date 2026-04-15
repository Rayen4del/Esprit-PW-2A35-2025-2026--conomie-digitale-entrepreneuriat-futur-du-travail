<?php
// skiller/view/gestion_blog/index.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/blog/Post.php';
require_once __DIR__ . '/../../model/blog/Comment.php';

// Auth check disabled — user module not implemented yet

$postModel = new Post();
$commentModel = new Comment();

// Handle search & filter from GET params
$search   = $_GET['search']   ?? '';
$category = $_GET['category'] ?? '';
$status   = $_GET['status']   ?? '';

$posts      = $postModel->getAll($search, $category, $status);
$categories = $postModel->getCategories();

// Flash message (will work once sessions are added with user module)
$flash = null;

// Helper function to get comment count safely
function getCommentCountForPost($commentModel, $postId) {
    if (!$postId) return 0;
    try {
        return $commentModel->countByPost($postId);
    } catch (Exception $e) {
        return 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Blog Posts — Admin | Skiller</title>

  <!-- Sneat assets -->
  <link rel="stylesheet" href="./assets/vendor/fonts/boxicons.css" />
  <link rel="stylesheet" href="./assets/vendor/css/core.css" />
  <link rel="stylesheet" href="./assets/vendor/css/theme-default.css" />
  <link rel="stylesheet" href="./assets/css/demo.css" />
  <link rel="stylesheet" href="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <style>
    .post-thumb {
      width: 52px;
      height: 52px;
      object-fit: cover;
      border-radius: 8px;
    }
    .post-thumb-placeholder {
      width: 52px;
      height: 52px;
      border-radius: 8px;
      background: #f0f0f0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #bbb;
      font-size: 1.4rem;
    }
    .status-badge { font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    .badge-publie   { background: #e6f9f0; color: #28a745; }
    .badge-brouillon{ background: #fff8e1; color: #f59e0b; }
    .badge-archive  { background: #f0f0f0; color: #888; }
    .table-hover tbody tr:hover { background: #f8f9fa; }
    .action-btns .btn { padding: 4px 10px; font-size: 0.8rem; }
    .filter-bar { background: #fff; border-radius: 12px; padding: 16px 20px; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 24px; }
    .stats-strip .card { border-radius: 12px; border: none; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
  </style>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

      <!-- ===== SIDEBAR ===== -->
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="/skiller" class="app-brand-link">
            <span class="app-brand-logo demo">
              <i class='bx bx-code-alt' style="font-size:2rem;color:#696cff"></i>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Skiller</span>
          </a>
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-auto ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">
          <li class="menu-header small text-uppercase"><span class="menu-header-text">Blog</span></li>

          <li class="menu-item active">
            <a href="index.php" class="menu-link">
              <i class="menu-icon bx bx-news"></i>
              <div>Posts</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="backoffice/comments/index.php" class="menu-link">
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
      <!-- / Sidebar -->

      <!-- ===== MAIN CONTENT ===== -->
      <div class="layout-page">

        <!-- Navbar -->
        <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="bx bx-menu bx-sm"></i>
            </a>
          </div>
          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <div class="navbar-nav align-items-center">
              <span class="fw-semibold text-muted" style="font-size:.9rem">
                <i class='bx bx-shield-quarter me-1' style="color:#696cff"></i>
                Admin Panel
              </span>
            </div>

            <!-- Switch to Front Office Button -->
            <div class="ms-auto">
              <a href="../front_office/posts/index.php" class="btn btn-sm btn-primary">
                <i class="bx bx-globe me-1"></i> Switch to Front Office
              </a>
            </div>

            <ul class="navbar-nav flex-row align-items-center ms-3">
              <li class="nav-item dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <span class="avatar-initial rounded-circle bg-label-primary">A</span>
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-grow-1">
                          <span class="fw-semibold d-block">Admin</span>
                          <small class="text-muted">Administrator</small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li><div class="dropdown-divider"></div></li>
                  <li><a class="dropdown-item text-danger" href="/skiller/controller/logout.php"><i class='bx bx-power-off me-2'></i>Logout</a></li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
        <!-- / Navbar -->

        <!-- Page content -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Page title -->
            <div class="d-flex align-items-center justify-content-between mb-4">
              <div>
                <h4 class="fw-bold mb-1">Blog Posts</h4>
                <p class="text-muted mb-0">Moderate, archive, or delete posts from here.</p>
              </div>
            </div>

            <!-- Flash message -->
            <?php if ($flash): ?>
              <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4" role="alert">
                <i class='bx bx-<?= $flash['type'] === 'success' ? 'check-circle' : 'error-circle' ?> me-2'></i>
                <?= htmlspecialchars($flash['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <!-- Stats strip -->
            <div class="row stats-strip mb-4 g-3">
              <?php
                $total     = count($posts);
                // Handle both uppercase and lowercase status keys
                $published = count(array_filter($posts, fn($p) => ($p['statut'] ?? $p['Statut'] ?? '') === 'publié'));
                $drafts    = count(array_filter($posts, fn($p) => ($p['statut'] ?? $p['Statut'] ?? '') === 'brouillon'));
                $archived  = count(array_filter($posts, fn($p) => ($p['statut'] ?? $p['Statut'] ?? '') === 'archivé'));
                $stats = [
                  ['label'=>'Total Posts',  'val'=>$total,     'icon'=>'bx-news',          'color'=>'primary'],
                  ['label'=>'Published',    'val'=>$published, 'icon'=>'bx-check-circle',  'color'=>'success'],
                  ['label'=>'Drafts',       'val'=>$drafts,    'icon'=>'bx-time-five',     'color'=>'warning'],
                  ['label'=>'Archived',     'val'=>$archived,  'icon'=>'bx-archive',       'color'=>'secondary'],
                ];
              ?>
              <?php foreach ($stats as $s): ?>
              <div class="col-6 col-md-3">
                <div class="card p-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-<?= $s['color'] ?>">
                        <i class="bx <?= $s['icon'] ?>"></i>
                      </span>
                    </div>
                    <div>
                      <div class="fw-bold fs-5"><?= $s['val'] ?></div>
                      <div class="text-muted" style="font-size:.8rem"><?= $s['label'] ?></div>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- Filter bar -->
            <div class="filter-bar">
              <form method="GET" action="" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                  <label class="form-label mb-1 small fw-semibold">Search</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class='bx bx-search'></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Title or content…" value="<?= htmlspecialchars($search) ?>">
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <label class="form-label mb-1 small fw-semibold">Category</label>
                  <select name="category" class="form-select form-select-sm">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-6 col-md-2">
                  <label class="form-label mb-1 small fw-semibold">Status</label>
                  <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    <option value="publié"    <?= $status === 'publié'    ? 'selected' : '' ?>>Published</option>
                    <option value="brouillon" <?= $status === 'brouillon' ? 'selected' : '' ?>>Draft</option>
                    <option value="archivé"   <?= $status === 'archivé'   ? 'selected' : '' ?>>Archived</option>
                  </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                  <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class='bx bx-filter-alt me-1'></i> Filter
                  </button>
                  <?php if ($search || $category || $status): ?>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm w-100">
                      <i class='bx bx-x me-1'></i> Clear
                    </a>
                  <?php endif; ?>
                </div>
              </form>
            </div>

            <!-- Posts table -->
            <div class="card">
              <div class="card-header d-flex align-items-center justify-content-between py-3">
                <h6 class="mb-0">
                  All Posts
                  <?php if ($search || $category || $status): ?>
                    <span class="badge bg-label-primary ms-2" style="font-size:.75rem">Filtered</span>
                  <?php endif; ?>
                </h6>
                <small class="text-muted"><?= count($posts) ?> result(s)</small>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th style="width:52px">#</th>
                      <th>Post</th>
                      <th>Author</th>
                      <th>Category</th>
                      <th>Status</th>
                      <th>Comments</th>
                      <th>Date</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($posts)): ?>
                      <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                          <i class='bx bx-ghost' style="font-size:2.5rem;display:block;margin-bottom:8px;opacity:.3"></i>
                          No posts found.
                         </td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($posts as $i => $post): 
                        // Get post ID safely (handle both ID and id)
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

                        <!-- Thumb + title -->
                        <td>
                          <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($postMedia)): ?>
                              <img src="./uploads/posts/<?= htmlspecialchars($postMedia) ?>"
                                   class="post-thumb" alt="thumb">
                            <?php else: ?>
                              <div class="post-thumb-placeholder">
                                <i class='bx bx-image-alt'></i>
                              </div>
                            <?php endif; ?>
                            <div>
                              <div class="fw-semibold" style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                <?= htmlspecialchars($postTitle) ?>
                              </div>
                              <small class="text-muted" style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block">
                                <?= htmlspecialchars(substr(strip_tags($postContent), 0, 60)) ?>…
                              </small>
                            </div>
                          </div>
                         </td>

                        <td>
                          <span class="fw-semibold"><?= htmlspecialchars($postAuthor) ?></span>
                         </td>

                        <td>
                          <?php if (!empty($postCategory)): ?>
                            <span class="badge bg-label-info"><?= htmlspecialchars($postCategory) ?></span>
                          <?php else: ?>
                            <span class="text-muted small">—</span>
                          <?php endif; ?>
                         </td>

                        <!-- Status badge + inline change -->
                        <td>
                          <form method="POST" action="../../controller/blog/PostController.php" class="d-inline">
                            <input type="hidden" name="action" value="backChangeStatus">
                            <input type="hidden" name="id" value="<?= $postId ?>">
                            <select name="statut" class="form-select form-select-sm status-select"
                                    style="width:120px;font-size:.78rem"
                                    onchange="this.form.submit()">
                              <option value="publié"    <?= $postStatus === 'publié'    ? 'selected' : '' ?>>✅ Published</option>
                              <option value="brouillon" <?= $postStatus === 'brouillon' ? 'selected' : '' ?>>🕐 Draft</option>
                              <option value="archivé"   <?= $postStatus === 'archivé'   ? 'selected' : '' ?>>📦 Archived</option>
                            </select>
                          </form>
                         </td>

                        <td>
                          <a href="backoffice/posts/view.php?id=<?= $postId ?>" class="badge bg-label-secondary text-decoration-none">
                            <i class='bx bx-comment me-1'></i>
                            <?= $commentCount ?>
                          </a>
                         </td>

                        <td class="text-muted small">
                          <?= date('d M Y', strtotime($postDate)) ?>
                         </td>

                        <!-- Actions -->
                        <td class="text-center">
                          <div class="d-flex justify-content-center gap-1 action-btns">
                            <!-- View -->
                            <a href="backoffice/posts/view.php?id=<?= $postId ?>"
                               class="btn btn-sm btn-icon btn-outline-secondary"
                               title="View post & comments">
                              <i class='bx bx-show'></i>
                            </a>
                            <!-- Delete -->
                            <button type="button"
                                    class="btn btn-sm btn-icon btn-outline-danger"
                                    title="Delete post"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="<?= $postId ?>"
                                    data-title="<?= htmlspecialchars($postTitle) ?>">
                              <i class='bx bx-trash'></i>
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
            <!-- / Posts table -->

          </div>
          <!-- / container -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- / Page content -->
      </div>
      <!-- / Main content -->

    </div>
    <!-- / Layout container -->
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
  <!-- / Layout wrapper -->

  <!-- ===== DELETE CONFIRM MODAL ===== -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title text-danger"><i class='bx bx-error-circle me-2'></i>Delete Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-0">
            Are you sure you want to permanently delete
            <strong id="deletePostTitle"></strong>?
            This will also remove all its comments and media.
          </p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="../../controller/blog/PostController.php" class="d-inline">
            <input type="hidden" name="action" value="backDelete">
            <input type="hidden" name="id" id="deletePostId">
            <button type="submit" class="btn btn-danger btn-sm">
              <i class='bx bx-trash me-1'></i> Yes, delete
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Sneat JS -->
  <script src="./assets/vendor/libs/jquery/jquery.js"></script>
  <script src="./assets/vendor/libs/popper/popper.js"></script>
  <script src="./assets/vendor/js/bootstrap.js"></script>
  <script src="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="./assets/vendor/js/menu.js"></script>
  <script src="./assets/js/main.js"></script>

  <script>
    // Feed post id/title into delete modal
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function (e) {
      const btn = e.relatedTarget;
      document.getElementById('deletePostId').value    = btn.dataset.id;
      document.getElementById('deletePostTitle').textContent = '"' + btn.dataset.title + '"';
    });
  </script>

</body>
</html>
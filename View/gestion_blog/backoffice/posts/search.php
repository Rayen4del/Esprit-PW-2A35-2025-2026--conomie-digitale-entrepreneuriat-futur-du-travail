<?php
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../model/blog/Post.php';
require_once __DIR__ . '/../../../../model/blog/Comment.php';

$postModel    = new Post();
$commentModel = new Comment();

$search   = trim($_GET['q']        ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status']   ?? '');

$posts = $postModel->getAll($search, $category, $status);

if (empty($posts)) {
    echo '<tr><td colspan="8" class="text-center py-5 text-muted">No posts found.</td></tr>';
    exit;
}

foreach ($posts as $i => $post):
    $postId       = $post['ID']            ?? 0;
    $postTitle    = $post['titre']         ?? $post['Titre']         ?? 'Untitled';
    $postContent  = $post['contenu']       ?? $post['Contenu']       ?? '';
    $postAuthor   = $post['auteur']        ?? $post['Auteur']        ?? 'Unknown';
    $postCategory = $post['categorie']     ?? $post['Categorie']     ?? '';
    $postStatus   = $post['statut']        ?? $post['Statut']        ?? 'brouillon';
    $postMedia    = $post['media']         ?? $post['Media']         ?? '';
    $postDate     = $post['date_creation'] ?? $post['DateCreation']  ?? $post['DatePublication'] ?? 'now';
    $commentCount = $commentModel->countByPost($postId);

    $statusCls = match(strtolower($postStatus)) {
        'publié'    => 'bg-label-success',
        'brouillon' => 'bg-label-warning',
        'archivé'   => 'bg-label-secondary',
        default     => 'bg-label-secondary'
    };

    // Build baseUrl dynamically
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseUrl    = rtrim(dirname(dirname(dirname(dirname($scriptName)))), '/');
?>
<tr>
    <td class="text-muted small"><?= $i + 1 ?></td>
    <td>
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($postMedia)): ?>
                <img src="../../uploads/posts/<?= htmlspecialchars($postMedia) ?>"
                     style="width:52px;height:52px;object-fit:cover;border-radius:8px;">
            <?php else: ?>
                <div style="width:52px;height:52px;border-radius:8px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#bbb;font-size:1.4rem;">
                    <i class='bx bx-image-alt'></i>
                </div>
            <?php endif; ?>
            <div>
                <div class="fw-semibold"><?= htmlspecialchars($postTitle) ?></div>
                <small class="text-muted"><?= htmlspecialchars(substr(strip_tags($postContent), 0, 60)) ?>…</small>
            </div>
        </div>
    </td>
    <td><span class="fw-semibold"><?= htmlspecialchars($postAuthor) ?></span></td>
    <td><?= !empty($postCategory) ? '<span class="badge bg-label-info">' . htmlspecialchars($postCategory) . '</span>' : '<span class="text-muted small">—</span>' ?></td>
    <td>
        <form method="POST" action="<?= $baseUrl ?>/controller/PostController.php" class="d-inline">
            <input type="hidden" name="action" value="backChangeStatus">
            <input type="hidden" name="id" value="<?= $postId ?>">
            <select name="statut" class="form-select form-select-sm" style="width:120px;font-size:.78rem" onchange="this.form.submit()">
                <option value="publié"    <?= strtolower($postStatus) === 'publié'    ? 'selected' : '' ?>>✅ Published</option>
                <option value="brouillon" <?= strtolower($postStatus) === 'brouillon' ? 'selected' : '' ?>>🕐 Draft</option>
                <option value="archivé"   <?= strtolower($postStatus) === 'archivé'   ? 'selected' : '' ?>>📦 Archived</option>
            </select>
        </form>
    </td>
    <td>
        <a href="backoffice/posts/view.php?id=<?= $postId ?>" class="badge bg-label-secondary text-decoration-none">
            <i class='bx bx-comment me-1'></i> <?= $commentCount ?>
        </a>
    </td>
    <td class="text-muted small"><?= date('d M Y', strtotime($postDate)) ?></td>
    <td class="text-center">
        <div class="d-flex justify-content-center gap-1">
            <a href="backoffice/posts/view.php?id=<?= $postId ?>" class="btn btn-sm btn-icon btn-outline-secondary"><i class='bx bx-show'></i></a>
            <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                    data-id="<?= $postId ?>" data-title="<?= htmlspecialchars($postTitle) ?>">
                <i class='bx bx-trash'></i>
            </button>
        </div>
    </td>
</tr>
<?php endforeach; ?>
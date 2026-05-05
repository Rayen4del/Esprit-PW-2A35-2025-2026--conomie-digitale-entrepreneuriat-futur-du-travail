<?php
// View/FrontOffice/favorites.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/FavoritesController.php';
requireLogin();

$assetPath = '../assets/';
$favCtrl = new FavoritesController();
$userId = $_SESSION['user']['id'] ?? null;

if (!$userId) {
    header('Location: login.php');
    exit;
}

// Handle remove from favorites (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $opportunityId = (int)($_POST['opportunity_id'] ?? 0);
    $result = $favCtrl->removeFavorite($userId, $opportunityId);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}

// Get user's favorite opportunities
$favoritesResult = $favCtrl->getUserFavorites($userId);
$favorites = $favoritesResult ? $favoritesResult->fetchAll(PDO::FETCH_ASSOC) : [];

// Handle AJAX search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json');
    $search = trim($_POST['search'] ?? '');
    
    $results = [];
    foreach ($favorites as $fav) {
        if ($search === '' || 
            stripos($fav['Titre'] ?? '', $search) !== false || 
            stripos($fav['Localisation'] ?? '', $search) !== false ||
            stripos($fav['Type_job'] ?? '', $search) !== false) {
            $results[] = $fav;
        }
    }
    
    echo json_encode($results);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Favorites — Skiller</title>
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/css/core.css">
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/css/theme-default.css">
  <link rel="stylesheet" href="<?= $assetPath ?>css/demo.css">
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/fonts/boxicons.css">
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      My Favorite Opportunities
      <small><span id="favCount"><?= count($favorites) ?></span> saved</small>
    </div>
    <a href="opportunities.php" class="sk-btn sk-btn-secondary">
      <i class="bx bx-arrow-back"></i> Back to All
    </a>
  </div>

  <!-- Search Section -->
  <div class="sk-card" style="margin-bottom: 24px;">
    <div style="padding: 16px;">
      <label class="sk-label" style="font-size: 0.8rem;">Search in Favorites</label>
      <input type="text" id="searchInput" class="sk-input" placeholder="Search by title, type, or location...">
    </div>
  </div>

  <!-- Favorites Table/List -->
  <div class="sk-card">
    <?php if (empty($favorites)): ?>
      <div class="sk-empty">
        <i class="bx bx-heart" style="font-size: 3rem; color: var(--sk-muted); margin-bottom: 16px;"></i>
        <p>No favorite opportunities yet.</p>
        <p style="font-size: 0.875rem; color: var(--sk-muted);">Click the heart icon on opportunities to add them here.</p>
        <a href="opportunities.php" class="sk-btn sk-btn-primary" style="margin-top: 16px;">Browse Opportunities</a>
      </div>
    <?php else: ?>
      <table class="sk-table" id="favoritesTable">
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Location</th>
            <th>Status</th>
            <th>Saved</th>
            <th style="text-align: center; width: 100px;">Action</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($favorites as $fav):
            $sc = $fav['Statut'] === 'actif' ? 'actif' : ($fav['Statut'] === 'archivé' ? 'archive' : 'expire');
          ?>
            <tr>
              <td style="font-weight:600"><?= htmlspecialchars($fav['Titre']) ?></td>
              <td><span class="sk-badge sk-badge-<?= htmlspecialchars($fav['Type_job']) ?>"><?= htmlspecialchars($fav['Type_job']) ?></span></td>
              <td style="color:var(--sk-muted)"><?= htmlspecialchars($fav['Localisation'] ?? '—') ?></td>
              <td><span class="sk-badge sk-badge-<?= $sc ?>"><?= htmlspecialchars($fav['Statut']) ?></span></td>
              <td style="color:var(--sk-muted); font-size: 0.875rem;"><?= htmlspecialchars(date('M d, Y', strtotime($fav['dateFav'] ?? ''))) ?></td>
              <td style="text-align: center;">
                <button class="sk-btn sk-btn-sm sk-btn-danger" onclick="removeFavorite(<?= $fav['ID'] ?>, this)">
                  <i class="bx bx-trash"></i> Remove
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div id="emptyState" class="sk-empty" style="display: none;">
        <p>No favorites match your search.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
const allFavorites = <?php echo json_encode($favorites, JSON_UNESCAPED_UNICODE); ?>;
let filteredFavorites = [...allFavorites];

const searchInput = document.getElementById('searchInput');
const tableBody = document.getElementById('tableBody');
const emptyState = document.getElementById('emptyState');
const favCount = document.getElementById('favCount');

function performSearch() {
  const search = searchInput.value.trim();
  
  fetch('<?= $_SERVER['PHP_SELF'] ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax=1&search=' + encodeURIComponent(search)
  })
  .then(r => r.json())
  .then(data => {
    filteredFavorites = data;
    updateTable();
  })
  .catch(e => console.error('Error:', e));
}

function updateTable() {
  if (filteredFavorites.length === 0) {
    tableBody.innerHTML = '';
    emptyState.style.display = 'block';
  } else {
    emptyState.style.display = 'none';
    tableBody.innerHTML = '';
    
    filteredFavorites.forEach(fav => {
      const sc = fav.Statut === 'actif' ? 'actif' : (fav.Statut === 'archivé' ? 'archive' : 'expire');
      const date = new Date(fav.dateFav);
      const dateStr = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
      
      const row = document.createElement('tr');
      row.innerHTML = `
        <td style="font-weight:600">${escapeHtml(fav.Titre)}</td>
        <td><span class="sk-badge sk-badge-${escapeHtml(fav.Type_job)}">${escapeHtml(fav.Type_job)}</span></td>
        <td style="color:var(--sk-muted)">${escapeHtml(fav.Localisation ?? '—')}</td>
        <td><span class="sk-badge sk-badge-${sc}">${escapeHtml(fav.Statut)}</span></td>
        <td style="color:var(--sk-muted); font-size: 0.875rem;">${dateStr}</td>
        <td style="text-align: center;">
          <button class="sk-btn sk-btn-sm sk-btn-danger" onclick="removeFavorite(${fav.ID}, this)">
            <i class="bx bx-trash"></i> Remove
          </button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  }
  
  favCount.textContent = filteredFavorites.length;
}

function removeFavorite(favId, btn) {
  if (!confirm('Remove from favorites?')) return;
  
  fetch('<?= $_SERVER['PHP_SELF'] ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'remove_favorite=1&opportunity_id=' + favId
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      // Remove from local array
      filteredFavorites = filteredFavorites.filter(f => f.ID !== favId);
      allFavorites = allFavorites.filter(f => f.ID !== favId);
      updateTable();
    } else {
      alert('Error removing favorite');
    }
  })
  .catch(e => console.error('Error:', e));
}

function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

if (searchInput) {
  searchInput.addEventListener('input', performSearch);
}
</script>

<style>
.sk-badge-jobs { background: rgba(91,108,255,.15); color: #7c8fff; border: 1px solid rgba(91,108,255,.25); }
.sk-badge-freelance { background: rgba(245,166,35,.15); color: #f5a623; border: 1px solid rgba(245,166,35,.25); }
.sk-badge-stage { background: rgba(34,211,165,.15); color: #22d3a5; border: 1px solid rgba(34,211,165,.25); }
.sk-badge-actif { background: rgba(34,211,165,.15); color: #22d3a5; border: 1px solid rgba(34,211,165,.25); }
.sk-badge-archive { background: rgba(122,128,153,.15); color: var(--sk-muted); border: 1px solid rgba(122,128,153,.25); }
.sk-badge-expire { background: rgba(255,77,109,.15); color: #ff4d6d; border: 1px solid rgba(255,77,109,.25); }
</style>
</body>
</html>

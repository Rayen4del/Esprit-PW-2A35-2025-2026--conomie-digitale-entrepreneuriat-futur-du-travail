<?php
// View/FrontOffice/opportunities.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/OportunityController.php';
require_once __DIR__ . '/../../Controller/FavoritesController.php';
requireLogin();

$assetPath = '../assets/';
$controller = new OportunityController();
$favCtrl = new FavoritesController();
$userId = $_SESSION['user']['id'] ?? null;
$opportunitiesList = ($r = $controller->listOportunities()) ? $r->fetchAll(PDO::FETCH_ASSOC) : [];

// Handle AJAX favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_action'])) {
    if (!$userId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    $opportunityId = (int)($_POST['opportunity_id'] ?? 0);
    $favCtrl->toggleFavorite($userId, $opportunityId);
}

// Handle AJAX search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json');
    $search = trim($_POST['search'] ?? '');
    $typeFilter = trim($_POST['type'] ?? '');
    $statusFilter = trim($_POST['status'] ?? '');
    
    $results = [];
    foreach ($opportunitiesList as $opp) {
        $matches = true;
        
        // Search in title, description, location, and published date
        if ($search !== '') {
            $searchLower = strtolower($search);
            $titleMatch = stripos($opp['Titre'] ?? '', $search) !== false;
            $descMatch = stripos($opp['Description'] ?? '', $search) !== false;
            $locationMatch = stripos($opp['Localisation'] ?? '', $search) !== false;
            $dateMatch = stripos($opp['datePublication'] ?? '', $search) !== false;
            if (!$titleMatch && !$descMatch && !$locationMatch && !$dateMatch) {
                $matches = false;
            }
        }
        
        // Filter by type
        if ($typeFilter !== '') {
            if ($opp['Type_job'] !== $typeFilter) {
                $matches = false;
            }
        }
        
        // Filter by status
        if ($statusFilter !== '') {
            if ($opp['Statut'] !== $statusFilter) {
                $matches = false;
            }
        }
        
        if ($matches) {
            $results[] = $opp;
        }
    }
    
    echo json_encode($results);
    exit;
}

// Get unique types and statuses
$types = [];
$statuses = [];
foreach ($opportunitiesList as $opp) {
    if (!in_array($opp['Type_job'], $types)) {
        $types[] = $opp['Type_job'];
    }
    if (!in_array($opp['Statut'], $statuses)) {
        $statuses[] = $opp['Statut'];
    }
}
sort($types);
sort($statuses);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Opportunities — Skiller</title>
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/css/core.css">
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/css/theme-default.css">
  <link rel="stylesheet" href="<?= $assetPath ?>css/demo.css">
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/fonts/boxicons.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      Browse Opportunities
      <small><span id="oppCount"><?= count($opportunitiesList) ?></span> listing<?= count($opportunitiesList) !== 1 ? 's' : '' ?> available</small>
    </div>
    <div style="display: flex; gap: 12px;">
      <a href="ai-search.php" class="sk-btn sk-btn-secondary">
        <i class="bx bx-brain"></i> AI Search
      </a>
      <a href="favorites.php" class="sk-btn sk-btn-secondary">
        <i class="bx bxs-heart"></i> My Favorites
      </a>
      <button class="sk-btn sk-btn-primary" onclick="exportPDF()">
        <i class="bx bx-download"></i> Export PDF
      </button>
    </div>
  </div>

  <!-- Filters Section -->
  <div class="sk-card" style="margin-bottom: 24px;">
    <div style="padding: 20px;">
      <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 16px;">
        <!-- Search Bar -->
        <div>
          <label class="sk-label">Search</label>
          <input type="text" id="searchInput" class="sk-input" placeholder="Search by title or description...">
        </div>
        
        <!-- Type Filter -->
        <div>
          <label class="sk-label">Type</label>
          <select id="typeFilter" class="sk-select">
            <option value="">All Types</option>
            <?php foreach ($types as $type): ?>
              <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <!-- Status Filter -->
        <div>
          <label class="sk-label">Status</label>
          <select id="statusFilter" class="sk-select">
            <option value="">All Status</option>
            <?php foreach ($statuses as $status): ?>
              <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Reset Button -->
        <div style="display: flex; align-items: flex-end;">
          <button type="button" class="sk-btn sk-btn-ghost" onclick="resetFilters()" style="width: 100%;">
            <i class="bx bx-refresh"></i> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Opportunities Table -->
  <div class="sk-card">
    <table class="sk-table" id="opportunitiesTable">
      <thead>
        <tr>
          <th>Title</th><th>Type</th><th>Location</th><th>Published</th><th>Status</th><th style="text-align: center; width: 180px;">Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php foreach ($opportunitiesList as $row):
          $sc = $row['Statut'] === 'actif' ? 'actif' : ($row['Statut'] === 'archivé' ? 'archive' : 'expire');
          $isFav = $userId ? $favCtrl->isFavorited($userId, $row['ID']) : false;
        ?>
          <tr>
            <td style="font-weight:600"><?= htmlspecialchars($row['Titre']) ?></td>
            <td><span class="sk-badge sk-badge-<?= htmlspecialchars($row['Type_job']) ?>"><?= htmlspecialchars($row['Type_job']) ?></span></td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($row['Localisation'] ?? '—') ?></td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($row['datePublication'] ?? '—') ?></td>
            <td><span class="sk-badge sk-badge-<?= $sc ?>"><?= htmlspecialchars($row['Statut']) ?></span></td>
            <td style="text-align: center;">
              <button class="sk-btn sk-btn-sm sk-btn-ghost"
                      onclick="viewOpportunityDescription(<?= htmlspecialchars(json_encode($row['Titre'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($row['Description'] ?? ''), ENT_QUOTES) ?>)"
                      title="View description">
                <i class="bx bx-show"></i> Description
              </button>
              <button class="sk-btn sk-btn-sm fav-btn <?= $isFav ? 'favorited' : '' ?>" 
                      onclick="toggleFavorite(<?= $row['ID'] ?>, this)"
                      title="<?= $isFav ? 'Remove from favorites' : 'Add to favorites' ?>"
                      style="background: none; border: none; padding: 4px 8px; cursor: pointer; font-size: 1.2em;">
                <i class="bx <?= $isFav ? 'bxs-heart' : 'bx-heart' ?>" style="color: <?= $isFav ? '#ff4d6d' : '#ccc' ?>; display: inline-block;"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div id="emptyState" class="sk-empty" style="display: none;">
      <p>No opportunities match your filters.</p>
    </div>
  </div>
</div>

<div class="sk-modal-overlay" id="descriptionModalOverlay" role="dialog" aria-modal="true">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <div class="sk-modal-title" id="descriptionTitle">Opportunity Description</div>
      <button type="button" class="sk-modal-close" onclick="closeDescriptionModal()" aria-label="Close">&times;</button>
    </div>
    <div class="sk-modal-body">
      <p id="descriptionText" style="margin:0;color:var(--sk-text);line-height:1.6;white-space:pre-wrap"></p>
    </div>
    <div class="sk-modal-footer">
      <button type="button" class="sk-btn sk-btn-ghost" onclick="closeDescriptionModal()">Close</button>
    </div>
  </div>
</div>

<script>
const allOpportunities = <?php echo json_encode($opportunitiesList, JSON_UNESCAPED_UNICODE); ?>;
let filteredOpportunities = [...allOpportunities];

const searchInput = document.getElementById('searchInput');
const typeFilter = document.getElementById('typeFilter');
const statusFilter = document.getElementById('statusFilter');
const tableBody = document.getElementById('tableBody');
const emptyState = document.getElementById('emptyState');
const oppCount = document.getElementById('oppCount');

function performSearch() {
  const search = searchInput.value.trim();
  const type = typeFilter.value;
  const status = statusFilter.value;
  
  fetch('<?= appUrl('View/FrontOffice/opportunities.php') ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'ajax=1&search=' + encodeURIComponent(search) + '&type=' + encodeURIComponent(type) + '&status=' + encodeURIComponent(status)
  })
  .then(response => response.json())
  .then(data => {
    filteredOpportunities = data;
    updateTable();
  })
  .catch(error => console.error('Error:', error));
}

function updateTable() {
  if (filteredOpportunities.length === 0) {
    tableBody.innerHTML = '';
    emptyState.style.display = 'block';
  } else {
    emptyState.style.display = 'none';
    tableBody.innerHTML = '';
    
    filteredOpportunities.forEach((opp, index) => {
      const sc = opp.Statut === 'actif' ? 'actif' : (opp.Statut === 'archivé' ? 'archive' : 'expire');
      const row = document.createElement('tr');
      row.innerHTML = `
        <td style="font-weight:600">${escapeHtml(opp.Titre)}</td>
        <td><span class="sk-badge sk-badge-${escapeHtml(opp.Type_job)}">${escapeHtml(opp.Type_job)}</span></td>
        <td style="color:var(--sk-muted)">${escapeHtml(opp.Localisation ?? '—')}</td>
        <td style="color:var(--sk-muted)">${escapeHtml(opp.datePublication ?? '—')}</td>
        <td><span class="sk-badge sk-badge-${sc}">${escapeHtml(opp.Statut)}</span></td>
        <td style="text-align: center;">
          <button class="sk-btn sk-btn-sm sk-btn-ghost"
                  onclick="viewOpportunityDescriptionFromList(${index})"
                  title="View description">
            <i class="bx bx-show"></i> Description
          </button>
          <button class="sk-btn sk-btn-sm fav-btn" 
                  onclick="toggleFavorite(${opp.ID}, this)"
                  title="Add to favorites"
                  style="background: none; border: none; padding: 4px 8px; cursor: pointer; font-size: 1.2em;">
            <i class="bx bx-heart" style="color: #ccc; display: inline-block;"></i>
          </button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  }
  
  oppCount.textContent = filteredOpportunities.length;
}

function viewOpportunityDescription(title, description) {
  document.getElementById('descriptionTitle').textContent = title || 'Opportunity Description';
  document.getElementById('descriptionText').textContent = description || 'No description provided.';
  document.getElementById('descriptionModalOverlay').classList.add('open');
}

function viewOpportunityDescriptionFromList(index) {
  const opp = filteredOpportunities[index];
  if (!opp) return;
  viewOpportunityDescription(opp.Titre || '', opp.Description || '');
}

function closeDescriptionModal() {
  document.getElementById('descriptionModalOverlay').classList.remove('open');
}

function resetFilters() {
  searchInput.value = '';
  typeFilter.value = '';
  statusFilter.value = '';
  performSearch();
}

function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function toggleFavorite(opportunityId, btn) {
  fetch('<?= $_SERVER['PHP_SELF'] ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'favorite_action=1&opportunity_id=' + opportunityId
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const icon = btn.querySelector('i');
      if (data.favorited) {
        btn.classList.add('favorited');
        icon.style.color = '#ff4d6d';
        icon.classList.remove('bx-heart');
        icon.classList.add('bxs-heart');
        btn.title = 'Remove from favorites';
      } else {
        btn.classList.remove('favorited');
        icon.style.color = '#ccc';
        icon.classList.remove('bxs-heart');
        icon.classList.add('bx-heart');
        btn.title = 'Add to favorites';
      }
    } else {
      alert(data.message || 'Error updating favorite');
    }
  })
  .catch(e => console.error('Error:', e));
}

function exportPDF() {
  const element = document.getElementById('opportunitiesTable');
  const opt = {
    margin: 10,
    filename: 'opportunities_export.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
  };
  html2pdf().set(opt).from(element).save();
}

// Add event listeners for real-time search
searchInput.addEventListener('input', performSearch);
typeFilter.addEventListener('change', performSearch);
statusFilter.addEventListener('change', performSearch);

document.getElementById('descriptionModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeDescriptionModal();
});
</script>

<style>
.sk-badge-jobs { background: rgba(91,108,255,.15); color: #7c8fff; border: 1px solid rgba(91,108,255,.25); }
.sk-badge-freelance { background: rgba(245,166,35,.15); color: #f5a623; border: 1px solid rgba(245,166,35,.25); }
.sk-badge-stage { background: rgba(34,211,165,.15); color: #22d3a5; border: 1px solid rgba(34,211,165,.25); }
.sk-badge-actif { background: rgba(34,211,165,.15); color: #22d3a5; border: 1px solid rgba(34,211,165,.25); }
.sk-badge-archive { background: rgba(122,128,153,.15); color: var(--sk-muted); border: 1px solid rgba(122,128,153,.25); }
.sk-badge-expire { background: rgba(255,77,109,.15); color: #ff4d6d; border: 1px solid rgba(255,77,109,.25); }

.fav-btn {
  transition: all 0.3s ease;
  transform: scale(1);
}

.fav-btn:hover {
  transform: scale(1.2);
}

.fav-btn.favorited i {
  animation: heartBeat 0.6s ease;
}

@keyframes heartBeat {
  0% {
    transform: scale(1);
  }
  25% {
    transform: scale(1.3);
  }
  50% {
    transform: scale(1.1);
  }
  75% {
    transform: scale(1.35);
  }
  100% {
    transform: scale(1);
  }
}
</style>
</body>
</html>

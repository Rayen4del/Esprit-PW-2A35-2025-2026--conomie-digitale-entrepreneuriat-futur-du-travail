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

// Gestion AJAX des favoris
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_action'])) {
    if (!$userId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Non connecte']);
        exit;
    }
    $opportunityId = (int)($_POST['opportunity_id'] ?? 0);
    $favCtrl->toggleFavorite($userId, $opportunityId);
}

// Recherche AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json');
    $search = trim($_POST['search'] ?? '');
    $typeFilter = trim($_POST['type'] ?? '');
    $statusFilter = trim($_POST['status'] ?? '');
    
    $results = [];
    foreach ($opportunitiesList as $opp) {
        $matches = true;
        
        // Recherche dans le titre, la description, le lieu et la date de publication
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
        
        // Filtrer par type
        if ($typeFilter !== '') {
            if ($opp['Type_job'] !== $typeFilter) {
                $matches = false;
            }
        }
        
        // Filtrer par statut
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

// Recuperer les types et statuts uniques
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
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Opportunites - Skiller</title>
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
      Parcourir les opportunites
      <small><span id="oppCount"><?= count($opportunitiesList) ?></span> offre<?= count($opportunitiesList) !== 1 ? 's' : '' ?> disponible<?= count($opportunitiesList) !== 1 ? 's' : '' ?></small>
    </div>
    <div style="display: flex; gap: 12px;">
      <a href="ai-search.php" class="sk-btn sk-btn-secondary">
        <i class="bx bx-brain"></i> Recherche IA
      </a>
      <a href="favorites.php" class="sk-btn sk-btn-secondary">
        <i class="bx bxs-heart"></i> Mes favoris
      </a>
      <button class="sk-btn sk-btn-primary" onclick="exportPDF()">
        <i class="bx bx-download"></i> Exporter en PDF
      </button>
    </div>
  </div>

  <!-- Filtres -->
  <div class="sk-card" style="margin-bottom: 24px;">
    <div style="padding: 20px;">
      <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 16px;">
        <!-- Barre de recherche -->
        <div>
          <label class="sk-label">Recherche</label>
          <input type="text" id="searchInput" class="sk-input" placeholder="Rechercher par titre ou description...">
        </div>
        
        <!-- Filtre type -->
        <div>
          <label class="sk-label">Type</label>
          <select id="typeFilter" class="sk-select">
            <option value="">Tous les types</option>
            <?php foreach ($types as $type): ?>
              <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <!-- Filtre statut -->
        <div>
          <label class="sk-label">Statut</label>
          <select id="statusFilter" class="sk-select">
            <option value="">Tous les statuts</option>
            <?php foreach ($statuses as $status): ?>
              <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Bouton de reinitialisation -->
        <div style="display: flex; align-items: flex-end;">
          <button type="button" class="sk-btn sk-btn-ghost" onclick="resetFilters()" style="width: 100%;">
            <i class="bx bx-refresh"></i> Reinitialiser
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Tableau des opportunites -->
  <div class="sk-card">
    <table class="sk-table" id="opportunitiesTable">
      <thead>
        <tr>
          <th>Titre</th><th>Type</th><th>Lieu</th><th>Publie le</th><th>Statut</th><th style="text-align: center; width: 180px;">Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php foreach ($opportunitiesList as $row):
          $sc = $row['Statut'] === 'actif' ? 'actif' : ($row['Statut'] === 'archivÃ©' ? 'archive' : 'expire');
          $isFav = $userId ? $favCtrl->isFavorited($userId, $row['ID']) : false;
        ?>
          <tr>
            <td style="font-weight:600"><?= htmlspecialchars($row['Titre']) ?></td>
            <td><span class="sk-badge sk-badge-<?= htmlspecialchars($row['Type_job']) ?>"><?= htmlspecialchars($row['Type_job']) ?></span></td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($row['Localisation'] ?? 'â€”') ?></td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($row['datePublication'] ?? 'â€”') ?></td>
            <td><span class="sk-badge sk-badge-<?= $sc ?>"><?= htmlspecialchars($row['Statut']) ?></span></td>
            <td style="text-align: center;">
              <button class="sk-btn sk-btn-sm sk-btn-ghost"
                      onclick="viewOpportuniteDescription(<?= htmlspecialchars(json_encode($row['Titre'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($row['Description'] ?? ''), ENT_QUOTES) ?>)"
                      title="Voir la description">
                <i class="bx bx-show"></i> Description
              </button>
              <button class="sk-btn sk-btn-sm fav-btn <?= $isFav ? 'favorited' : '' ?>" 
                      onclick="toggleFavorite(<?= $row['ID'] ?>, this)"
                      title="<?= $isFav ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>"
                      style="background: none; border: none; padding: 4px 8px; cursor: pointer; font-size: 1.2em;">
                <i class="bx <?= $isFav ? 'bxs-heart' : 'bx-heart' ?>" style="color: <?= $isFav ? '#ff4d6d' : '#ccc' ?>; display: inline-block;"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div id="emptyState" class="sk-empty" style="display: none;">
      <p>Aucune opportunite ne correspond a vos filtres.</p>
    </div>
  </div>
</div>

<div class="sk-modal-overlay" id="descriptionModalOverlay" role="dialog" aria-modal="true">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <div class="sk-modal-title" id="descriptionTitre">Description de l opportunite</div>
      <button type="button" class="sk-modal-close" onclick="closeDescriptionModal()" aria-label="Fermer">&times;</button>
    </div>
    <div class="sk-modal-body">
      <p id="descriptionText" style="margin:0;color:var(--sk-text);line-height:1.6;white-space:pre-wrap"></p>
    </div>
    <div class="sk-modal-footer">
      <button type="button" class="sk-btn sk-btn-ghost" onclick="closeDescriptionModal()">Fermer</button>
    </div>
  </div>
</div>

<script>
const allOpportunites = <?php echo json_encode($opportunitiesList, JSON_UNESCAPED_UNICODE); ?>;
let filteredOpportunites = [...allOpportunites];

const searchInput = document.getElementById('searchInput');
const typeFilter = document.getElementById('typeFilter');
const statusFilter = document.getElementById('statusFilter');
const tableBody = document.getElementById('tableBody');
const emptyState = document.getElementById('emptyState');
const oppCount = document.getElementById('oppCount');

function performRecherche() {
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
    filteredOpportunites = data;
    updateTable();
  })
  .catch(error => console.error('Erreur :', error));
}

function updateTable() {
  if (filteredOpportunites.length === 0) {
    tableBody.innerHTML = '';
    emptyState.style.display = 'block';
  } else {
    emptyState.style.display = 'none';
    tableBody.innerHTML = '';
    
    filteredOpportunites.forEach((opp, index) => {
      const sc = opp.Statut === 'actif' ? 'actif' : (opp.Statut === 'archivÃ©' ? 'archive' : 'expire');
      const row = document.createElement('tr');
      row.innerHTML = `
        <td style="font-weight:600">${escapeHtml(opp.Titre)}</td>
        <td><span class="sk-badge sk-badge-${escapeHtml(opp.Type_job)}">${escapeHtml(opp.Type_job)}</span></td>
        <td style="color:var(--sk-muted)">${escapeHtml(opp.Localisation ?? 'â€”')}</td>
        <td style="color:var(--sk-muted)">${escapeHtml(opp.datePublication ?? 'â€”')}</td>
        <td><span class="sk-badge sk-badge-${sc}">${escapeHtml(opp.Statut)}</span></td>
        <td style="text-align: center;">
          <button class="sk-btn sk-btn-sm sk-btn-ghost"
                  onclick="viewOpportuniteDescriptionFromList(${index})"
                  title="Voir la description">
            <i class="bx bx-show"></i> Description
          </button>
          <button class="sk-btn sk-btn-sm fav-btn" 
                  onclick="toggleFavorite(${opp.ID}, this)"
                  title="Ajouter aux favoris"
                  style="background: none; border: none; padding: 4px 8px; cursor: pointer; font-size: 1.2em;">
            <i class="bx bx-heart" style="color: #ccc; display: inline-block;"></i>
          </button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  }
  
  oppCount.textContent = filteredOpportunites.length;
}

function viewOpportuniteDescription(title, description) {
  document.getElementById('descriptionTitre').textContent = title || 'Description de l opportunite';
  document.getElementById('descriptionText').textContent = description || 'Aucune description fournie.';
  document.getElementById('descriptionModalOverlay').classList.add('open');
}

function viewOpportuniteDescriptionFromList(index) {
  const opp = filteredOpportunites[index];
  if (!opp) return;
  viewOpportuniteDescription(opp.Titre || '', opp.Description || '');
}

function closeDescriptionModal() {
  document.getElementById('descriptionModalOverlay').classList.remove('open');
}

function resetFilters() {
  searchInput.value = '';
  typeFilter.value = '';
  statusFilter.value = '';
  performRecherche();
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
        btn.title = 'Retirer des favoris';
      } else {
        btn.classList.remove('favorited');
        icon.style.color = '#ccc';
        icon.classList.remove('bxs-heart');
        icon.classList.add('bx-heart');
        btn.title = 'Ajouter aux favoris';
      }
    } else {
      alert(data.message || 'Erreur lors de la mise a jour du favori');
    }
  })
  .catch(e => console.error('Erreur :', e));
}

function exportPDF() {
  const element = document.getElementById('opportunitiesTable');
  const opt = {
    margin: 10,
    filename: 'opportunites_export.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
  };
  html2pdf().set(opt).from(element).save();
}

// Recherche en temps reel
searchInput.addEventListener('input', performRecherche);
typeFilter.addEventListener('change', performRecherche);
statusFilter.addEventListener('change', performRecherche);

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



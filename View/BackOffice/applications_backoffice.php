<?php
// View/BackOffice/applications_backoffice.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/ApplicationController.php';
requireRole(['admin']);

$assetPath = '../assets/';
$applicationCtrl = new ApplicationController();
$successMsg = '';
$errorMsg = '';

// AJAX avant tout affichage.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === '1') {
    $search    = trim($_POST['search'] ?? '');
    $sortBy    = $_POST['sort_by']    ?? 'DateCondidature';
    $sortOrder = $_POST['sort_order'] ?? 'DESC';
    $typeJob   = trim($_POST['type_job'] ?? '');
    $applicationsData = $applicationCtrl
        ->listApplicationsWithDetails($search, $sortBy, $sortOrder, $typeJob)
        ->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($applicationsData, JSON_UNESCAPED_UNICODE);
    exit;
}

// â”€â”€ Supprimer action â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $appId = $_POST['app_id'] ?? null;
    if ($appId) {
        try {
            $applicationCtrl->deleteApplication((int)$appId);
            $successMsg = 'Candidature supprimee avec succes !';
        } catch (Exception $e) {
            $errorMsg = 'Erreur : ' . $e->getMessage();
        }
    }
}

// â”€â”€ Fetch all data for initial render â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$applicationsData = $applicationCtrl->listApplicationsWithDetails()->fetchAll(PDO::FETCH_ASSOC);
$applicationTypes = $applicationCtrl->listApplicationTypes()->fetchAll(PDO::FETCH_COLUMN);

function applicationStatusLabel($status) {
    $key = strtolower((string)$status);
    return [
        'pending' => 'En attente',
        'accepted' => 'Acceptee',
        'rejected' => 'Rejetee'
    ][$key] ?? ucfirst((string)$status);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Candidatures - Administration</title>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">

  <div class="sk-page-header">
    <div class="sk-page-title">
      Gestion des candidatures
      <small>Consulter et supprimer les candidatures (administrateur)</small>
    </div>
    <span class="sk-badge sk-badge-jobs" style="font-size:.7rem;align-self:flex-start">Vue administrateur</span>
  </div>

  <!-- â”€â”€ Recherche / Sort bar â”€â”€ -->
  <div class="sk-card" style="margin-bottom:20px">
    <div class="sk-filter-bar">
      <div class="sk-filter-field sk-filter-search">
        <label class="sk-label">Recherche</label>
        <div style="position:relative">
          <i class="bx bx-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--sk-muted)"></i>
          <input type="text" id="searchInput" class="sk-input"
                 placeholder="Opportunite, utilisateur, statut, motivation..."
                 style="padding-left:34px" autocomplete="off">
        </div>
      </div>
      <div class="sk-filter-field">
        <label class="sk-label">Trier par</label>
        <select id="sortBy" class="sk-select">
          <option value="ID">ID</option>
          <option value="opportunity_title">Opportunite</option>
          <option value="IDUtilisateur">ID utilisateur</option>
          <option value="DateCondidature" selected>Date de candidature</option>
          <option value="Type_job">Type de poste</option>
        </select>
      </div>
      <div class="sk-filter-field">
        <label class="sk-label">Type de poste</label>
        <select id="typeFilter" class="sk-select">
          <option value="">Tous les types</option>
          <?php foreach ($applicationTypes as $type): ?>
            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars(ucfirst($type)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="sk-filter-field">
        <label class="sk-label">Ordre</label>
        <select id="sortOrder" class="sk-select">
          <option value="DESC">Decroissant</option>
          <option value="ASC">Croissant</option>
        </select>
      </div>
      <div class="sk-filter-field" style="align-self:flex-end">
        <button class="sk-btn sk-btn-ghost" onclick="resetFilters()">
          <i class="bx bx-refresh"></i> Reinitialiser
        </button>
      </div>
    </div>
  </div>

  <?php if ($successMsg): ?>
    <div class="sk-toast sk-toast-success" id="sk-toast">
      <i class="bx bx-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
    </div>
  <?php elseif ($errorMsg): ?>
    <div class="sk-toast sk-toast-danger" id="sk-toast">
      <i class="bx bx-x-circle"></i> <?= htmlspecialchars($errorMsg) ?>
    </div>
  <?php endif; ?>

  <!-- Resultats count -->
  <div style="font-size:.8rem;color:var(--sk-muted);margin-bottom:10px" id="resultCount">
    <?= count($applicationsData) ?> application<?= count($applicationsData) !== 1 ? 's' : '' ?>
  </div>

  <div class="sk-card">
    <table class="sk-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Opportunite</th>
          <th>Utilisateur</th>
          <th>Date de candidature</th>
          <th>Statut</th>
          <th>Motivation</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php foreach ($applicationsData as $app): ?>
          <?php $statut = strtolower($app['Statut'] ?? 'pending'); ?>
          <tr>
            <td style="color:var(--sk-muted);font-size:.8rem">#<?= htmlspecialchars($app['ID']) ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($app['opportunity_title'] ?? 'N/A') ?></td>
            <td>
              <div style="font-weight:600"><?= htmlspecialchars($app['user_name'] ?? ('Utilisateur #' . $app['IDUtilisateur'])) ?></div>
              <div style="font-size:.75rem;color:var(--sk-muted)"><?= htmlspecialchars($app['user_email'] ?? ('#' . $app['IDUtilisateur'])) ?></div>
            </td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($app['DateCondidature'] ?? 'â€”') ?></td>
            <td>
              <span class="sk-badge sk-badge-<?= $statut ?>">
                <?= htmlspecialchars(applicationStatusLabel($app['Statut'] ?? 'pending')) ?>
              </span>
            </td>
            <td class="sk-motivation-cell" title="<?= htmlspecialchars($app['motivation'] ?? '') ?>">
              <?= htmlspecialchars(mb_substr($app['motivation'] ?? '', 0, 60)) ?><?= strlen($app['motivation'] ?? '') > 60 ? 'â€¦' : '' ?>
            </td>
            <td style="white-space:nowrap">
              <button class="sk-btn sk-btn-ghost sk-btn-sm"
                      onclick="showDetails(<?= (int)$app['ID'] ?>, <?= htmlspecialchars(json_encode($app['opportunity_title'] ?? 'N/A'), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($app['motivation'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($app['CV'] ?? ''), ENT_QUOTES) ?>)">
                <i class="bx bx-show"></i> Voir la candidature
              </button>
              <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette candidature ?')">
                <input type="hidden" name="action"  value="delete">
                <input type="hidden" name="app_id"  value="<?= (int)$app['ID'] ?>">
                <button type="submit" class="sk-btn sk-btn-danger sk-btn-sm"><i class="bx bx-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($applicationsData)): ?>
          <tr><td colspan="7" class="sk-empty">Aucune candidature trouvee.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div id="emptyState" class="sk-empty" style="display:none">Aucune candidature ne correspond a votre recherche.</div>
  </div>
</div>

<!-- Details Modal -->
<div class="sk-modal-overlay" id="detailsModal">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <span class="sk-modal-title">Details de la candidature</span>
      <button class="sk-modal-close" onclick="closeModal()">Ã—</button>
    </div>
    <div class="sk-modal-body">
      <div class="sk-form-group">
        <label class="sk-label">Opportunite</label>
        <p id="modalOpportunite" style="font-weight:600;color:var(--sk-text)"></p>
      </div>
      <div class="sk-form-group">
        <label class="sk-label">Motivation</label>
        <p id="modalMotivation" style="line-height:1.6;color:var(--sk-text);white-space:pre-wrap"></p>
      </div>
      <div class="sk-form-group">
        <label class="sk-label">CV / Ressource</label>
        <div id="modalCV"></div>
      </div>
    </div>
    <div class="sk-modal-footer">
      <button class="sk-btn sk-btn-ghost" onclick="closeModal()">Fermer</button>
    </div>
  </div>
</div>

<style>
.sk-filter-bar {
  padding: 16px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: flex-end;
}
.sk-filter-field { display: flex; flex-direction: column; gap: 6px; min-width: 140px; }
.sk-filter-search { flex: 1; min-width: 220px; }
.sk-motivation-cell {
  max-width: 240px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--sk-muted);
  font-size: .85rem;
}
</style>

<script>
// Seed data from PHP â€” source of truth for client-side filter
const ALL_DATA = <?= json_encode($applicationsData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

let debounceTimer = null;
let currentTableData = ALL_DATA;

function scheduleRecherche() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(performRecherche, 220);
}

function performRecherche() {
  const sortBy    = document.getElementById('sortBy').value;
  const sortOrder = document.getElementById('sortOrder').value;
  const typeJob   = document.getElementById('typeFilter').value;

  // Send to PHP for authoritative filter+sort (handles edge cases server-side)
  fetch(window.location.pathname, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax=1'
        + '&search='     + encodeURIComponent(document.getElementById('searchInput').value.trim())
        + '&sort_by='    + encodeURIComponent(sortBy)
        + '&sort_order=' + encodeURIComponent(sortOrder)
        + '&type_job='   + encodeURIComponent(typeJob)
  })
  .then(r => {
    if (!r.ok) throw new Error('Erreur serveur ' + r.status);
    return r.json();
  })
  .then(data => renderTable(data))
  .catch(err => console.error('Erreur de recherche :', err));
}

function renderTable(data) {
  const tbody      = document.getElementById('tableBody');
  const emptyState = document.getElementById('emptyState');
  const counter    = document.getElementById('resultCount');
  currentTableData = Array.isArray(data) ? data : [];

  counter.textContent = currentTableData.length + ' application' + (currentTableData.length !== 1 ? 's' : '');

  if (currentTableData.length === 0) {
    tbody.innerHTML = '';
    emptyState.style.display = 'block';
    return;
  }

  emptyState.style.display = 'none';

  tbody.innerHTML = currentTableData.map((app, index) => {
    const statut   = (app.Statut || 'pending').toLowerCase();
    const statusLabels = { pending: 'En attente', accepted: 'Acceptee', rejected: 'Rejetee' };
    const ucStatut = statusLabels[statut] || (statut.charAt(0).toUpperCase() + statut.slice(1));
    const motiv    = app.motivation || '';
    const short    = motiv.length > 60 ? motiv.substring(0, 60) + '...' : motiv;

    return `
      <tr>
        <td style="color:var(--sk-muted);font-size:.8rem">#${esc(app.ID)}</td>
        <td style="font-weight:600">${esc(app.opportunity_title || 'N/A')}</td>
        <td>
          <div style="font-weight:600">${esc(app.user_name || ('Utilisateur #' + app.IDUtilisateur))}</div>
          <div style="font-size:.75rem;color:var(--sk-muted)">${esc(app.user_email || ('#' + app.IDUtilisateur))}</div>
        </td>
        <td style="color:var(--sk-muted)">${esc(app.DateCondidature || 'â€”')}</td>
        <td><span class="sk-badge sk-badge-${statut}">${ucStatut}</span></td>
        <td class="sk-motivation-cell" title="${esc(motiv)}">${esc(short)}</td>
        <td style="white-space:nowrap">
          <button class="sk-btn sk-btn-ghost sk-btn-sm"
                  onclick="showDetailsFromRendered(${index})">
            <i class="bx bx-show"></i> Voir la candidature
          </button>
          <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette candidature ?')">
            <input type="hidden" name="action"  value="delete">
            <input type="hidden" name="app_id"  value="${parseInt(app.ID)}">
            <button type="submit" class="sk-btn sk-btn-danger sk-btn-sm"><i class="bx bx-trash"></i></button>
          </form>
        </td>
      </tr>`;
  }).join('');
}

function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showDetailsFromRendered(index) {
  const app = currentTableData[index];
  if (!app) return;
  showDetails(parseInt(app.ID), app.opportunity_title || 'N/A', app.motivation || '', app.CV || '');
}

function showDetails(id, opportunity, motivation, cv) {
  document.getElementById('modalOpportunite').textContent = opportunity;
  document.getElementById('modalMotivation').textContent  = motivation || '(aucune)';
  const cvEl = document.getElementById('modalCV');
  if (cv) {
    cvEl.innerHTML = `<a href="${esc(cv)}" target="_blank" rel="noopener" style="color:var(--sk-accent)">${esc(cv)}</a>`;
  } else {
    cvEl.innerHTML = '<span style="color:var(--sk-muted)">Aucun CV fourni</span>';
  }
  document.getElementById('detailsModal').classList.add('open');
}

function closeModal() {
  document.getElementById('detailsModal').classList.remove('open');
}

function resetFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('sortBy').value      = 'DateCondidature';
  document.getElementById('sortOrder').value   = 'DESC';
  document.getElementById('typeFilter').value = '';
  performRecherche();
}

// Event listeners
document.getElementById('searchInput').addEventListener('input',  scheduleRecherche);
document.getElementById('sortBy').addEventListener('change',      performRecherche);
document.getElementById('sortOrder').addEventListener('change',   performRecherche);
document.getElementById('typeFilter').addEventListener('change', performRecherche);

// Fermer modal on backdrop click
document.getElementById('detailsModal').addEventListener('click', e => {
  if (e.target === document.getElementById('detailsModal')) closeModal();
});

// Auto-dismiss toasts
const toast = document.getElementById('sk-toast');
if (toast) {
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .4s'; setTimeout(() => toast.remove(), 400); }, 4000);
}
</script>
</body>
</html>



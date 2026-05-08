<?php
include_once '/../../../config.php';
include_once  '/../../../Controller/gestion_opportunite/OportunityController.php';

$controller = new OportunityController();
$message = ''; $messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $datePublication = !empty($_POST['datePublication']) ? new DateTime($_POST['datePublication']) : null;
        $oportunity = new Oportunity($id, $_POST['Titre']??null, $_POST['Type_job']??null, $_POST['Description']??null, $_POST['Localisation']??null, $datePublication, $_POST['Statut']??null);
        $controller->updateOportunity($oportunity, $id);
        $message = 'Opportunity updated!'; $messageType = 'success';
    } elseif ($_POST['action'] === 'delete') {
        $controller->deleteOportunity((int)$_POST['id']);
        $message = 'Opportunity deleted.'; $messageType = 'danger';
    }
}
if (isset($_GET['fetch_id'])) {
    header('Content-Type: application/json');
    echo json_encode($controller->showOportunity((int)$_GET['fetch_id']));
    exit();
}
$opportunitiesList = ($list = $controller->listOportunities()) ? $list->fetchAll(PDO::FETCH_ASSOC) : [];

function opportunityStatusKey($status) {
    $status = strtolower((string)$status);
    if (strpos($status, 'actif') !== false) return 'active';
    if (strpos($status, 'archiv') !== false) return 'archived';
    if (strpos($status, 'expir') !== false) return 'expired';
    return 'other';
}

$totalOpportunities = count($opportunitiesList);
$statusStats = ['active' => 0, 'archived' => 0, 'expired' => 0];
$typeStats = [];
$recentOpportunities = 0;
$latestTimestamp = null;

foreach ($opportunitiesList as $opportunity) {
    $statusKey = opportunityStatusKey($opportunity['Statut'] ?? '');
    if (isset($statusStats[$statusKey])) {
        $statusStats[$statusKey]++;
    }

    $type = $opportunity['Type_job'] ?? 'Other';
    $typeStats[$type] = ($typeStats[$type] ?? 0) + 1;

    $publishedAt = strtotime($opportunity['datePublication'] ?? '');
    if ($publishedAt) {
        if ($publishedAt >= strtotime('-30 days')) {
            $recentOpportunities++;
        }
        if ($latestTimestamp === null || $publishedAt > $latestTimestamp) {
            $latestTimestamp = $publishedAt;
        }
    }
}

arsort($typeStats);
$topType = $typeStats ? array_key_first($typeStats) : 'N/A';
$topTypeCount = $typeStats[$topType] ?? 0;
$activePercent = $totalOpportunities > 0 ? round(($statusStats['active'] / $totalOpportunities) * 100) : 0;
$latestPublished = $latestTimestamp ? date('M d, Y', $latestTimestamp) : 'N/A';

// Handle AJAX search and sort
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json');
    $search = trim($_POST['search'] ?? '');
    $sortBy = $_POST['sort_by'] ?? 'ID';
    $sortOrder = $_POST['sort_order'] ?? 'ASC';
    
    $filtered = $opportunitiesList;
    
    // Search filter
    if ($search !== '') {
        $filtered = array_filter($filtered, function($item) use ($search) {
            return stripos($item['Titre'] ?? '', $search) !== false 
                || stripos($item['Localisation'] ?? '', $search) !== false
                || stripos($item['Type_job'] ?? '', $search) !== false;
        });
    }
    
    // Sort
    usort($filtered, function($a, $b) use ($sortBy, $sortOrder) {
        $aVal = $a[$sortBy] ?? '';
        $bVal = $b[$sortBy] ?? '';
        $cmp = strcasecmp($aVal, $bVal);
        return $sortOrder === 'ASC' ? $cmp : -$cmp;
    });
    
    echo json_encode(array_values($filtered));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Opportunities — Back Office</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css">
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <?php if ($message): ?>
    <div class="sk-toast sk-toast-<?= $messageType ?>" id="sk-toast">
      <?= $messageType === 'success' ? '✓' : '✕' ?> <?= htmlspecialchars($message) ?>
    </div>
    <script>setTimeout(()=>{const t=document.getElementById('sk-toast');if(t){t.style.opacity='0';t.style.transition='opacity 0.4s';setTimeout(()=>t.remove(),400);}},3000);</script>
  <?php endif; ?>

  <div class="sk-page-header">
    <div class="sk-page-title">
      Opportunities
      <small>Admin access — edit &amp; delete only</small>
    </div>
    <span class="sk-badge sk-badge-jobs" style="font-size:0.7rem">Admin View</span>
  </div>

  <!-- Search and Sort Controls -->
  <div class="sk-card" style="margin-bottom: 24px;">
    <div style="padding: 16px; display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: flex-end;">
      <div>
        <label class="sk-label" style="font-size: 0.8rem;">Search</label>
        <input type="text" id="searchInput" class="sk-input" placeholder="Search title, type, location..." style="padding: 8px 12px; font-size: 0.875rem;">
      </div>
      <div>
        <label class="sk-label" style="font-size: 0.8rem;">Sort By</label>
        <select id="sortBy" class="sk-select" style="padding: 8px 12px; font-size: 0.875rem;">
          <option value="ID">ID</option>
          <option value="Titre">Title</option>
          <option value="Type_job">Type</option>
          <option value="datePublication">Published Date</option>
        </select>
      </div>
      <div>
        <label class="sk-label" style="font-size: 0.8rem;">Order</label>
        <select id="sortOrder" class="sk-select" style="padding: 8px 12px; font-size: 0.875rem;">
          <option value="ASC">Ascending</option>
          <option value="DESC">Descending</option>
        </select>
      </div>
      <button type="button" class="sk-btn sk-btn-ghost" onclick="resetSearch()" style="padding: 8px 12px; font-size: 0.875rem;"><i class="bx bx-refresh"></i> Reset</button>
    </div>
  </div>

  <div class="sk-card">
    <table class="sk-table">
      <thead>
        <tr><th>ID</th><th>Title</th><th>Type</th><th>Location</th><th>Published</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody id="tableBody">
        <?php foreach ($opportunitiesList as $row): ?>
          <?php $sc = $row['Statut'] === 'actif' ? 'actif' : ($row['Statut'] === 'archivé' ? 'archive' : 'expire'); ?>
          <tr>
            <td style="color:var(--sk-muted);font-size:0.8rem">#<?= $row['ID'] ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($row['Titre']) ?></td>
            <td><span class="sk-badge sk-badge-<?= htmlspecialchars($row['Type_job']) ?>"><?= htmlspecialchars($row['Type_job']) ?></span></td>
            <td><?= htmlspecialchars($row['Localisation'] ?? '—') ?></td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($row['datePublication'] ?? '—') ?></td>
            <td><span class="sk-badge sk-badge-<?= $sc ?>"><?= htmlspecialchars($row['Statut']) ?></span></td>
            <td>
              <button class="sk-btn sk-btn-warn sk-btn-sm" onclick="openEditModal(<?= $row['ID'] ?>)">Edit</button>
              <button class="sk-btn sk-btn-danger sk-btn-sm" onclick="openDeleteModal(<?= $row['ID'] ?>, '<?= htmlspecialchars($row['Titre'], ENT_QUOTES) ?>')">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div id="emptyState" class="sk-empty" style="display: none; padding: 32px; text-align: center;">No opportunities found.</div>
  </div>

  <div class="sk-stats-panel" aria-label="Opportunities statistics">
    <div class="sk-stat-card sk-stat-wide">
      <div class="sk-stat-top">
        <span class="sk-stat-icon"><i class="bx bx-briefcase"></i></span>
        <span class="sk-stat-label">Total Opportunities</span>
      </div>
      <div class="sk-stat-value" id="statTotal"><?= $totalOpportunities ?></div>
      <div class="sk-stat-note"><span id="statRecent"><?= $recentOpportunities ?></span> published in the last 30 days</div>
    </div>

    <div class="sk-stat-card">
      <div class="sk-stat-top">
        <span class="sk-stat-icon sk-stat-icon-good"><i class="bx bx-trending-up"></i></span>
        <span class="sk-stat-label">Active Rate</span>
      </div>
      <div class="sk-stat-value"><span id="statActivePercent"><?= $activePercent ?></span>%</div>
      <div class="sk-stat-bar"><span id="statActiveBar" style="width:<?= $activePercent ?>%"></span></div>
    </div>

    <div class="sk-stat-card">
      <div class="sk-stat-top">
        <span class="sk-stat-icon sk-stat-icon-type"><i class="bx bx-category"></i></span>
        <span class="sk-stat-label">Top Type</span>
      </div>
      <div class="sk-stat-value sk-stat-value-text" id="statTopType"><?= htmlspecialchars(ucfirst($topType)) ?></div>
      <div class="sk-stat-note"><span id="statTopTypeCount"><?= $topTypeCount ?></span> <span id="statTopTypeLabel"><?= $topTypeCount === 1 ? 'opportunity' : 'opportunities' ?></span></div>
    </div>

    <div class="sk-stat-card">
      <div class="sk-stat-top">
        <span class="sk-stat-icon sk-stat-icon-date"><i class="bx bx-calendar-star"></i></span>
        <span class="sk-stat-label">Latest Publish</span>
      </div>
      <div class="sk-stat-value sk-stat-value-text" id="statLatest"><?= htmlspecialchars($latestPublished) ?></div>
      <div class="sk-stat-note">Newest opportunity date</div>
    </div>

    <div class="sk-stat-card sk-stat-breakdown">
      <div class="sk-stat-label">Status Breakdown</div>
      <div class="sk-mini-row"><span>Active</span><strong id="statActive"><?= $statusStats['active'] ?></strong></div>
      <div class="sk-mini-row"><span>Archived</span><strong id="statArchived"><?= $statusStats['archived'] ?></strong></div>
      <div class="sk-mini-row"><span>Expired</span><strong id="statExpired"><?= $statusStats['expired'] ?></strong></div>
    </div>
  </div>
</div>

<style>
.sk-stats-panel {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(5, minmax(150px, 1fr));
  gap: 12px;
}
.sk-stat-card {
  min-height: 132px;
  padding: 16px;
  border: 1px solid rgba(99, 102, 241, .12);
  border-radius: 8px;
  background:
    linear-gradient(135deg, rgba(99, 102, 241, .08), rgba(16, 185, 129, .05)),
    var(--sk-card, #fff);
  box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
}
.sk-stat-wide { grid-column: span 1; }
.sk-stat-top {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 12px;
}
.sk-stat-icon {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  color: #4338ca;
  background: rgba(99, 102, 241, .14);
  font-size: 1.1rem;
}
.sk-stat-icon-good { color: #047857; background: rgba(16, 185, 129, .14); }
.sk-stat-icon-type { color: #b45309; background: rgba(245, 158, 11, .16); }
.sk-stat-icon-date { color: #be123c; background: rgba(244, 63, 94, .13); }
.sk-stat-label {
  color: var(--sk-muted);
  font-size: .74rem;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
}
.sk-stat-value {
  color: var(--sk-text);
  font-size: 2rem;
  line-height: 1;
  font-weight: 800;
}
.sk-stat-value-text {
  font-size: 1.05rem;
  line-height: 1.25;
  min-height: 34px;
  display: flex;
  align-items: center;
}
.sk-stat-note {
  margin-top: 10px;
  color: var(--sk-muted);
  font-size: .78rem;
}
.sk-stat-bar {
  height: 8px;
  margin-top: 16px;
  overflow: hidden;
  border-radius: 999px;
  background: rgba(15, 23, 42, .08);
}
.sk-stat-bar span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #10b981, #6366f1);
  transition: width .25s ease;
}
.sk-stat-breakdown {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.sk-mini-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: var(--sk-muted);
  font-size: .82rem;
}
.sk-mini-row strong {
  color: var(--sk-text);
  font-size: .95rem;
}
@media (max-width: 1100px) {
  .sk-stats-panel { grid-template-columns: repeat(2, minmax(180px, 1fr)); }
}
@media (max-width: 640px) {
  .sk-stats-panel { grid-template-columns: 1fr; }
}
</style>

<!-- EDIT MODAL -->
<div class="sk-modal-overlay" id="editModal">
  <div class="sk-modal">
    <div class="sk-modal-header">
      <span class="sk-modal-title">Edit Opportunity</span>
      <button class="sk-modal-close" onclick="closeModal('editModal')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="editId">
      <div class="sk-modal-body" id="editBody"><div style="text-align:center;padding:32px;color:var(--sk-muted)">Loading…</div></div>
      <div class="sk-modal-footer">
        <button type="button" class="sk-btn sk-btn-ghost" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="sk-btn sk-btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- DELETE MODAL -->
<div class="sk-modal-overlay" id="deleteModal">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <span class="sk-modal-title" style="color:var(--sk-danger)">Delete Opportunity</span>
      <button class="sk-modal-close" onclick="closeModal('deleteModal')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" id="deleteId">
      <div class="sk-modal-body">
        <p style="color:var(--sk-muted);font-size:0.875rem">Delete <strong id="deleteTitle" style="color:var(--sk-text)"></strong>? This cannot be undone.</p>
      </div>
      <div class="sk-modal-footer">
        <button type="button" class="sk-btn sk-btn-ghost" onclick="closeModal('deleteModal')">Cancel</button>
        <button type="submit" class="sk-btn sk-btn-danger">Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
const allOpportunities = <?php echo json_encode($opportunitiesList, JSON_UNESCAPED_UNICODE); ?>;
let filteredData = [...allOpportunities];

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openDeleteModal(id, title) {
  document.getElementById('deleteId').value = id;
  document.getElementById('deleteTitle').textContent = title;
  openModal('deleteModal');
}
function openEditModal(id) {
  document.getElementById('editId').value = id;
  document.getElementById('editBody').innerHTML = '<div style="text-align:center;padding:32px;color:var(--sk-muted)">Loading…</div>';
  openModal('editModal');
  fetch('?fetch_id=' + id).then(r => r.json()).then(d => {
    document.getElementById('editBody').innerHTML = `
      <div class="sk-form-row">
        <div><label class="sk-label">Title *</label><input type="text" name="Titre" class="sk-input" value="${esc(d.Titre||'')}" required></div>
        <div><label class="sk-label">Type *</label><select name="Type_job" class="sk-select" required>
          <option value="">Choose…</option>
          <option value="jobs" ${d.Type_job==='jobs'?'selected':''}>Jobs</option>
          <option value="freelance" ${d.Type_job==='freelance'?'selected':''}>Freelance</option>
          <option value="stage" ${d.Type_job==='stage'?'selected':''}>Stage</option>
        </select></div>
      </div>
      <div class="sk-form-group"><label class="sk-label">Description</label><textarea name="Description" class="sk-textarea">${esc(d.Description||'')}</textarea></div>
      <div class="sk-form-row">
        <div><label class="sk-label">Location</label><input type="text" name="Localisation" class="sk-input" value="${esc(d.Localisation||'')}"></div>
        <div><label class="sk-label">Publish Date *</label><input type="date" name="datePublication" class="sk-input" value="${esc(d.datePublication||'')}" required></div>
      </div>
      <div class="sk-form-group"><label class="sk-label">Status *</label><select name="Statut" class="sk-select" required>
        <option value="">Choose…</option>
        <option value="actif" ${d.Statut==='actif'?'selected':''}>Actif</option>
        <option value="archivé" ${d.Statut==='archivé'?'selected':''}>Archivé</option>
        <option value="expiré" ${d.Statut==='expiré'?'selected':''}>Expiré</option>
      </select></div>
    `;
  });
}
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function statusKey(status) {
  const value = String(status || '').toLowerCase();
  if (value.includes('actif')) return 'active';
  if (value.includes('archiv')) return 'archived';
  if (value.includes('expir')) return 'expired';
  return 'other';
}

function formatDisplayDate(value) {
  if (!value) return 'N/A';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'N/A';
  return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
}

function updateStats(data) {
  const rows = Array.isArray(data) ? data : [];
  const total = rows.length;
  const statuses = { active: 0, archived: 0, expired: 0 };
  const types = {};
  const now = new Date();
  const recentLimit = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 30);
  let recent = 0;
  let latest = null;

  rows.forEach(row => {
    const key = statusKey(row.Statut);
    if (Object.prototype.hasOwnProperty.call(statuses, key)) statuses[key]++;

    const type = row.Type_job || 'Other';
    types[type] = (types[type] || 0) + 1;

    if (row.datePublication) {
      const published = new Date(row.datePublication);
      if (!Number.isNaN(published.getTime())) {
        if (published >= recentLimit) recent++;
        if (!latest || published > latest) latest = published;
      }
    }
  });

  const topTypeEntry = Object.entries(types).sort((a, b) => b[1] - a[1])[0] || ['N/A', 0];
  const activePercent = total > 0 ? Math.round((statuses.active / total) * 100) : 0;

  document.getElementById('statTotal').textContent = total;
  document.getElementById('statRecent').textContent = recent;
  document.getElementById('statActivePercent').textContent = activePercent;
  document.getElementById('statActiveBar').style.width = activePercent + '%';
  document.getElementById('statTopType').textContent = topTypeEntry[0].charAt(0).toUpperCase() + topTypeEntry[0].slice(1);
  document.getElementById('statTopTypeCount').textContent = topTypeEntry[1];
  document.getElementById('statTopTypeLabel').textContent = topTypeEntry[1] === 1 ? 'opportunity' : 'opportunities';
  document.getElementById('statLatest').textContent = latest ? formatDisplayDate(latest) : 'N/A';
  document.getElementById('statActive').textContent = statuses.active;
  document.getElementById('statArchived').textContent = statuses.archived;
  document.getElementById('statExpired').textContent = statuses.expired;
}

function performSearch() {
  const search = document.getElementById('searchInput').value.trim();
  const sortBy = document.getElementById('sortBy').value;
  const sortOrder = document.getElementById('sortOrder').value;
  
  fetch('<?= $_SERVER['PHP_SELF'] ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax=1&search=' + encodeURIComponent(search) + '&sort_by=' + encodeURIComponent(sortBy) + '&sort_order=' + encodeURIComponent(sortOrder)
  })
  .then(r => r.json())
  .then(data => {
    filteredData = data;
    updateTable();
  })
  .catch(e => console.error('Error:', e));
}

function updateTable() {
  const tbody = document.getElementById('tableBody');
  const emptyState = document.getElementById('emptyState');
  updateStats(filteredData);
  
  if (filteredData.length === 0) {
    tbody.innerHTML = '';
    emptyState.style.display = 'block';
  } else {
    emptyState.style.display = 'none';
    tbody.innerHTML = filteredData.map(row => {
      const sc = row.Statut === 'actif' ? 'actif' : (row.Statut === 'archivé' ? 'archive' : 'expire');
      return `
        <tr>
          <td style="color:var(--sk-muted);font-size:0.8rem">#${row.ID}</td>
          <td style="font-weight:600">${esc(row.Titre)}</td>
          <td><span class="sk-badge sk-badge-${esc(row.Type_job)}">${esc(row.Type_job)}</span></td>
          <td>${esc(row.Localisation ?? '—')}</td>
          <td style="color:var(--sk-muted)">${esc(row.datePublication ?? '—')}</td>
          <td><span class="sk-badge sk-badge-${sc}">${esc(row.Statut)}</span></td>
          <td>
            <button class="sk-btn sk-btn-warn sk-btn-sm" onclick="openEditModal(${row.ID})">Edit</button>
            <button class="sk-btn sk-btn-danger sk-btn-sm" onclick="openDeleteModal(${row.ID}, '${esc(row.Titre)}')">Delete</button>
          </td>
        </tr>
      `;
    }).join('');
  }
}

function resetSearch() {
  document.getElementById('searchInput').value = '';
  document.getElementById('sortBy').value = 'ID';
  document.getElementById('sortOrder').value = 'ASC';
  performSearch();
}

document.getElementById('searchInput').addEventListener('input', performSearch);
document.getElementById('sortBy').addEventListener('change', performSearch);
document.getElementById('sortOrder').addEventListener('change', performSearch);

document.querySelectorAll('.sk-modal-overlay').forEach(el => el.addEventListener('click', e => { if(e.target===el) el.classList.remove('open'); }));
</script>
</body>
</html>

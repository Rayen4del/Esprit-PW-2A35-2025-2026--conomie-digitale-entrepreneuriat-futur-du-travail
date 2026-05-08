<?php
// View/FrontOffice/super_user_applications.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/ApplicationController.php';
require_once __DIR__ . '/../../Controller/AiCompatibilityController.php';
require_once __DIR__ . '/../../Controller/RequirementController.php';

requireLogin();
requireRole(['super_user']);

$assetPath = '../assets/';
$applicationCtrl = new ApplicationController();
$aiCompatibilityCtrl = new AiCompatibilityController();
$requirementCtrl = new RequirementController();
$settingsMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_requirements') {
    $requirementCtrl->saveRequirements($_POST['requirements'] ?? '');
    $settingsMsg = 'Requirements saved successfully.';
}

$requirements = $requirementCtrl->getRequirements();
$requirementsText = $requirementCtrl->requirementsText($requirements);

// Get all applications with details
$applicationsData = $applicationCtrl->listApplicationsWithDetails()->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ai_compatibility'] ?? '') === '1') {
    header('Content-Type: application/json');
    $applicationId = (int)($_POST['application_id'] ?? 0);
    $selectedApplication = null;

    foreach ($applicationsData as $application) {
        if ((int)($application['ID'] ?? 0) === $applicationId) {
            $selectedApplication = $application;
            break;
        }
    }

    if (!$selectedApplication) {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }

    echo json_encode($aiCompatibilityCtrl->scoreApplication($selectedApplication, $requirements), JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Applications — Skiller</title>
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/fonts/boxicons.css">
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      All Applications
      <small>View-only access (Super User)</small>
    </div>
  </div>

  <?php if ($settingsMsg): ?>
    <div class="sk-toast sk-toast-success"><?= htmlspecialchars($settingsMsg) ?></div>
  <?php endif; ?>

  <div class="sk-card" style="margin-bottom:20px;padding:18px;">
    <form method="POST">
      <input type="hidden" name="action" value="save_requirements">
      <div style="display:grid;grid-template-columns:1fr auto;gap:14px;align-items:end;">
        <div>
          <label class="sk-label" for="requirementsBox">Requirement List Used By AI</label>
          <textarea id="requirementsBox" name="requirements" class="sk-textarea" style="min-height:150px;" required><?= htmlspecialchars($requirementsText) ?></textarea>
          <div style="font-size:.8rem;color:var(--sk-muted);margin-top:6px;">Write one requirement per line. AI compares these requirements with each application's submitted CV link.</div>
        </div>
        <button type="submit" class="sk-btn sk-btn-primary">
          <i class="bx bx-save"></i> Save Requirements
        </button>
      </div>
    </form>
  </div>

  <div class="sk-card">
    <?php if (empty($applicationsData)): ?>
      <div class="sk-empty">
        <p>No applications found.</p>
      </div>
    <?php else: ?>
      <table class="sk-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Opportunity</th>
            <th>User ID</th>
            <th>Status</th>
            <th>Date Applied</th>
            <th>AI CV Match</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applicationsData as $app): 
            $statut = strtolower($app['Statut'] ?? $app['statut'] ?? 'pending');
          ?>
            <tr>
              <td><?= htmlspecialchars($app['ID']) ?></td>
              <td><?= htmlspecialchars($app['opportunity_title'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($app['IDUtilisateur'] ?? $app['idUtilisateur'] ?? '—') ?></td>
              <td>
                <span class="sk-badge sk-badge-<?= $statut ?>">
                  <?= htmlspecialchars(ucfirst($app['Statut'] ?? $app['statut'] ?? 'Pending')) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($app['DateCondidature'] ?? $app['dateCondidature'] ?? '—') ?></td>
              <td>
                <div id="ai-score-<?= (int)$app['ID'] ?>" class="ai-score-chip ai-score-empty">Not rated</div>
              </td>
              <td>
                <button type="button" class="sk-btn sk-btn-sm sk-btn-primary" onclick="rateApplication(<?= (int)$app['ID'] ?>, this)">
                  <i class="bx bx-brain"></i> AI Rate
                </button>
                <button type="button" class="sk-btn sk-btn-sm sk-btn-ghost" onclick="showApplicationDetails(
                  <?= htmlspecialchars($app['ID']) ?>, 
                  '<?= addslashes(htmlspecialchars($app['opportunity_title'] ?? '')) ?>', 
                  '<?= addslashes(htmlspecialchars($app['motivation'] ?? '')) ?>', 
                  '<?= addslashes(htmlspecialchars($app['CV'] ?? $app['resource'] ?? '')) ?>'
                )">
                  <i class="bx bx-show"></i> View Application
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- AI Score Modal -->
<div class="sk-modal-overlay" id="aiScoreModal">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <span class="sk-modal-title">AI CV Compatibility</span>
      <button class="sk-modal-close" onclick="closeAiScoreModal()">Ã—</button>
    </div>
    <div class="sk-modal-body">
      <div id="aiScoreBig" style="font-size:2.4rem;font-weight:800;color:var(--sk-accent);line-height:1;">--/100</div>
      <p id="aiScoreSource" style="margin:6px 0 14px;color:var(--sk-muted);font-size:.8rem;"></p>
      <p id="aiScoreSummary" style="margin:0 0 16px;color:var(--sk-text);line-height:1.55;"></p>
      <div style="margin-bottom:14px;">
        <label class="sk-label">Strengths</label>
        <ul id="aiStrengths" style="margin:6px 0 0;padding-left:18px;color:var(--sk-muted);line-height:1.6;"></ul>
      </div>
      <div style="margin-bottom:14px;">
        <label class="sk-label">Gaps</label>
        <ul id="aiGaps" style="margin:6px 0 0;padding-left:18px;color:var(--sk-muted);line-height:1.6;"></ul>
      </div>
      <div>
        <label class="sk-label">Recommendation</label>
        <p id="aiRecommendation" style="margin:6px 0 0;color:var(--sk-text);line-height:1.55;"></p>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="sk-modal-overlay" id="detailsModal">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <span class="sk-modal-title">Application Details</span>
      <button class="sk-modal-close" onclick="closeModal()">×</button>
    </div>
    <div class="sk-modal-body">
      <div style="margin-bottom: 16px;">
        <label class="sk-label">Opportunity</label>
        <p id="modalOpportunity" style="font-weight:500; margin:0;"></p>
      </div>
      <div style="margin-bottom: 16px;">
        <label class="sk-label">Motivation</label>
        <p id="modalMotivation" style="line-height:1.5; white-space:pre-wrap; margin:0;"></p>
      </div>
      <div style="margin-bottom: 16px;">
        <label class="sk-label">CV Link</label>
        <a id="modalCVLink" href="#" target="_blank" style="color:var(--sk-accent);">View CV</a>
      </div>
    </div>
  </div>
</div>

<script>
function rateApplication(applicationId, button) {
  const scoreChip = document.getElementById('ai-score-' + applicationId);
  const originalHtml = button.innerHTML;
  button.disabled = true;
  button.innerHTML = '<i class="bx bx-loader bx-spin"></i> Rating...';
  scoreChip.className = 'ai-score-chip ai-score-loading';
  scoreChip.textContent = 'Rating...';

  fetch('<?= $_SERVER['PHP_SELF'] ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ai_compatibility=1&application_id=' + encodeURIComponent(applicationId)
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) {
      throw new Error(data.message || 'Could not rate application');
    }

    updateAiScoreChip(applicationId, data.score);
    showAiScoreResult(data);
  })
  .catch(error => {
    scoreChip.className = 'ai-score-chip ai-score-empty';
    scoreChip.textContent = 'Failed';
    alert(error.message || 'AI rating failed');
  })
  .finally(() => {
    button.disabled = false;
    button.innerHTML = originalHtml;
  });
}

function updateAiScoreChip(applicationId, score) {
  const chip = document.getElementById('ai-score-' + applicationId);
  chip.textContent = score + '/100';
  chip.className = 'ai-score-chip ' + (score >= 75 ? 'ai-score-good' : (score >= 50 ? 'ai-score-mid' : 'ai-score-low'));
}

function showAiScoreResult(data) {
  document.getElementById('aiScoreBig').textContent = data.score + '/100';
  document.getElementById('aiScoreSource').textContent = data.ai_used ? 'Scored by Gemini AI' : 'Local fallback estimate';
  document.getElementById('aiScoreSummary').textContent = data.summary || '';
  fillList('aiStrengths', data.strengths || []);
  fillList('aiGaps', data.gaps || []);
  document.getElementById('aiRecommendation').textContent = data.recommendation || '';
  document.getElementById('aiScoreModal').classList.add('open');
}

function fillList(id, items) {
  const list = document.getElementById(id);
  list.innerHTML = '';
  if (items.length === 0) {
    const li = document.createElement('li');
    li.textContent = 'No specific items returned.';
    list.appendChild(li);
    return;
  }

  items.forEach(item => {
    const li = document.createElement('li');
    li.textContent = item;
    list.appendChild(li);
  });
}

function closeAiScoreModal() {
  document.getElementById('aiScoreModal').classList.remove('open');
}

function showApplicationDetails(id, opportunity, motivation, cvLink) {
  document.getElementById('modalOpportunity').textContent = opportunity;
  document.getElementById('modalMotivation').textContent = motivation;
  const linkEl = document.getElementById('modalCVLink');
  linkEl.href = cvLink || '#';
  linkEl.textContent = cvLink ? cvLink : 'No CV provided';
  document.getElementById('detailsModal').classList.add('open');
}

function closeModal() {
  document.getElementById('detailsModal').classList.remove('open');
}

document.getElementById('detailsModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

document.getElementById('aiScoreModal').addEventListener('click', function(e) {
  if (e.target === this) closeAiScoreModal();
});
</script>
<style>
.ai-score-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 78px;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: .78rem;
  font-weight: 700;
  border: 1px solid var(--sk-border);
}
.ai-score-empty,
.ai-score-loading {
  color: var(--sk-muted);
  background: rgba(122,128,153,.12);
}
.ai-score-good {
  color: #047857;
  background: rgba(16,185,129,.14);
  border-color: rgba(16,185,129,.25);
}
.ai-score-mid {
  color: #b45309;
  background: rgba(245,158,11,.16);
  border-color: rgba(245,158,11,.28);
}
.ai-score-low {
  color: #be123c;
  background: rgba(244,63,94,.14);
  border-color: rgba(244,63,94,.28);
}
@media (max-width: 900px) {
  .sk-page .sk-card > div[style*="grid-template-columns"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
</body>
</html>

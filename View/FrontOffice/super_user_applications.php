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
    $settingsMsg = 'Exigences enregistrees avec succes.';
}

$requirements = $requirementCtrl->getRequirements();
$requirementsText = $requirementCtrl->requirementsText($requirements);

// Recuperer toutes les candidatures avec details
$applicationsData = $applicationCtrl->listApplicationsWithDetails()->fetchAll(PDO::FETCH_ASSOC);

function applicationStatusLabel($status) {
    $key = strtolower((string)$status);
    return [
        'pending' => 'En attente',
        'accepted' => 'Acceptee',
        'rejected' => 'Rejetee'
    ][$key] ?? ucfirst((string)$status);
}

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
        echo json_encode(['success' => false, 'message' => 'Candidature introuvable']);
        exit;
    }

    echo json_encode($aiCompatibilityCtrl->scoreApplication($selectedApplication, $requirements), JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Toutes les candidatures - Skiller</title>
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/fonts/boxicons.css">
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      Toutes les candidatures
      <small>Acces en lecture seule (super utilisateur)</small>
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
          <label class="sk-label" for="requirementsBox">Liste des exigences utilisee par IA</label>
          <textarea id="requirementsBox" name="requirements" class="sk-textarea" style="min-height:150px;" required><?= htmlspecialchars($requirementsText) ?></textarea>
          <div style="font-size:.8rem;color:var(--sk-muted);margin-top:6px;">Ecrivez une exigence par ligne. IA compare ces exigences avec le lien CV soumis dans chaque candidature.</div>
        </div>
        <button type="submit" class="sk-btn sk-btn-primary">
          <i class="bx bx-save"></i> Enregistrer les exigences
        </button>
      </div>
    </form>
  </div>

  <div class="sk-card">
    <?php if (empty($applicationsData)): ?>
      <div class="sk-empty">
        <p>Aucune candidature trouvee.</p>
      </div>
    <?php else: ?>
      <table class="sk-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Opportunite</th>
            <th>Utilisateur</th>
            <th>Statut</th>
            <th>Date de candidature</th>
            <th>Compatibilite IA du CV</th>
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
              <td>
                <div style="font-weight:600"><?= htmlspecialchars($app['user_name'] ?? ('Utilisateur #' . ($app['IDUtilisateur'] ?? ''))) ?></div>
                <div style="font-size:.75rem;color:var(--sk-muted)"><?= htmlspecialchars($app['user_email'] ?? ('#' . ($app['IDUtilisateur'] ?? ''))) ?></div>
              </td>
              <td>
                <span class="sk-badge sk-badge-<?= $statut ?>">
                  <?= htmlspecialchars(applicationStatusLabel($app['Statut'] ?? $app['statut'] ?? 'pending')) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($app['DateCondidature'] ?? $app['dateCondidature'] ?? 'â€”') ?></td>
              <td>
                <div id="ai-score-<?= (int)$app['ID'] ?>" class="ai-score-chip ai-score-empty">Non evalue</div>
              </td>
              <td>
                <button type="button" class="sk-btn sk-btn-sm sk-btn-primary" onclick="rateApplication(<?= (int)$app['ID'] ?>, this)">
                  <i class="bx bx-brain"></i> Evaluer par IA
                </button>
                <button type="button" class="sk-btn sk-btn-sm sk-btn-ghost" onclick="showApplicationDetails(
                  <?= htmlspecialchars($app['ID']) ?>, 
                  '<?= addslashes(htmlspecialchars($app['opportunity_title'] ?? '')) ?>', 
                  '<?= addslashes(htmlspecialchars($app['motivation'] ?? '')) ?>', 
                  '<?= addslashes(htmlspecialchars($app['CV'] ?? $app['resource'] ?? '')) ?>'
                )">
                  <i class="bx bx-show"></i> Voir la candidature
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- Modale du score IA -->
<div class="sk-modal-overlay" id="aiScoreModal">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <span class="sk-modal-title">Compatibilite IA du CV</span>
      <button class="sk-modal-close" onclick="closeAiScoreModal()">Ãƒâ€”</button>
    </div>
    <div class="sk-modal-body">
      <div id="aiScoreBig" style="font-size:2.4rem;font-weight:800;color:var(--sk-accent);line-height:1;">--/100</div>
      <p id="aiScoreSource" style="margin:6px 0 14px;color:var(--sk-muted);font-size:.8rem;"></p>
      <p id="aiScoreSummary" style="margin:0 0 16px;color:var(--sk-text);line-height:1.55;"></p>
      <div style="margin-bottom:14px;">
        <label class="sk-label">Points forts</label>
        <ul id="aiPoints forts" style="margin:6px 0 0;padding-left:18px;color:var(--sk-muted);line-height:1.6;"></ul>
      </div>
      <div style="margin-bottom:14px;">
        <label class="sk-label">Ecarts</label>
        <ul id="aiEcarts" style="margin:6px 0 0;padding-left:18px;color:var(--sk-muted);line-height:1.6;"></ul>
      </div>
      <div>
        <label class="sk-label">Recommandation</label>
        <p id="aiRecommandation" style="margin:6px 0 0;color:var(--sk-text);line-height:1.55;"></p>
      </div>
    </div>
  </div>
</div>

<!-- Modale -->
<div class="sk-modal-overlay" id="detailsModal">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <span class="sk-modal-title">Details de la candidature</span>
      <button class="sk-modal-close" onclick="closeModal()">Ã—</button>
    </div>
    <div class="sk-modal-body">
      <div style="margin-bottom: 16px;">
        <label class="sk-label">Opportunite</label>
        <p id="modalOpportunite" style="font-weight:500; margin:0;"></p>
      </div>
      <div style="margin-bottom: 16px;">
        <label class="sk-label">Motivation</label>
        <p id="modalMotivation" style="line-height:1.5; white-space:pre-wrap; margin:0;"></p>
      </div>
      <div style="margin-bottom: 16px;">
        <label class="sk-label">Lien du CV</label>
        <a id="modalCVLink" href="#" target="_blank" style="color:var(--sk-accent);">Voir le CV</a>
      </div>
    </div>
  </div>
</div>

<script>
function rateApplication(applicationId, button) {
  const scoreChip = document.getElementById('ai-score-' + applicationId);
  const originalHtml = button.innerHTML;
  button.disabled = true;
  button.innerHTML = '<i class="bx bx-loader bx-spin"></i> Evaluation...';
  scoreChip.className = 'ai-score-chip ai-score-loading';
  scoreChip.textContent = 'Evaluation...';

  fetch('<?= $_SERVER['PHP_SELF'] ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ai_compatibility=1&application_id=' + encodeURIComponent(applicationId)
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) {
      throw new Error(data.message || 'Impossible d evaluer la candidature');
    }

    updateAiScoreChip(applicationId, data.score);
    showAiScoreResult(data);
  })
  .catch(error => {
    scoreChip.className = 'ai-score-chip ai-score-empty';
    scoreChip.textContent = 'Echec';
    alert(error.message || 'Evaluation IA echouee');
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
  document.getElementById('aiScoreSource').textContent = data.ai_used ? 'Evalue par Gemini IA' : 'Estimation locale';
  document.getElementById('aiScoreSummary').textContent = data.summary || '';
  fillList('aiPoints forts', data.strengths || []);
  fillList('aiEcarts', data.gaps || []);
  document.getElementById('aiRecommandation').textContent = data.recommendation || '';
  document.getElementById('aiScoreModal').classList.add('open');
}

function fillList(id, items) {
  const list = document.getElementById(id);
  list.innerHTML = '';
  if (items.length === 0) {
    const li = document.createElement('li');
    li.textContent = 'Aucun element specifique retourne.';
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
  document.getElementById('modalOpportunite').textContent = opportunity;
  document.getElementById('modalMotivation').textContent = motivation;
  const linkEl = document.getElementById('modalCVLink');
  linkEl.href = cvLink || '#';
  linkEl.textContent = cvLink ? cvLink : 'Aucun CV fourni';
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





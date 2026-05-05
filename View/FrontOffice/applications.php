<?php
// View/FrontOffice/applications.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/ApplicationController.php';
require_once __DIR__ . '/../../Controller/OportunityController.php';

requireLogin();
requireRole(['simple_user']);

$assetPath = '../assets/';
$applicationCtrl = new ApplicationController();
$oportunityCtrl = new OportunityController();

$userId = $_SESSION['user']['id'] ?? 1;
$selectedOpportunityId = (int)($_GET['opportunity_id'] ?? 0);

$successMsg = '';
$errorMsg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $opportunityId = (int)($_POST['opportunity_id'] ?? 0);
        $motivation = trim($_POST['motivation'] ?? '');
        $cvLink = trim($_POST['cv_link'] ?? '');

        if ($opportunityId > 0 && $motivation !== '' && $cvLink !== '') {
            try {
                $application = new Application(
                    null,
                    $userId,
                    $opportunityId,
                    new DateTime(),
                    'pending',
                    $motivation,
                    $cvLink
                );
                $applicationCtrl->addApplication($application);
                $successMsg = 'Application submitted successfully!';
            } catch (Exception $e) {
                $errorMsg = 'Create failed: ' . $e->getMessage();
            }
        } else {
            $errorMsg = 'Opportunity, motivation and CV are required.';
        }
    } elseif ($action === 'update') {
        $appId = (int)($_POST['app_id'] ?? 0);
        $motivation = trim($_POST['motivation'] ?? '');
        $cvLink = trim($_POST['cv_link'] ?? '');

        if ($appId > 0 && $motivation !== '' && $cvLink !== '') {
            try {
                $db = config::getConnexion();
                $check = $db->prepare("SELECT * FROM application WHERE ID = :id AND IDUtilisateur = :uid");
                $check->execute([':id' => $appId, ':uid' => $userId]);
                $current = $check->fetch(PDO::FETCH_ASSOC);

                if (!$current) {
                    throw new Exception('Application not found (or not yours).');
                }

                $dateCondidatureRaw = $current['DateCondidature'] ?? $current['dateCondidature'] ?? null;
                $dateCondidature = $dateCondidatureRaw ? new DateTime($dateCondidatureRaw) : null;

                $application = new Application(
                    (int)($current['ID'] ?? $appId),
                    (int)($current['IDUtilisateur'] ?? $userId),
                    (int)($current['idOportunity'] ?? $current['idOportunité'] ?? 0),
                    $dateCondidature,
                    $current['Statut'] ?? $current['statut'] ?? 'pending',
                    $motivation,
                    $cvLink
                );

                $applicationCtrl->updateApplication($application, $appId);
                $successMsg = 'Application updated successfully!';
            } catch (Exception $e) {
                $errorMsg = 'Update failed: ' . $e->getMessage();
            }
        } else {
            $errorMsg = 'Motivation and CV are required.';
        }
    } elseif ($action === 'delete') {
        $appId = (int)($_POST['app_id'] ?? 0);
        if ($appId > 0) {
            try {
                $db = config::getConnexion();
                $check = $db->prepare("SELECT ID FROM application WHERE ID = :id AND IDUtilisateur = :uid");
                $check->execute([':id' => $appId, ':uid' => $userId]);
                if (!$check->fetch()) {
                    throw new Exception('Application not found (or not yours).');
                }
                $applicationCtrl->deleteApplication($appId);
                $successMsg = 'Application deleted successfully!';
            } catch (Exception $e) {
                $errorMsg = 'Delete failed: ' . $e->getMessage();
            }
        }
    }
}

// Fetch data
$db = config::getConnexion();
$query = $db->prepare("
    SELECT a.*, o.Titre as opportunity_title, o.Type_job 
    FROM application a
    JOIN oportunity o ON a.idOportunity = o.ID
    WHERE a.IDUtilisateur = :userId
    ORDER BY a.DateCondidature DESC
");
$query->execute([':userId' => $userId]);
$userApplications = $query->fetchAll(PDO::FETCH_ASSOC);

$opportunities = $oportunityCtrl->listOportunities()->fetchAll(PDO::FETCH_ASSOC);

$editData = [];
foreach ($userApplications as $app) {
    $editData[(int)($app['ID'] ?? 0)] = [
        'motivation' => $app['motivation'] ?? $app['Motivation'] ?? '',
        'resource' => $app['CV'] ?? $app['resource'] ?? $app['Resource'] ?? '',
    ];
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Applications — Skiller</title>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      My Applications
      <small>Create, View, Edit & Delete</small>
    </div>
  </div>

  <?php if ($successMsg): ?>
    <div class="sk-toast sk-toast-success"><?= htmlspecialchars($successMsg) ?></div>
  <?php endif; ?>
  <?php if ($errorMsg): ?>
    <div class="sk-toast sk-toast-danger"><?= htmlspecialchars($errorMsg) ?></div>
  <?php endif; ?>

  <!-- New Application Form -->
  <div class="sk-card" style="margin-bottom: 32px; padding: 24px;">
    <h3>Submit New Application</h3>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div style="margin-bottom:16px">
        <label class="sk-label">Opportunity *</label>
        <select name="opportunity_id" class="sk-select" required>
          <option value="">-- Choose an opportunity --</option>
          <?php foreach ($opportunities as $opp): ?>
            <?php if (($opp['Statut'] ?? '') === 'actif'): ?>
              <option value="<?= $opp['ID'] ?>" <?= (int)$opp['ID'] === $selectedOpportunityId ? 'selected' : '' ?>><?= htmlspecialchars($opp['Titre']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
        <?php if ($selectedOpportunityId > 0): ?>
          <div style="font-size:.8rem;color:var(--sk-accent);margin-top:6px;">Opportunity selected from AI search.</div>
        <?php endif; ?>
      </div>
      <div style="margin-bottom:16px">
        <label class="sk-label">Motivation *</label>
        <textarea name="motivation" class="sk-textarea" required></textarea>
      </div>
      <div style="margin-bottom:20px">
        <label class="sk-label">CV Link *</label>
        <input type="url" name="cv_link" class="sk-input" required>
      </div>
      <button type="submit" class="sk-btn sk-btn-primary">Submit Application</button>
    </form>
  </div>

  <!-- Table with View, Edit, Delete -->
  <div class="sk-card">
    <table class="sk-table">
      <thead>
        <tr>
          <th>Opportunity</th>
          <th>Status</th>
          <th>Date Applied</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($userApplications as $app): ?>
          <tr>
            <td style="font-weight:500"><?= htmlspecialchars($app['opportunity_title']) ?></td>
            <td><span class="sk-badge sk-badge-<?= strtolower($app['Statut'] ?? 'pending') ?>"><?= ucfirst($app['Statut'] ?? 'Pending') ?></span></td>
            <td><?= htmlspecialchars($app['DateCondidature'] ?? '') ?></td>
            <td>
              <button class="sk-btn sk-btn-sm sk-btn-ghost" onclick="viewApp(<?= $app['ID'] ?>, '<?= htmlspecialchars(addslashes($app['opportunity_title'])) ?>', '<?= htmlspecialchars(addslashes($app['motivation'])) ?>', '<?= htmlspecialchars(addslashes($app['CV'])) ?>')">View Application</button>
              <button class="sk-btn sk-btn-sm sk-btn-warn" onclick="editApp(<?= $app['ID'] ?>)">Edit</button>
              <form method="POST" style="display:inline" onsubmit="return confirm('Delete this application?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="app_id" value="<?= $app['ID'] ?>">
                <button type="submit" class="sk-btn sk-btn-sm sk-btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const appEditData = <?php echo json_encode($editData, JSON_UNESCAPED_UNICODE); ?>;

function viewApp(id, opportunity, motivation, cvLink) {
  document.getElementById('view_opportunity').textContent = opportunity;
  document.getElementById('view_motivation').textContent = motivation;
  const cvLinkElement = document.getElementById('view_cv_link');
  cvLinkElement.href = cvLink;
  cvLinkElement.textContent = cvLink;
  document.getElementById('viewModalOverlay').classList.add('open');
}

function closeViewModal() {
  document.getElementById('viewModalOverlay').classList.remove('open');
}

function openEditModal(id) {
  const payload = appEditData[id];
  if (!payload) {
    alert('Could not load application data for ID: ' + id);
    return;
  }

  document.getElementById('edit_app_id').value = id;
  document.getElementById('edit_motivation').value = payload.motivation ?? '';
  document.getElementById('edit_cv_link').value = payload.resource ?? '';

  document.getElementById('editModalOverlay').classList.add('open');
}

function closeEditModal() {
  document.getElementById('editModalOverlay').classList.remove('open');
}

function editApp(id) {
  openEditModal(id);
}

document.getElementById('editModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeEditModal();
});

document.getElementById('viewModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeViewModal();
});
</script>

<!-- ===== EDIT MODAL ===== -->
<div class="sk-modal-overlay" id="editModalOverlay" role="dialog" aria-modal="true">
  <div class="sk-modal">
    <div class="sk-modal-header">
      <div>
        <div class="sk-modal-title">Edit Application</div>
        <div class="text-muted" style="font-size:.85rem; margin-top:4px;">Update motivation and CV link</div>
      </div>
      <button type="button" class="sk-modal-close" onclick="closeEditModal()" aria-label="Close">&times;</button>
    </div>

    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="app_id" id="edit_app_id" value="">

      <div class="sk-modal-body">
        <div class="sk-form-group">
          <label class="sk-label" for="edit_motivation">Motivation *</label>
          <textarea id="edit_motivation" name="motivation" class="sk-textarea" required></textarea>
        </div>

        <div class="sk-form-group" style="margin-top:16px;">
          <label class="sk-label" for="edit_cv_link">CV Link *</label>
          <input type="url" id="edit_cv_link" name="cv_link" class="sk-input" required>
        </div>
      </div>

      <div class="sk-modal-footer">
        <button type="button" class="sk-btn sk-btn-ghost" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="sk-btn sk-btn-primary">Save changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== VIEW MODAL ===== -->
<div class="sk-modal-overlay" id="viewModalOverlay" role="dialog" aria-modal="true">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <div>
        <div class="sk-modal-title">Application Details</div>
      </div>
      <button type="button" class="sk-modal-close" onclick="closeViewModal()" aria-label="Close">&times;</button>
    </div>

    <div class="sk-modal-body">
      <div class="sk-form-group">
        <label class="sk-label">Opportunity</label>
        <p id="view_opportunity" style="margin: 0; color: var(--sk-text); font-weight: 500;"></p>
      </div>

      <div class="sk-form-group" style="margin-top:16px;">
        <label class="sk-label">Motivation</label>
        <p id="view_motivation" style="margin: 0; color: var(--sk-text); line-height: 1.5;"></p>
      </div>

      <div class="sk-form-group" style="margin-top:16px;">
        <label class="sk-label">CV Link</label>
        <a id="view_cv_link" href="#" target="_blank" style="color: var(--sk-accent); text-decoration: none;"></a>
      </div>
    </div>

    <div class="sk-modal-footer">
      <button type="button" class="sk-btn sk-btn-ghost" onclick="closeViewModal()">Close</button>
    </div>
  </div>
</div>
</body>
</html>

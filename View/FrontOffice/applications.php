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
$selectedOpportuniteId = (int)($_GET['opportunity_id'] ?? 0);

$successMsg = '';
$errorMsg = '';

// Traitement des formulaires
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
                $successMsg = 'Candidature soumise avec succes !';
            } catch (Exception $e) {
                $errorMsg = 'Echec de la creation : ' . $e->getMessage();
            }
        } else {
            $errorMsg = 'L opportunite, la motivation et le CV sont obligatoires.';
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
                    throw new Exception('Candidature introuvable ou non autorisee.');
                }

                $dateCondidatureRaw = $current['DateCondidature'] ?? $current['dateCondidature'] ?? null;
                $dateCondidature = $dateCondidatureRaw ? new DateTime($dateCondidatureRaw) : null;

                $application = new Application(
                    (int)($current['ID'] ?? $appId),
                    (int)($current['IDUtilisateur'] ?? $userId),
                    (int)($current['idOportunity'] ?? $current['idOportunitÃ©'] ?? 0),
                    $dateCondidature,
                    $current['Statut'] ?? $current['statut'] ?? 'pending',
                    $motivation,
                    $cvLink
                );

                $applicationCtrl->updateApplication($application, $appId);
                $successMsg = 'Candidature mise a jour avec succes !';
            } catch (Exception $e) {
                $errorMsg = 'Echec de la mise a jour : ' . $e->getMessage();
            }
        } else {
            $errorMsg = 'Motivation et CV sont obligatoires.';
        }
    } elseif ($action === 'delete') {
        $appId = (int)($_POST['app_id'] ?? 0);
        if ($appId > 0) {
            try {
                $db = config::getConnexion();
                $check = $db->prepare("SELECT ID FROM application WHERE ID = :id AND IDUtilisateur = :uid");
                $check->execute([':id' => $appId, ':uid' => $userId]);
                if (!$check->fetch()) {
                    throw new Exception('Candidature introuvable ou non autorisee.');
                }
                $applicationCtrl->deleteApplication($appId);
                $successMsg = 'Candidature supprimee avec succes !';
            } catch (Exception $e) {
                $errorMsg = 'Echec de la suppression : ' . $e->getMessage();
            }
        }
    }
}

// Recuperer les donnees
$db = config::getConnexion();
$query = $db->prepare("
    SELECT a.*, o.Titre as opportunity_title, o.Type_job 
    FROM application a
    JOIN oportunity o ON a.idOportunity = o.ID
    WHERE a.IDUtilisateur = :userId
    ORDER BY a.DateCondidature DESC
");
$query->execute([':userId' => $userId]);
$userCandidatures = $query->fetchAll(PDO::FETCH_ASSOC);

$opportunities = $oportunityCtrl->listOportunities()->fetchAll(PDO::FETCH_ASSOC);

$editData = [];
foreach ($userCandidatures as $app) {
    $editData[(int)($app['ID'] ?? 0)] = [
        'motivation' => $app['motivation'] ?? $app['Motivation'] ?? '',
        'resource' => $app['CV'] ?? $app['resource'] ?? $app['Resource'] ?? '',
    ];
}

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
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes candidatures - Skiller</title>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      Mes candidatures
      <small>Creer, consulter, modifier et supprimer</small>
    </div>
  </div>

  <?php if ($successMsg): ?>
    <div class="sk-toast sk-toast-success"><?= htmlspecialchars($successMsg) ?></div>
  <?php endif; ?>
  <?php if ($errorMsg): ?>
    <div class="sk-toast sk-toast-danger"><?= htmlspecialchars($errorMsg) ?></div>
  <?php endif; ?>

  <!-- Nouvelle candidature -->
  <div class="sk-card" style="margin-bottom: 32px; padding: 24px;">
    <h3>Soumettre une nouvelle candidature</h3>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div style="margin-bottom:16px">
        <label class="sk-label">Opportunite *</label>
        <select name="opportunity_id" class="sk-select" required>
          <option value="">-- Choisir une opportunite --</option>
          <?php foreach ($opportunities as $opp): ?>
            <?php if (($opp['Statut'] ?? '') === 'actif'): ?>
              <option value="<?= $opp['ID'] ?>" <?= (int)$opp['ID'] === $selectedOpportuniteId ? 'selected' : '' ?>><?= htmlspecialchars($opp['Titre']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
        <?php if ($selectedOpportuniteId > 0): ?>
          <div style="font-size:.8rem;color:var(--sk-accent);margin-top:6px;">Opportunite selectionnee depuis la recherche IA.</div>
        <?php endif; ?>
      </div>
      <div style="margin-bottom:16px">
        <label class="sk-label">Motivation *</label>
        <textarea name="motivation" class="sk-textarea" required></textarea>
      </div>
      <div style="margin-bottom:20px">
        <label class="sk-label">Lien du CV *</label>
        <input type="url" name="cv_link" class="sk-input" required>
      </div>
      <button type="submit" class="sk-btn sk-btn-primary">Soumettre la candidature</button>
    </form>
  </div>

  <!-- Tableau des candidatures -->
  <div class="sk-card">
    <table class="sk-table">
      <thead>
        <tr>
          <th>Opportunite</th>
          <th>Statut</th>
          <th>Date de candidature</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($userCandidatures as $app): ?>
          <tr>
            <td style="font-weight:500"><?= htmlspecialchars($app['opportunity_title']) ?></td>
            <td><span class="sk-badge sk-badge-<?= strtolower($app['Statut'] ?? 'pending') ?>"><?= htmlspecialchars(applicationStatusLabel($app['Statut'] ?? 'pending')) ?></span></td>
            <td><?= htmlspecialchars($app['DateCondidature'] ?? '') ?></td>
            <td>
              <button class="sk-btn sk-btn-sm sk-btn-ghost" onclick="viewApp(<?= $app['ID'] ?>, '<?= htmlspecialchars(addslashes($app['opportunity_title'])) ?>', '<?= htmlspecialchars(addslashes($app['motivation'])) ?>', '<?= htmlspecialchars(addslashes($app['CV'])) ?>')">Voir la candidature</button>
              <button class="sk-btn sk-btn-sm sk-btn-warn" onclick="editApp(<?= $app['ID'] ?>)">Modifier</button>
              <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette candidature ?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="app_id" value="<?= $app['ID'] ?>">
                <button type="submit" class="sk-btn sk-btn-sm sk-btn-danger">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const appModifierData = <?php echo json_encode($editData, JSON_UNESCAPED_UNICODE); ?>;

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

function openModifierModal(id) {
  const payload = appModifierData[id];
  if (!payload) {
    alert('Impossible de charger les donnees de la candidature ID : ' + id);
    return;
  }

  document.getElementById('edit_app_id').value = id;
  document.getElementById('edit_motivation').value = payload.motivation ?? '';
  document.getElementById('edit_cv_link').value = payload.resource ?? '';

  document.getElementById('editModalOverlay').classList.add('open');
}

function closeModifierModal() {
  document.getElementById('editModalOverlay').classList.remove('open');
}

function editApp(id) {
  openModifierModal(id);
}

document.getElementById('editModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeModifierModal();
});

document.getElementById('viewModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeViewModal();
});
</script>

<!-- ===== MODALE DE MODIFICATION ===== -->
<div class="sk-modal-overlay" id="editModalOverlay" role="dialog" aria-modal="true">
  <div class="sk-modal">
    <div class="sk-modal-header">
      <div>
        <div class="sk-modal-title">Modifier la candidature</div>
        <div class="text-muted" style="font-size:.85rem; margin-top:4px;">Mettre a jour la motivation et le lien du CV</div>
      </div>
      <button type="button" class="sk-modal-close" onclick="closeModifierModal()" aria-label="Fermer">&times;</button>
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
          <label class="sk-label" for="edit_cv_link">Lien du CV *</label>
          <input type="url" id="edit_cv_link" name="cv_link" class="sk-input" required>
        </div>
      </div>

      <div class="sk-modal-footer">
        <button type="button" class="sk-btn sk-btn-ghost" onclick="closeModifierModal()">Annuler</button>
        <button type="submit" class="sk-btn sk-btn-primary">Enregistrer les modifications</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODALE DE CONSULTATION ===== -->
<div class="sk-modal-overlay" id="viewModalOverlay" role="dialog" aria-modal="true">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <div>
        <div class="sk-modal-title">Details de la candidature</div>
      </div>
      <button type="button" class="sk-modal-close" onclick="closeViewModal()" aria-label="Fermer">&times;</button>
    </div>

    <div class="sk-modal-body">
      <div class="sk-form-group">
        <label class="sk-label">Opportunite</label>
        <p id="view_opportunity" style="margin: 0; color: var(--sk-text); font-weight: 500;"></p>
      </div>

      <div class="sk-form-group" style="margin-top:16px;">
        <label class="sk-label">Motivation</label>
        <p id="view_motivation" style="margin: 0; color: var(--sk-text); line-height: 1.5;"></p>
      </div>

      <div class="sk-form-group" style="margin-top:16px;">
        <label class="sk-label">Lien du CV</label>
        <a id="view_cv_link" href="#" target="_blank" style="color: var(--sk-accent); text-decoration: none;"></a>
      </div>
    </div>

    <div class="sk-modal-footer">
      <button type="button" class="sk-btn sk-btn-ghost" onclick="closeViewModal()">Fermer</button>
    </div>
  </div>
</div>
</body>
</html>



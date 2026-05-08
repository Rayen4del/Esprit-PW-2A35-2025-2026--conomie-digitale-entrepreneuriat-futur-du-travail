<?php
include_once(__DIR__ . '/../../../../config.php');
include_once(__DIR__ . '/../../../../controller/evenement/EvenementController.php');

$controller = new EvenementController();
$successMessage = '';
$errorMessage = '';

// ── Handle AJAX requests ───────────────────────────────────────────
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $search = trim($_GET['search'] ?? '');
    $statut = trim($_GET['statut'] ?? '');
    $type   = trim($_GET['type']   ?? '');
    $registrations = $controller->filtrerRegistrations($search, $statut, $type);
    $html   = renderRegistrations($registrations);
    header('Content-Type: application/json');
    echo json_encode(['html' => $html, 'count' => count($registrations), 'registrations' => $registrations]);
    exit;
}

$events = $controller->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $idUtilisateur = intval($_POST['idUtilisateur'] ?? 0);
        $idEvent = intval($_POST['idEvent'] ?? 0);
        $statut = trim($_POST['statut'] ?? 'inscrit');
        $allowedStatuts = ['inscrit', 'annulé'];

        if ($id <= 0 || $idUtilisateur <= 0 || $idEvent <= 0) {
            $errorMessage = "Données invalides pour la modification.";
        } elseif (!in_array($statut, $allowedStatuts, true)) {
            $errorMessage = "Statut invalide.";
        } elseif (!$controller->getById($idEvent)) {
            $errorMessage = "ID Event introuvable. Choisissez un événement existant.";
        } elseif ($controller->updateRegistration($id, $idUtilisateur, $idEvent, $statut)) {
            $successMessage = "Inscription modifiée avec succès.";
        } else {
            $errorMessage = "Erreur SQL: " . $controller->getLastError();
        }
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            $errorMessage = "ID inscription invalide.";
        } elseif ($controller->deleteRegistration($id)) {
            $successMessage = "Inscription supprimée avec succès.";
        } else {
            $errorMessage = "Erreur SQL: " . $controller->getLastError();
        }
    }
}

// ── Load registrations (after any action) ─────────────────────────
$search    = trim($_GET['search'] ?? '');
$statut    = trim($_GET['statut'] ?? '');
$type      = trim($_GET['type']   ?? '');
$registrations = $controller->filtrerRegistrations($search, $statut, $type);

$searchVal = htmlspecialchars($search);
$statutVal = htmlspecialchars($statut);
$typeVal   = htmlspecialchars($type);

function renderRegistrations($registrations) {
    ob_start();
    if (empty($registrations)): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>ID Utilisateur</th>
                <th>Événement</th>
                <th>ID Event</th>
                <th>Date inscription</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="7" class="text-center py-4 text-muted">Aucune inscription trouvée.</td></tr>
            </tbody>
          </table>
        </div>
    <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>ID Utilisateur</th>
                <th>Événement</th>
                <th>ID Event</th>
                <th>Date inscription</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($registrations as $row): ?>
              <?php
                $statusClass = match($row['Statut']) {
                  'inscrit', 'confirmé' => 'bg-success-subtle text-success',
                  'annulé' => 'bg-danger-subtle text-danger',
                  default => 'bg-warning-subtle text-warning'
                };
              ?>
              <tr>
                <td><?= intval($row['ID']) ?></td>
                <td><?= intval($row['IDUtilisateur']) ?></td>
                <td><?= htmlspecialchars($row['Titre']) ?></td>
                <td><?= intval($row['IDEvent']) ?></td>
                <td><?= htmlspecialchars($row['DateInscription']) ?></td>
                <td><span class="badge-status <?= $statusClass ?>"><?= htmlspecialchars($row['Statut']) ?></span></td>
                <td>
                  <div class="action-buttons">
                    <button type="button"
                            class="btn btn-sm btn-outline-primary edit-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editRegistrationModal"
                            data-id="<?= intval($row['ID']) ?>"
                            data-idutilisateur="<?= intval($row['IDUtilisateur']) ?>"
                            data-idevent="<?= intval($row['IDEvent']) ?>"
                            data-statut="<?= htmlspecialchars($row['Statut'], ENT_QUOTES) ?>">
                      <i class="bi bi-pencil-square"></i>
                    </button>

                    <form method="POST" action="" onsubmit="return confirm('Supprimer cette inscription ?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= intval($row['ID']) ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
    <?php endif;
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Liste des inscriptions</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    :root { --bs-primary:#696cff; --menu-bg:#fff; --menu-text:#566a7f; --menu-active-bg:#f1f1ff; --menu-active-text:#696cff; --body-bg:#f5f5f9; --navbar-bg:#fff; --sidebar-width:260px; --header-height:64px; }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Public Sans',sans-serif;font-size:.9375rem;background:var(--body-bg);color:#566a7f}
    .layout-wrapper{display:flex;min-height:100vh}
    .layout-container{display:flex;flex:1}
    .layout-menu{width:var(--sidebar-width);background:var(--menu-bg);flex-shrink:0;display:flex;flex-direction:column;box-shadow:0 0 0 1px rgba(67,89,113,.05),0 2px 6px rgba(67,89,113,.12);position:fixed;top:0;left:0;bottom:0;z-index:1100;overflow-y:auto}
    .app-brand{display:flex;align-items:center;padding:1.5rem 1.5rem .5rem;min-height:var(--header-height)}
    .app-brand-text{font-size:1.25rem;font-weight:700;color:#566a7f;margin-left:.6rem}
    .menu-inner{list-style:none;padding:.5rem 0 1rem;flex:1}
    .menu-header{padding:.75rem 1.5rem .25rem;font-size:.6875rem;font-weight:600;color:#a1acb8;letter-spacing:.8px;text-transform:uppercase}
    .menu-link{display:flex;align-items:center;padding:.625rem 1.5rem;color:var(--menu-text);text-decoration:none;border-radius:.375rem;margin:.1rem .75rem;transition:background .15s,color .15s;font-size:.9rem}
    .menu-link:hover,.menu-link.active{background:var(--menu-active-bg);color:var(--menu-active-text)}
    .menu-icon{font-size:1.1rem;margin-right:.75rem;opacity:.85}
    .layout-page{margin-left:var(--sidebar-width);display:flex;flex-direction:column;flex:1;min-width:0}
    .layout-navbar{background:var(--navbar-bg);height:var(--header-height);display:flex;align-items:center;padding:0 1.5rem;box-shadow:0 1px 0 rgba(67,89,113,.1);position:sticky;top:0;z-index:1000;gap:1rem}
    .navbar-search{flex:1;max-width:300px}
    .navbar-search .input-group{background:var(--body-bg);border-radius:.375rem}
    .navbar-search input{background:transparent;border:none;font-size:.875rem;color:#566a7f}
    .navbar-search input:focus{box-shadow:none;outline:none}
    .navbar-search .input-group-text{background:transparent;border:none;color:#a1acb8}
    .navbar-nav-right{display:flex;align-items:center;gap:.25rem;margin-left:auto}
    .nav-icon-btn{background:none;border:none;color:#566a7f;font-size:1.2rem;padding:.5rem;border-radius:.375rem;cursor:pointer;position:relative;transition:background .15s,color .15s}
    .nav-icon-btn:hover{background:var(--body-bg);color:var(--bs-primary)}
    .nav-badge{position:absolute;top:6px;right:6px;width:8px;height:8px;background:#ff3e1d;border-radius:50%;border:2px solid #fff}
    .content-wrapper{flex:1;padding:1.5rem}
    .page-title{font-size:1.125rem;font-weight:700;color:#566a7f;margin-bottom:1.5rem}
    .page-title span{color:#a1acb8;font-weight:400}
    .card{background:#fff;border:none;border-radius:.5rem;box-shadow:0 2px 6px rgba(67,89,113,.12)}
    .card-header{background:transparent;border-bottom:1px solid rgba(67,89,113,.08);padding:1rem 1.5rem;display:flex;align-items:center;gap:.75rem}
    .card-header-icon{width:36px;height:36px;border-radius:.375rem;background:linear-gradient(135deg,#696cff,#a3a4ff);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem}
    .card-header-title{font-size:1rem;font-weight:600;color:#566a7f}
    .badge-status{font-size:.72rem;padding:.25rem .5rem;border-radius:999px}
    .action-buttons{display:flex;gap:.5rem}
    .user-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#696cff,#a3a4ff);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;margin-left:auto;position:relative}
    .online-dot{position:absolute;bottom:1px;right:1px;width:9px;height:9px;background:#71dd37;border-radius:50%;border:2px solid #fff}
    .content-footer{padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;font-size:.8125rem;color:#a1acb8;border-top:1px solid rgba(67,89,113,.08)}
    .footer-link{color:#a1acb8;text-decoration:none;margin-left:1rem}
    .footer-link:hover{color:var(--bs-primary)}
  </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">
    <aside class="layout-menu">
      <div class="app-brand">
        <div class="app-brand-logo">
          <svg viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.5 2L22 7V18L12.5 23L3 18V7L12.5 2Z" fill="#696cff" fill-opacity=".15" stroke="#696cff" stroke-width="1.5"/>
            <path d="M12.5 7L17 9.5V14.5L12.5 17L8 14.5V9.5L12.5 7Z" fill="#696cff"/>
          </svg>
        </div>
        <span class="app-brand-text">EventHub</span>
      </div>
      <ul class="menu-inner">
        <li class="menu-header">Utilisateur</li>
        <li><a href="/projet/view/evenement/html/frontoffice/user_events.php" class="menu-link"><i class="bi bi-calendar-event menu-icon"></i> Événements</a></li>
        <li><a href="/projet/view/evenement/html/frontoffice/liste_inscriptions.php" class="menu-link active"><i class="bi bi-card-checklist menu-icon"></i> Inscriptions</a></li>
      </ul>
    </aside>

    <div class="layout-page">
      <nav class="layout-navbar">
        <div class="navbar-search">
          <form method="GET" action="/projet/view/evenement/html/frontoffice/liste_inscriptions.php" id="searchForm">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" name="search" id="searchInput"
                     placeholder="Rechercher une inscription..."
                     value="<?= $searchVal ?>" />
              <input type="hidden" name="statut" value="<?= $statutVal ?>">
              <input type="hidden" name="type"   value="<?= $typeVal ?>">
            </div>
          </form>
        </div>
        <div class="navbar-nav-right">
          <button class="nav-icon-btn"><i class="bi bi-bell"></i><span class="nav-badge"></span></button>
          <button class="nav-icon-btn"><i class="bi bi-chat-dots"></i></button>
          <div class="user-avatar">US<span class="online-dot"></span></div>
        </div>
      </nav>

      <div class="content-wrapper">
        <p class="page-title">Inscriptions <span>/ Tous les enregistrements</span></p>

        <?php if ($successMessage): ?>
        <div class="alert alert-success d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <div><?= htmlspecialchars($successMessage) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-circle-fill"></i>
          <div><?= htmlspecialchars($errorMessage) ?></div>
        </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="card mb-4">
          <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-funnel"></i></div>
            <span class="card-header-title">Filtres</span>
            <span class="badge bg-primary ms-auto"><?= count($registrations) ?> enregistrement(s)</span>
          </div>
          <div class="card-body">
            <form method="GET" action="/projet/view/evenement/html/frontoffice/liste_inscriptions.php" id="filterForm">
              <input type="hidden" name="search" value="<?= $searchVal ?>">
              <div class="row g-3">
                <div class="col-md-4">
                  <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="inscrit" <?= $statutVal==='inscrit' ? 'selected':'' ?>>Inscrit</option>
                    <option value="annulé" <?= $statutVal==='annulé' ? 'selected':'' ?>>Annulé</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="workshop"   <?= $typeVal==='workshop'   ? 'selected':'' ?>>Workshop</option>
                    <option value="conference" <?= $typeVal==='conference' ? 'selected':'' ?>>Conférence</option>
                    <option value="seminaire"  <?= $typeVal==='seminaire'  ? 'selected':'' ?>>Séminaire</option>
                    <option value="hackathon"  <?= $typeVal==='hackathon'  ? 'selected':'' ?>>Hackathon</option>
                    <option value="formation"  <?= $typeVal==='formation'  ? 'selected':'' ?>>Formation</option>
                    <option value="webinar"    <?= $typeVal==='webinar'    ? 'selected':'' ?>>Webinaire</option>
                    <option value="autre"      <?= $typeVal==='autre'      ? 'selected':'' ?>>Autre</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <a href="/projet/view/evenement/html/frontoffice/liste_inscriptions.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-repeat me-1"></i>Réinitialiser
                  </a>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-card-checklist"></i></div>
            <span class="card-header-title">Gestion des inscriptions</span>
          </div>
          <div class="card-body p-0">
            <div id="registrationsContainer">
              <?php echo renderRegistrations($registrations); ?>
            </div>
          </div>
        </div>
      </div>
      <footer class="content-footer">
        <div>© <span id="year"></span> EventHub — Plateforme d'événements</div>
        <div>
          <a href="#" class="footer-link">Aide</a>
          <a href="#" class="footer-link">Mentions légales</a>
          <a href="#" class="footer-link">Support</a>
        </div>
      </footer>
    </div>
  </div>
</div>

<div class="modal fade" id="editRegistrationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="edit-id">
        <div class="modal-header">
          <h5 class="modal-title">Modifier inscription</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="edit-idUtilisateur">ID Utilisateur</label>
            <input type="number" class="form-control" id="edit-idUtilisateur" name="idUtilisateur" min="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="edit-idEvent">ID Event</label>
            <select class="form-select" id="edit-idEvent" name="idEvent" required>
              <option value="" disabled selected>-- Choisir un événement --</option>
              <?php foreach ($events as $ev): ?>
              <option value="<?= intval($ev['ID']) ?>">
                #<?= intval($ev['ID']) ?> - <?= htmlspecialchars($ev['Titre']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-0">
            <label class="form-label" for="edit-statut">Statut</label>
            <select class="form-select" id="edit-statut" name="statut" required>
              <option value="inscrit">Inscrit</option>
              <option value="annulé">Annulé</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.edit-btn').forEach(button => {
  button.addEventListener('click', () => {
    document.getElementById('edit-id').value = button.getAttribute('data-id');
    document.getElementById('edit-idUtilisateur').value = button.getAttribute('data-idutilisateur');
    document.getElementById('edit-idEvent').value = button.getAttribute('data-idevent');
    document.getElementById('edit-statut').value = button.getAttribute('data-statut');
  });
});
document.getElementById('year').textContent = new Date().getFullYear();

// AJAX search and filter
function updateResults() {
    const search = document.getElementById('searchInput').value;
    const statut = document.querySelector('select[name="statut"]').value;
    const type = document.querySelector('select[name="type"]').value;
    fetch(window.location.href + '?ajax=1&search=' + encodeURIComponent(search) + '&statut=' + encodeURIComponent(statut) + '&type=' + encodeURIComponent(type), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.querySelector('.badge.bg-primary').textContent = data.count + ' enregistrement(s)';
        document.getElementById('registrationsContainer').innerHTML = data.html;
        // Re-bind edit buttons
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('edit-id').value = button.getAttribute('data-id');
                document.getElementById('edit-idUtilisateur').value = button.getAttribute('data-idutilisateur');
                document.getElementById('edit-idEvent').value = button.getAttribute('data-idevent');
                document.getElementById('edit-statut').value = button.getAttribute('data-statut');
            });
        });
    })
    .catch(error => console.error('Error:', error));
}

document.getElementById('searchInput').addEventListener('input', updateResults);
document.querySelector('select[name="statut"]').addEventListener('change', updateResults);
document.querySelector('select[name="type"]').addEventListener('change', updateResults);
</script>
</body>
</html>

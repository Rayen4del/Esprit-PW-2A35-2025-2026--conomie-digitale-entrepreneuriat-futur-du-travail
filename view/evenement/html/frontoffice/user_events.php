<?php
include_once(__DIR__ . '/../../../../config.php');
include_once(__DIR__ . '/../../../../controller/evenement/EvenementController.php');

$controller = new EvenementController();

// ── Handle AJAX requests ───────────────────────────────────────────
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $search = trim($_GET['search'] ?? '');
    $type   = trim($_GET['type']   ?? '');
    $page   = max(1, intval($_GET['page'] ?? 1));
    $perPage = 6;
    $totalCount = $controller->countFiltered($search, '', $type);
    $totalPages = max(1, (int) ceil($totalCount / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $events = $controller->filtrer($search, '', $type, $perPage, $offset);
    $html   = renderEvents($events);
    $paginationHtml = renderPagination($page, $totalPages, $search, $type);
    header('Content-Type: application/json');
    echo json_encode(['html' => $html, 'count' => count($events), 'paginationHtml' => $paginationHtml]);
    exit;
}

$successMessage = '';
$errorMessage   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_event'])) {
    $idUtilisateur = intval($_POST['idUtilisateur'] ?? 0);
    $idEvent       = intval($_POST['idEvent'] ?? 0);
    $statut        = trim($_POST['statut'] ?? 'inscrit');
    $allowedStatuts = ['inscrit', 'annulé'];

    if (!in_array($statut, $allowedStatuts, true)) {
        $statut = 'inscrit';
    }

    if ($idUtilisateur <= 0) {
        $errorMessage = "ID utilisateur invalide.";
    } elseif ($idEvent <= 0) {
        $errorMessage = "Événement invalide.";
    } else {
        $event = $controller->getById($idEvent);

        if (!$event) {
            $errorMessage = "Événement introuvable.";
        } elseif ($controller->isAlreadyRegistered($idUtilisateur, $idEvent)) {
            $errorMessage = "Vous êtes déjà inscrit à cet événement.";
        } elseif ($controller->inscrire($idUtilisateur, $idEvent, $statut)) {
            $successMessage = "Inscription confirmée avec succès.";
        } else {
            $dbError = $controller->getLastError();
            $errorMessage = "Erreur SQL: " . ($dbError !== '' ? $dbError : "inconnue");
        }
    }
}

// ── Load events (after any action) ─────────────────────────
$search = trim($_GET['search'] ?? '');
$type   = trim($_GET['type']   ?? '');
$currentPage = max(1, intval($_GET['page'] ?? 1));
$perPage = 6;
$totalCount = $controller->countFiltered($search, '', $type);
$totalPages = max(1, (int) ceil($totalCount / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;
$events = $controller->filtrer($search, '', $type, $perPage, $offset);
$pagination = renderPagination($currentPage, $totalPages, $search, $type);

$searchVal = htmlspecialchars($search);
$typeVal   = htmlspecialchars($type);

function renderEvents($events) {
    ob_start();
    if (empty($events)): ?>
        <div class="text-center py-5">
          <i class="bi bi-inbox" style="font-size:3rem;color:#a1acb8"></i>
          <p class="mt-3">Aucun événement disponible pour le moment</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
          <?php foreach ($events as $ev):
            $eventStatus = strtolower(trim($ev['Statut'] ?? ''));
            $isOpen = ($eventStatus === 'ouvert') && intval($ev['places_restantes'] ?? 0) > 0;
            $statusClass = match($eventStatus) {
              'ouvert' => 'bg-success', 'ferme' => 'bg-danger', 'complet' => 'bg-warning', default => 'bg-secondary'
            };
            $typeLabels = ['workshop'=>'Workshop','conference'=>'Conférence','seminaire'=>'Séminaire','hackathon'=>'Hackathon','formation'=>'Formation','webinar'=>'Webinaire','autre'=>'Autre'];
          ?>
          <div class="col">
            <div class="card event-card h-100 <?= !$isOpen ? 'opacity-75' : '' ?>"
                 role="button"
                 style="<?= $isOpen ? 'cursor:pointer' : 'cursor:not-allowed' ?>"
                 <?= $isOpen ? 'data-bs-toggle="modal" data-bs-target="#joinEventModal"' : '' ?>
                 data-event-id="<?= intval($ev['ID']) ?>"
                 data-event-title="<?= htmlspecialchars($ev['Titre'], ENT_QUOTES) ?>"
                 data-event-status="<?= htmlspecialchars($eventStatus, ENT_QUOTES) ?>">
              <div class="event-image-placeholder">

                <i class="bi bi-calendar-event"></i>
              </div>
              <div style="position:relative;margin-top:-2rem;padding:0 .75rem">
                <span class="status-badge <?= $statusClass ?>"><?= strtoupper($ev['Statut']) ?></span>
              </div>
              <div class="card-body pt-2">
                <small class="text-muted"><?= $typeLabels[$ev['Type']] ?? ucfirst($ev['Type']) ?></small>
                <h5 class="mt-1 mb-2"><?= htmlspecialchars($ev['Titre']) ?></h5>
                <p class="mb-1 small"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($ev['dateEvent'])) ?></p>
                <p class="mb-1 small"><i class="bi bi-people me-1"></i><?= intval($ev['inscrits_count'] ?? 0) ?> / <?= intval($ev['nbplaces']) ?> inscrits</p>
                <p class="mb-3 small <?= intval($ev['places_restantes'] ?? 0) <= 0 ? 'text-danger' : 'text-success' ?>">
                  <i class="bi bi-door-open me-1"></i><?= intval($ev['places_restantes'] ?? 0) ?> places restantes
                </p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
    <?php endif;
    return ob_get_clean();
}

function renderPagination($currentPage, $totalPages, $search, $type) {
    if ($totalPages <= 1) {
        return '';
    }

    $baseUrl = '/projet/view/evenement/html/frontoffice/user_events.php';
    $html = '<nav aria-label="Navigation des pages"><ul class="pagination justify-content-center mt-4">';

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $currentPage ? ' active' : '';
        $query = http_build_query(['search' => $search, 'type' => $type, 'page' => $i]);
        $html .= '<li class="page-item' . $active . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($baseUrl . '?' . $query) . '" onclick="goToPage(' . $i . '); return false;">' . $i . '</a>';
        $html .= '</li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Événements - Inscription</title>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  
  <style>
    :root{--bs-primary:#696cff;--menu-bg:#fff;--menu-text:#566a7f;--menu-active-bg:#f1f1ff;
      --menu-active-text:#696cff;--body-bg:#f5f5f9;--navbar-bg:#fff;
      --sidebar-width:260px;--header-height:64px}
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Public Sans',sans-serif;font-size:.9375rem;background:var(--body-bg);color:#566a7f}
    .layout-wrapper{display:flex;min-height:100vh}
    .layout-container{display:flex;flex:1}
    .layout-menu{width:var(--sidebar-width);background:var(--menu-bg);flex-shrink:0;position:fixed;top:0;left:0;bottom:0;z-index:1100;overflow-y:auto;box-shadow:0 0 0 1px rgba(67,89,113,.05),0 2px 6px rgba(67,89,113,.12)}
    .app-brand{display:flex;align-items:center;padding:1.5rem 1.5rem .5rem;min-height:var(--header-height)}
    .app-brand-text{font-size:1.25rem;font-weight:700;color:#566a7f;margin-left:.6rem}
    .menu-inner{list-style:none;padding:.5rem 0 1rem;flex:1}
    .menu-header{padding:.75rem 1.5rem .25rem;font-size:.6875rem;font-weight:600;color:#a1acb8;letter-spacing:.8px;text-transform:uppercase}
    .menu-item{position:relative}
    .menu-link{display:flex;align-items:center;padding:.625rem 1.5rem;color:var(--menu-text);text-decoration:none;border-radius:.375rem;margin:.1rem .75rem;transition:background .15s,color .15s;font-size:.9rem;cursor:pointer}
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
    .page-title{font-size:1.125rem;font-weight:700;color:#566a7f;margin-bottom:1rem}
    .page-title span{color:#a1acb8;font-weight:400}
    .user-actions{display:flex;justify-content:flex-end;margin-bottom:1rem}
    .card{background:#fff;border:none;border-radius:.5rem;box-shadow:0 2px 6px rgba(67,89,113,.12);transition:transform .2s,box-shadow .2s}
    .event-card:hover{transform:translateY(-4px);box-shadow:0 8px 24px rgba(67,89,113,.18)}
    .event-image-placeholder{height:160px;background:linear-gradient(135deg,#696cff22,#a3a4ff44);display:flex;align-items:center;justify-content:center;font-size:3rem;color:#696cff}
    .status-badge{position:absolute;top:.75rem;right:.75rem;padding:.25rem .65rem;border-radius:999px;font-size:.7rem;font-weight:700;color:#fff}
    .content-footer{padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;font-size:.8125rem;color:#a1acb8;border-top:1px solid rgba(67,89,113,.08)}
    .footer-link{color:#a1acb8;text-decoration:none;margin-left:1rem}
    .footer-link:hover{color:var(--bs-primary)}
  </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- SIDEBAR -->
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
        <li class="menu-item">
          <a href="/projet/view/evenement/html/frontoffice/user_events.php" class="menu-link active">
            <i class="bi bi-calendar-event menu-icon"></i> Événements
          </a>
        </li>
        <li class="menu-item">
          <a href="/projet/view/evenement/html/frontoffice/liste_inscriptions.php" class="menu-link">
            <i class="bi bi-card-checklist menu-icon"></i> Inscriptions
          </a>
        </li>
      </ul>
    </aside>

    <div class="layout-page">
      <nav class="layout-navbar">
        <div class="navbar-search">
          <form method="GET" action="/projet/view/evenement/html/user_events.php" id="searchForm">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" name="search" id="searchInput"
                     placeholder="Rechercher un événement..."
                     value="<?= $searchVal ?>" />
              <input type="hidden" name="type" value="<?= $typeVal ?>">
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
        <p class="page-title">Événements <span>/ Inscription rapide</span></p>
        <div class="user-actions">
          <a href="/projet/view/evenement/html/frontoffice/liste_inscriptions.php" class="btn btn-outline-primary">
            <i class="bi bi-list-ul me-1"></i> Liste des inscriptions
          </a>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
          <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-funnel"></i></div>
            <span class="card-header-title">Filtres</span>
            <span class="badge bg-primary ms-auto"><?= count($events) ?> événement(s)</span>
          </div>
          <div class="card-body">
            <form method="GET" action="/projet/view/evenement/html/frontoffice/user_events.php" id="filterForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <input type="text" id="searchInput" name="search" class="form-control"
                    placeholder="Rechercher un événement"
                    value="<?= $searchVal ?>">
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
                <div class="col-md-2 d-grid">
                  <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='/projet/view/evenement/html/frontoffice/user_events.php'">
                    <i class="bi bi-arrow-repeat me-1"></i>Réinitialiser
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

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

        <div id="eventsContainer">
          <?php echo renderEvents($events); ?>
        </div>
        <div id="paginationContainer"><?= $pagination ?></div>

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

<div class="modal fade" id="joinEventModal" tabindex="-1" aria-labelledby="joinEventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
        <input type="hidden" name="join_event" value="1">
        <div class="modal-header">
          <h5 class="modal-title" id="joinEventModalLabel">Inscription à l'événement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-3" id="selectedEventTitle">Choisissez un événement</p>

          <div class="mb-3">
            <label class="form-label" for="idUtilisateur">ID Utilisateur</label>
            <input type="number" class="form-control" id="idUtilisateur" name="idUtilisateur" min="1" required>
          </div>

          <div class="mb-3">
            <label class="form-label" for="idEvent">ID Événement</label>
            <input type="number" class="form-control" id="idEvent" name="idEvent" readonly required>
          </div>

          <div class="mb-3">
            <label class="form-label" for="dateInscription">Date d'inscription</label>
            <input type="text" class="form-control" id="dateInscription" value="<?= date('Y-m-d') ?>" readonly>
          </div>

          <div class="mb-0">
            <label class="form-label" for="statut">Statut</label>
            <select class="form-select" id="statut" name="statut" required>
              <option value="inscrit" selected>Inscrit</option>
              <option value="annulé">Annulé</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Confirmer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Show tooltip on non-open events
document.querySelectorAll('.event-card').forEach(card => {
  if (card.dataset.eventStatus !== 'ouvert') {
    card.title = "Cet événement n\'est pas disponible pour l\'inscription.";
  }
});

function bindModalEvents() {
    const joinEventModal = document.getElementById('joinEventModal');
    const modal = new bootstrap.Modal(joinEventModal);

    document.querySelectorAll('.event-card').forEach(card => {
        const eventStatus = (card.dataset.eventStatus || '').trim().toLowerCase();
        if (eventStatus !== 'ouvert') {
            card.title = "Cet événement n'est pas disponible pour l'inscription.";
            return;
        }

        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            const eventId = card.dataset.eventId;
            const eventTitle = card.dataset.eventTitle;
            document.getElementById('idEvent').value = eventId;
            document.getElementById('selectedEventTitle').textContent = eventTitle
                ? `Événement sélectionné : ${eventTitle}`
                : 'Événement sélectionné';
            modal.show();
        });
    });
}

bindModalEvents();

document.getElementById('year').textContent = new Date().getFullYear();

function goToPage(page) {
    updateResults(page);
}

// AJAX search and filter
function updateResults(page = 1) {
    const searchInput = document.getElementById('searchInput');
    const search = searchInput ? searchInput.value : '';
    const type = document.querySelector('select[name="type"]').value;
    fetch(window.location.pathname + '?ajax=1&search=' + encodeURIComponent(search) + '&type=' + encodeURIComponent(type) + '&page=' + page, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.querySelector('.badge.bg-primary').textContent = data.count + ' événement(s)';
        document.getElementById('eventsContainer').innerHTML = data.html;
        document.getElementById('paginationContainer').innerHTML = data.paginationHtml || '';
        // Re-bind tooltips and modal events
        document.querySelectorAll('.event-card').forEach(card => {
            if (card.dataset.eventStatus !== 'ouvert') {
                card.title = "Cet événement n\'est pas disponible pour l\'inscription.";
            }
        });
        bindModalEvents();
    })
    .catch(error => console.error('Error:', error));
}

const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', () => updateResults(1));
}
document.querySelector('select[name="type"]').addEventListener('change', () => updateResults(1));
</script>
</body>
</html>
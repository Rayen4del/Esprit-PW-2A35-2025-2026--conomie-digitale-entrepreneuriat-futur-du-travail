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
    echo json_encode(['html' => $html, 'count' => count($events), 'events' => $events, 'paginationHtml' => $paginationHtml]);
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
                 data-event-id="<?= intval($ev['ID']) ?>"
                 data-event-title="<?= htmlspecialchars($ev['Titre'], ENT_QUOTES) ?>"
                 data-event-location="<?= htmlspecialchars($ev['lieu_lien'], ENT_QUOTES) ?>"
                 data-event-status="<?= htmlspecialchars($eventStatus, ENT_QUOTES) ?>">
              <div class="event-image-placeholder" style="cursor:pointer" onclick="openMapModal('<?= htmlspecialchars($ev['lieu_lien'], ENT_QUOTES) ?>', '<?= htmlspecialchars($ev['Titre'], ENT_QUOTES) ?>')">
                <i class="bi bi-geo-alt"></i>
                <span class="map-hint">Cliquez pour voir la carte</span>
              </div>
              <div style="position:relative;margin-top:-2rem;padding:0 .75rem">
                <span class="status-badge <?= $statusClass ?>"><?= strtoupper($ev['Statut']) ?></span>
              </div>
              <div class="card-body pt-2">
                <small class="text-muted"><?= $typeLabels[$ev['Type']] ?? ucfirst($ev['Type']) ?></small>
                <h5 class="mt-1 mb-2"><?= htmlspecialchars($ev['Titre']) ?></h5>
                <p class="mb-1 small"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($ev['dateEvent'])) ?></p>
                <p class="mb-1 small"><i class="bi bi-people me-1"></i><?= intval($ev['inscrits_count'] ?? 0) ?> / <?= intval($ev['nbplaces']) ?> inscrits</p>
                <p class="mb-1 small"><i class="bi bi-currency-euro me-1"></i><?= number_format($ev['prix'] ?? 0, 2, ',', ' ') ?> €</p>
                <p class="mb-3 small <?= intval($ev['places_restantes'] ?? 0) <= 0 ? 'text-danger' : 'text-success' ?>">
                  <i class="bi bi-door-open me-1"></i><?= intval($ev['places_restantes'] ?? 0) ?> places restantes
                </p>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-primary flex-fill" onclick="viewEventDetail(<?= $ev['ID'] ?>)">
                    <i class="bi bi-eye"></i> Détail
                  </button>
                  <button class="btn btn-sm btn-primary flex-fill" onclick="openJoinModal(this)" <?= !$isOpen ? 'disabled' : '' ?>>
                    <i class="bi bi-check-lg"></i> S'inscrire
                  </button>
                </div>
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
    .event-image-placeholder{height:160px;background:linear-gradient(135deg,#696cff22,#a3a4ff44);display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:3rem;color:#696cff;gap:.5rem}
    .map-hint{font-size:.8rem;color:#696cff;font-weight:500}
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
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="navbarSearchInput"
                   placeholder="Rechercher un événement..."
                   value="<?= $searchVal ?>" />
          </div>
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="joinEventModalLabel">Inscription à l'événement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3" id="selectedEventTitle">Choisissez un événement</p>
        
        <!-- Alert for payment events -->
        <div id="paymentAlert" class="alert alert-info d-none mb-3">
          <i class="bi bi-credit-card me-2"></i>
          <span id="paymentAlertText">Cet événement est payant. Veuillez procéder au paiement.</span>
        </div>

        <!-- Form Section -->
        <div id="formSection" class="mb-4">
          <div class="mb-3">
            <label class="form-label" for="idUtilisateur">ID Utilisateur *</label>
            <input type="number" class="form-control" id="idUtilisateur" min="1" required>
          </div>

          <div class="mb-3">
            <label class="form-label" for="idEvent">ID Événement</label>
            <input type="number" class="form-control" id="idEvent" readonly required>
          </div>

          <div class="mb-3">
            <label class="form-label" for="dateInscription">Date d'inscription</label>
            <input type="text" class="form-control" id="dateInscription" value="<?= date('Y-m-d') ?>" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label" for="statut">Statut</label>
            <select class="form-select" id="statut" required>
              <option value="inscrit" selected>Inscrit</option>
              <option value="annulé">Annulé</option>
            </select>
          </div>
        </div>

        <!-- Payment Section (shown only for paid events) -->
        <div id="paymentSection" class="d-none">
          <div class="mb-3">
            <h6 class="mb-3 fw-bold">Détails de paiement</h6>
            <div id="payment-element"></div>
            <div id="payment-message" class="text-danger mt-2 d-none"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="submitBtn" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i> <span id="submitBtnText">Confirmer</span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mapModalLabel">Localisation</h5>
        <button type="button" class="btn btn-sm btn-outline-primary" id="getDirectionsBtn">
          <i class="bi bi-signpost-2 me-1"></i> Itinéraire
        </button>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body p-0">
        <iframe
          id="mapIframe"
          width="100%"
          height="400"
          style="border:0"
          loading="lazy"
          allowfullscreen
          src="">
        </iframe>
      </div>
    </div>
  </div>
</div>

<!-- View Event Detail Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalTitle">Détail de l'événement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="viewModalBody">
        <!-- Event details will be inserted here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.stripe.com/v3/"></script>

<script>
// Open map modal
let currentDestination = '';

function openMapModal(location, title) {
    const mapIframe = document.getElementById('mapIframe');
    const mapModalLabel = document.getElementById('mapModalLabel');
    currentDestination = location;
    mapIframe.src = 'https://maps.google.com/maps?q=' + encodeURIComponent(location) + '&t=&z=13&ie=UTF8&iwloc=&output=embed';
    mapModalLabel.textContent = 'Localisation - ' + title;
    const mapModal = new bootstrap.Modal(document.getElementById('mapModal'));
    mapModal.show();
}

// Get directions from user's location to destination
function setupDirectionsButton() {
    const btn = document.getElementById('getDirectionsBtn');
    if (btn) {
        btn.onclick = function() {
            if (!currentDestination) return;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        const mapIframe = document.getElementById('mapIframe');
                        mapIframe.src = 'https://maps.google.com/maps?saddr=' + userLat + ',' + userLng + '&daddr=' + encodeURIComponent(currentDestination) + '&output=embed';
                    },
                    function(error) {
                        alert('Impossible d\'obtenir votre position. Veuillez autoriser la géolocalisation.');
                        // Fallback: open Google Maps with directions
                        window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(currentDestination), '_blank');
                    }
                );
            } else {
                alert('La géolocalisation n\'est pas supportée par votre navigateur.');
                // Fallback: open Google Maps with directions
                window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(currentDestination), '_blank');
            }
        };
    }
}

setupDirectionsButton();

// Show tooltip on non-open events
document.querySelectorAll('.event-card').forEach(card => {
  if (card.dataset.eventStatus !== 'ouvert') {
    card.title = "Cet événement n\'est pas disponible pour l\'inscription.";
  }
});

function openJoinModal(element) {
    const card = element.closest('.event-card');
    const eventId = card.dataset.eventId;
    const eventTitle = card.dataset.eventTitle;
    const eventData = allEvents[eventId];
    
    // Set event data
    document.getElementById('idEvent').value = eventId;
    document.getElementById('selectedEventTitle').textContent = eventTitle
        ? `Événement sélectionné : ${eventTitle}`
        : 'Événement sélectionné';
    
    // Check if event is paid
    const isPaid = eventData && eventData.prix > 0;
    
    if (isPaid) {
        // Show payment section for paid events
        document.getElementById('paymentAlert').classList.remove('d-none');
        document.getElementById('paymentSection').classList.remove('d-none');
        document.getElementById('submitBtnText').textContent = 'Procéder au paiement';
        
        // Initialize Stripe for this event
        initializeStripePayment(eventId, eventData.prix);
    } else {
        // Hide payment section for free events
        document.getElementById('paymentAlert').classList.add('d-none');
        document.getElementById('paymentSection').classList.add('d-none');
        document.getElementById('submitBtnText').textContent = 'Confirmer';
    }
    
    currentEventId = eventId;
    currentEventPrice = eventData ? eventData.prix : 0;
    
    const joinEventModal = new bootstrap.Modal(document.getElementById('joinEventModal'));
    joinEventModal.show();
}

// Stripe initialization variables
let stripe = null;
let elements = null;
let currentEventId = null;
let currentEventPrice = 0;

function initializeStripePayment(eventId, price) {
    // Initialize Stripe only once
    if (!stripe) {
        // Fetch the publishable key from server
        fetch('/projet/controller/evenement/get_stripe_config.php')
            .then(res => res.json())
            .then(data => {
                if (data.publishableKey) {
                    stripe = Stripe(data.publishableKey);
                    continuePaymentInit(eventId, price);
                } else {
                    showPaymentError('Erreur: Configuration Stripe manquante');
                }
            })
            .catch(err => {
                console.error('Config fetch error:', err);
                showPaymentError('Erreur: Impossible de charger la configuration');
            });
        return;
    }
    continuePaymentInit(eventId, price);
}

function continuePaymentInit(eventId, price) {
function continuePaymentInit(eventId, price) {
    
    // Create or recreate elements
    const paymentElement = document.getElementById('payment-element');
    if (paymentElement.innerHTML) {
        paymentElement.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Chargement...</span></div>'; // Clear previous content
    }
    
    if (!elements) {
        elements = stripe.elements();
    }
    
    // Create payment intent
    fetch('/projet/controller/evenement/create_payment_intent.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            amount: Math.round(price * 100), // Convert to cents
            eventId: eventId
        })
    })
    .then(res => {
        // Check if response is JSON
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return res.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON: ' + text.substring(0, 300));
            });
        }
        if (!res.ok) {
            return res.json().then(data => {
                throw new Error(data.error || 'Server error');
            });
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            // Create payment element with client secret
            if (elements) {
                const paymentEl = elements.getElement('payment');
                if (paymentEl) paymentEl.destroy();
            }
            elements = stripe.elements({ clientSecret: data.clientSecret });
            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');
            document.getElementById('payment-message').classList.add('d-none');
        } else {
            showPaymentError('Erreur: ' + data.error);
        }
    })
    .catch(err => {
        console.error('Payment init error:', err);
        showPaymentError('Erreur réseau: ' + err.message);
    });
}

function showPaymentError(message) {
    const errorDiv = document.getElementById('payment-message');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
}

function viewEventDetail(eventId) {
    const eventData = allEvents[eventId];
    if (!eventData) return;
    
    const detailHtml = `
        <div class="mb-3">
            <h6 class="text-muted small">Type</h6>
            <p>${eventData.Type}</p>
        </div>
        <div class="mb-3">
            <h6 class="text-muted small">Date</h6>
            <p><i class="bi bi-calendar3 me-2"></i>${eventData.dateEvent}</p>
        </div>
        <div class="mb-3">
            <h6 class="text-muted small">Durée</h6>
            <p>${eventData.duree > 0 ? eventData.duree + ' heure(s)' : 'Non spécifiée'}</p>
        </div>
        <div class="mb-3">
            <h6 class="text-muted small">Localisation</h6>
            <p><i class="bi bi-geo-alt me-2"></i>${eventData.lieu_lien}</p>
        </div>
        <div class="mb-3">
            <h6 class="text-muted small">Prix</h6>
            <p>${eventData.prix > 0 ? eventData.prix.toFixed(2) + ' €' : 'Gratuit'}</p>
        </div>
        <div class="mb-3">
            <h6 class="text-muted small">Description</h6>
            <p>${eventData.Description || 'Aucune description disponible'}</p>
        </div>
        <div class="mb-0">
            <h6 class="text-muted small">Statut</h6>
            <p><span class="badge bg-${eventData.Statut === 'ouvert' ? 'success' : (eventData.Statut === 'ferme' ? 'danger' : 'warning')}">${eventData.Statut.toUpperCase()}</span></p>
        </div>
    `;
    
    document.getElementById('viewModalTitle').textContent = 'Détail - ' + eventData.Titre;
    document.getElementById('viewModalBody').innerHTML = detailHtml;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

document.getElementById('year').textContent = new Date().getFullYear();

// Event data for event detail modal
const allEvents = <?php
    $eventsJs = [];
    foreach ($events as $ev) {
        $eventsJs[$ev['ID']] = [
            'ID'          => $ev['ID'],
            'Titre'       => $ev['Titre'],
            'Type'        => $ev['Type'],
            'Description' => $ev['Description'],
            'dateEvent'   => date('d/m/Y', strtotime($ev['dateEvent'])),
            'duree'       => $ev['duree'],
            'lieu_lien'   => $ev['lieu_lien'],
            'Statut'      => $ev['Statut'],
            'nbplaces'    => $ev['nbplaces'],
            'prix'        => $ev['prix'] ?? 0,
        ];
    }
    echo json_encode($eventsJs);
?>;

// Handle join/payment form submission
document.getElementById('submitBtn').addEventListener('click', async function(e) {
    e.preventDefault();
    
    const idUtilisateur = document.getElementById('idUtilisateur').value;
    const idEvent = document.getElementById('idEvent').value;
    const statut = document.getElementById('statut').value;
    
    if (!idUtilisateur || !idEvent) {
        alert('Veuillez remplir tous les champs');
        return;
    }
    
    // If event is free, submit directly
    if (currentEventPrice <= 0) {
        submitRegistration(idUtilisateur, idEvent, statut);
    } else {
        // For paid events, process payment first
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Traitement...';
        
        try {
            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                redirect: 'if_required'
            });
            
            if (error) {
                showPaymentError('Erreur de paiement: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Procéder au paiement';
            } else if (paymentIntent.status === 'succeeded') {
                // Payment successful, now register
                submitRegistration(idUtilisateur, idEvent, statut);
            }
        } catch (err) {
            showPaymentError('Erreur: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Procéder au paiement';
        }
    }
});

function submitRegistration(idUtilisateur, idEvent, statut) {
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.pathname;
    
    const fields = {
        'join_event': '1',
        'idUtilisateur': idUtilisateur,
        'idEvent': idEvent,
        'statut': statut
    };
    
    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}

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
        
        // Update allEvents object with new event data for detail modal
        if (data.events && Array.isArray(data.events)) {
            data.events.forEach(ev => {
                allEvents[ev.ID] = {
                    ID: ev.ID,
                    Titre: ev.Titre,
                    Type: ev.Type,
                    Description: ev.Description,
                    dateEvent: new Date(ev.dateEvent).toLocaleDateString('fr-FR'),
                    duree: ev.duree,
                    lieu_lien: ev.lieu_lien,
                    Statut: ev.Statut,
                    nbplaces: ev.nbplaces,
                    prix: ev.prix || 0
                };
            });
        }
        
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

const filterSearchInput = document.getElementById('searchInput');
const navbarSearchInput = document.getElementById('navbarSearchInput');

// Sync navbar search with filter search
if (navbarSearchInput) {
    navbarSearchInput.addEventListener('input', (e) => {
        if (filterSearchInput) {
            filterSearchInput.value = e.target.value;
        }
        updateResults(1);
    });
}

// Sync filter search with navbar search
if (filterSearchInput) {
    filterSearchInput.addEventListener('input', (e) => {
        if (navbarSearchInput) {
            navbarSearchInput.value = e.target.value;
        }
        updateResults(1);
    });
}

document.querySelector('select[name="type"]').addEventListener('change', () => updateResults(1));
</script>
</body>
</html>
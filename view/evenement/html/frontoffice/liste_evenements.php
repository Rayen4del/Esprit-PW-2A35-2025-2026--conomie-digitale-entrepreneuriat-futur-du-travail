<?php
// ─── Self-contained: all logic runs here, URL stays on this view ────
include_once __DIR__ . '/../../../../config.php';
include_once __DIR__ . '/../../../../model/Evenement.php';
include_once __DIR__ . '/../../../../controller/evenement/EvenementController.php';

$controller  = new EvenementController();
$editEvent   = null;
$editErrors  = [];
$editSuccess = false;
$deleted     = false;
$errMsg      = '';
function renderEvents($events) {
    ob_start();
    if (empty($events)): ?>
        <div class="text-center py-5">
          <i class="bi bi-inbox" style="font-size:3rem;color:#a1acb8"></i>
          <p class="mt-3 text-muted">Aucun événement trouvé</p>
          <a href="/projet/view/evenement/html/frontoffice/form_evenement.php" class="btn btn-primary mt-2">
            <i class="bi bi-plus-lg me-1"></i> Créer le premier événement
          </a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
          <?php foreach ($events as $ev):
            $statusClass = match($ev['Statut']) {
              'ouvert'  => 'bg-success',
              'ferme'   => 'bg-danger',
              'complet' => 'bg-warning',
              default   => 'bg-secondary'
            };
            $statusText = match($ev['Statut']) {
              'ouvert'  => 'OUVERT',
              'ferme'   => 'FERMÉ',
              'complet' => 'COMPLET',
              default   => strtoupper($ev['Statut'])
            };
            $typeLabels = ['workshop'=>'Workshop','conference'=>'Conférence','seminaire'=>'Séminaire',
                           'hackathon'=>'Hackathon','formation'=>'Formation','webinar'=>'Webinaire','autre'=>'Autre'];
            $typeLabel = $typeLabels[$ev['Type']] ?? ucfirst($ev['Type']);
            $dateFormatted = date('d/m/Y', strtotime($ev['dateEvent']));
          ?>
          <div class="col">
            <div class="card event-card h-100">
              <div class="event-image-placeholder">
                <i class="bi bi-calendar-event"></i>
              </div>
              <div style="position:relative;margin-top:-2rem;padding:0 .75rem">
                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
              </div>
              <div class="card-body pt-2">
                <small class="text-muted"><?= htmlspecialchars($typeLabel) ?></small>
                <h5 class="mt-1 mb-2" style="font-size:1rem;font-weight:700;color:#566a7f">
                  <?= htmlspecialchars($ev['Titre']) ?>
                </h5>
                <p class="mb-1 small"><i class="bi bi-calendar3 me-1"></i><?= $dateFormatted ?>
                  <?= $ev['duree'] > 0 ? ' · ' . $ev['duree'] . 'h' : '' ?>
                </p>
                <p class="mb-1 small"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars(substr($ev['lieu_lien'], 0, 50)) ?><?= strlen($ev['lieu_lien']) > 50 ? '…' : '' ?></p>
                <p class="mb-3 small"><i class="bi bi-people me-1"></i><?= $ev['nbplaces'] ?> place(s)</p>
                <?php if (!empty($ev['Description'])): ?>
                <p class="small text-muted mb-3" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                  <?= htmlspecialchars($ev['Description']) ?>
                </p>
                <?php endif; ?>
                <div class="d-flex gap-2">
                  <!-- Btn Voir (détail rapide) -->
                  <button class="btn btn-sm btn-primary flex-fill"
                          onclick="viewEvent(<?= $ev['ID'] ?>)"
                          title="Voir le détail">
                    <i class="bi bi-eye"></i> Voir
                  </button>
                  <!-- Btn Export PDF — inscriptions -->
                  <a class="btn btn-sm btn-outline-info flex-fill"
                     href="/projet/view/evenement/html/frontoffice/export_event_registrations.php?event_id=<?= $ev['ID'] ?>"
                     target="_blank"
                     title="Exporter les inscriptions en PDF">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                  </a>
                  <!-- Btn Modifier — ouvre le modal d'édition -->
                  <button class="btn btn-sm btn-outline-warning flex-fill"
                          onclick="openEditModal(<?= htmlspecialchars(json_encode($ev), ENT_QUOTES) ?>)"
                          title="Modifier">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <!-- Btn Supprimer — ouvre le modal de confirmation -->
                  <button class="btn btn-sm btn-outline-danger flex-fill"
                          onclick="openDeleteModal(<?= $ev['ID'] ?>, '<?= addslashes(htmlspecialchars($ev['Titre'])) ?>')"
                          title="Supprimer">
                    <i class="bi bi-trash"></i> Supprimer
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

function renderPagination($currentPage, $totalPages, $search, $statut, $type) {
    if ($totalPages <= 1) {
        return '';
    }

    $baseUrl = '/projet/view/evenement/html/frontoffice/liste_evenements.php';
    $html = '<nav aria-label="Navigation des pages"><ul class="pagination justify-content-center mt-4">';

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $currentPage ? ' active' : '';
        $query = http_build_query(['search' => $search, 'statut' => $statut, 'type' => $type, 'page' => $i]);
        $html .= '<li class="page-item' . $active . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($baseUrl . '?' . $query) . '" onclick="goToPage(' . $i . '); return false;">' . $i . '</a>';
        $html .= '</li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

// ── Handle AJAX requests ───────────────────────────────────────────
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $search = trim($_GET['search'] ?? '');
    $statut = trim($_GET['statut'] ?? '');
    $type   = trim($_GET['type']   ?? '');
    $page   = max(1, intval($_GET['page'] ?? 1));
    $perPage = 6;
    $totalCount = $controller->countFiltered($search, $statut, $type);
    $totalPages = max(1, (int) ceil($totalCount / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $events = $controller->filtrer($search, $statut, $type, $perPage, $offset);
    $html   = renderEvents($events);
    $paginationHtml = renderPagination($page, $totalPages, $search, $statut, $type);
    header('Content-Type: application/json');
    echo json_encode(['html' => $html, 'count' => count($events), 'events' => $events, 'paginationHtml' => $paginationHtml]);
    exit;
}

// ── Handle GET delete ──────────────────────────────────────────────
if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    if ($delId > 0 && $controller->deleteEvenement($delId)) {
        $deleted = true;
    } else {
        $errMsg = 'delete_failed';
    }
}

// ── Handle POST edit ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action']) && $_POST['_action'] === 'edit') {
    $editId = intval($_POST['id'] ?? 0);
    if ($editId > 0) {
        $titre       = trim($_POST['titre']       ?? '');
        $type        = trim($_POST['type']        ?? '');
        $description = trim($_POST['description'] ?? '');
        $dateEvent   = trim($_POST['dateEvent']   ?? '');
        $duree       = intval($_POST['duree']     ?? 0);
        $lieu_lien   = trim($_POST['lieu_lien']   ?? '');
        $statut      = trim($_POST['statut']      ?? '');
        $nbplaces    = intval($_POST['nbplaces']  ?? 0);

        $evenement = new Evenement($titre, $type, $description, $dateEvent, $duree, $lieu_lien, $statut, $nbplaces, $editId);

        if ($controller->updateEvenement($evenement, $editId)) {
            $editSuccess = true;
        } else {
            $editErrors[] = "Erreur lors de la modification.";
            $editEvent    = $controller->getById($editId);
        }
    }
}

// ── Handle GET flags passed after redirect ─────────────────────────
if (!$deleted)     $deleted     = isset($_GET['deleted']);
if (!$editSuccess) $editSuccess = isset($_GET['updated']);
if (!$errMsg)      $errMsg      = $_GET['error'] ?? '';

// ── Load events (after any deletion/edit) ─────────────────────────
$search      = trim($_GET['search'] ?? '');
$statut      = trim($_GET['statut'] ?? '');
$type        = trim($_GET['type']   ?? '');
$currentPage = max(1, intval($_GET['page'] ?? 1));
$perPage     = 6;
$totalCount  = $controller->countFiltered($search, $statut, $type);
$totalPages  = max(1, (int) ceil($totalCount / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;
$events = $controller->filtrer($search, $statut, $type, $perPage, $offset);
$pagination = renderPagination($currentPage, $totalPages, $search, $statut, $type);

$searchVal = htmlspecialchars($search);
$statutVal = htmlspecialchars($statut);
$typeVal   = htmlspecialchars($type);
?>
<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
  <title>Liste des Événements</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
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
    .layout-menu{width:var(--sidebar-width);background:var(--menu-bg);flex-shrink:0;
      position:fixed;top:0;left:0;bottom:0;z-index:1100;overflow-y:auto;
      box-shadow:0 0 0 1px rgba(67,89,113,.05),0 2px 6px rgba(67,89,113,.12)}
    .app-brand{display:flex;align-items:center;padding:1.5rem 1.5rem .5rem;min-height:var(--header-height)}
    .app-brand-text{font-size:1.25rem;font-weight:700;color:#566a7f;margin-left:.6rem}
    .menu-inner{list-style:none;padding:.5rem 0 1rem}
    .menu-header{padding:.75rem 1.5rem .25rem;font-size:.6875rem;font-weight:600;color:#a1acb8;
      letter-spacing:.8px;text-transform:uppercase}
    .menu-item{position:relative}
    .menu-link{display:flex;align-items:center;padding:.625rem 1.5rem;color:var(--menu-text);
      text-decoration:none;border-radius:.375rem;margin:.1rem .75rem;
      transition:background .15s,color .15s;font-size:.9rem;cursor:pointer}
    .menu-link:hover{background:var(--menu-active-bg);color:var(--menu-active-text)}
    .menu-link.active{background:var(--menu-active-bg);color:var(--menu-active-text);font-weight:600}
    .menu-icon{font-size:1.1rem;margin-right:.75rem;opacity:.85}
    .menu-sub{list-style:none;padding:0;display:none}
    .menu-item.open>.menu-sub{display:block}
    .menu-sub .menu-link{padding-left:3rem;font-size:.875rem}
    .menu-toggle::after{content:'\F285';font-family:'bootstrap-icons';margin-left:auto;
      font-size:.8rem;transition:transform .2s}
    .menu-item.open>.menu-toggle::after{transform:rotate(90deg)}
    .layout-page{margin-left:var(--sidebar-width);display:flex;flex-direction:column;flex:1;min-width:0}
    .layout-navbar{background:var(--navbar-bg);height:var(--header-height);display:flex;
      align-items:center;padding:0 1.5rem;box-shadow:0 1px 0 rgba(67,89,113,.1);
      position:sticky;top:0;z-index:1000;gap:1rem}
    .navbar-search{flex:1;max-width:300px}
    .navbar-search .input-group{background:var(--body-bg);border-radius:.375rem}
    .navbar-search input{background:transparent;border:none;font-size:.875rem;color:#566a7f}
    .navbar-search input:focus{box-shadow:none;outline:none}
    .navbar-search .input-group-text{background:transparent;border:none;color:#a1acb8}
    .navbar-nav-right{display:flex;align-items:center;gap:.25rem;margin-left:auto}
    .nav-icon-btn{background:none;border:none;color:#566a7f;font-size:1.2rem;padding:.5rem;
      border-radius:.375rem;cursor:pointer;position:relative;transition:background .15s,color .15s}
    .nav-icon-btn:hover{background:var(--body-bg);color:var(--bs-primary)}
    .nav-badge{position:absolute;top:6px;right:6px;width:8px;height:8px;background:#ff3e1d;
      border-radius:50%;border:2px solid #fff}
    .user-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#696cff,#a3a4ff);
      display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;
      font-size:.9rem;cursor:pointer;margin-left:.5rem;position:relative}
    .online-dot{position:absolute;bottom:1px;right:1px;width:9px;height:9px;background:#71dd37;
      border-radius:50%;border:2px solid #fff}
    .content-wrapper{flex:1;padding:1.5rem}
    .page-title{font-size:1.125rem;font-weight:700;color:#566a7f;margin-bottom:1.5rem}
    .page-title span{color:#a1acb8;font-weight:400}
    .card{background:#fff;border:none;border-radius:.5rem;
      box-shadow:0 2px 6px rgba(67,89,113,.12);margin-bottom:1.5rem}
    .card-header{background:transparent;border-bottom:1px solid rgba(67,89,113,.08);
      padding:1rem 1.5rem;display:flex;align-items:center;gap:.75rem}
    .card-header-icon{width:36px;height:36px;border-radius:.375rem;
      background:linear-gradient(135deg,#696cff,#a3a4ff);
      display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem}
    .card-header-title{font-size:1rem;font-weight:600;color:#566a7f}
    .card-body{padding:1.5rem}
    /* Event cards */
    .event-card{border:none;border-radius:.5rem;box-shadow:0 2px 6px rgba(67,89,113,.12);
      transition:transform .2s,box-shadow .2s;overflow:hidden}
    .event-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(67,89,113,.18)}
    .event-image{height:160px;background-size:cover;background-position:center;position:relative}
    .event-image-placeholder{height:160px;background:linear-gradient(135deg,#696cff22,#a3a4ff44);
      display:flex;align-items:center;justify-content:center;font-size:3rem;color:#696cff}
    .status-badge{position:absolute;top:.75rem;right:.75rem;padding:.25rem .65rem;
      border-radius:999px;font-size:.7rem;font-weight:700;letter-spacing:.5px;color:#fff}
    .content-footer{padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;
      font-size:.8125rem;color:#a1acb8;border-top:1px solid rgba(67,89,113,.08)}
    .footer-link{color:#a1acb8;text-decoration:none;margin-left:1rem}
    .footer-link:hover{color:var(--bs-primary)}
    /* Modal */
    .modal-header{border-bottom:1px solid rgba(67,89,113,.08)}
    .modal-footer{border-top:1px solid rgba(67,89,113,.08)}
    .section-divider{font-size:.75rem;font-weight:700;color:#a1acb8;letter-spacing:.8px;
      text-transform:uppercase;margin:1.25rem 0 .75rem;padding-bottom:.4rem;
      border-bottom:1px dashed #e7eaf0}
    .field-error{font-size:.8rem;color:#ff3e1d;margin-top:.25rem}
    @keyframes slideIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}
  </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- ═══ SIDEBAR ═══ -->
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
        <li class="menu-header">Général</li>
        <li class="menu-item">
          <a href="#" class="menu-link"><i class="bi bi-grid menu-icon"></i> Tableau de bord</a>
        </li>
        <li class="menu-header">Événements</li>
        <li class="menu-item open">
          <a onclick="toggleMenu(this)" class="menu-link menu-toggle active">
            <i class="bi bi-calendar-event menu-icon"></i> Événements
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="/projet/view/evenement/html/frontoffice/liste_evenements.php" class="menu-link active">
                <i class="bi bi-list-ul menu-icon"></i> Liste
              </a>
            </li>
            <li class="menu-item">
              <a href="/projet/view/evenement/html/frontoffice/form_evenement.php" class="menu-link">
                <i class="bi bi-plus-circle menu-icon"></i> Créer
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </aside>
    <!-- ═══ / SIDEBAR ═══ -->

    <div class="layout-page">

      <!-- NAVBAR -->
      <nav class="layout-navbar">
        <div class="navbar-search">
          <form method="GET" action="/projet/view/evenement/html/frontoffice/liste_evenements.php" id="searchForm">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" name="search" id="searchInput"
                     placeholder="Rechercher un événement..."
                     value="<?= $searchVal ?>" />
              <input type="hidden" name="statut" value="<?= $statutVal ?>">
              <input type="hidden" name="type"   value="<?= $typeVal ?>">
            </div>
          </form>
        </div>
        <div class="navbar-nav-right">
          <button class="nav-icon-btn"><i class="bi bi-bell"></i><span class="nav-badge"></span></button>
          <button class="nav-icon-btn"><i class="bi bi-chat-dots"></i></button>
          <div class="user-avatar">AD<span class="online-dot"></span></div>
        </div>
      </nav>
      <!-- / NAVBAR -->

      <div class="content-wrapper">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <p class="page-title mb-0">Événements <span>/ Liste</span></p>
          <a href="/projet/view/evenement/html/frontoffice/form_evenement.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Nouvel événement
          </a>
        </div>

        <?php if ($deleted): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i> Événement supprimé avec succès.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($errMsg === 'not_found'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> Événement introuvable.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($editSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i> Événement modifié avec succès.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="card mb-4">
          <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-funnel"></i></div>
            <span class="card-header-title">Filtres</span>
            <span class="badge bg-primary ms-auto"><?= count($events) ?> résultat(s)</span>
          </div>
          <div class="card-body">
            <form method="GET" action="/projet/view/evenement/html/frontoffice/liste_evenements.php" id="filterForm">
              <input type="hidden" name="search" value="<?= $searchVal ?>">
              <div class="row g-3">
                <div class="col-md-4">
                  <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="ouvert"  <?= $statutVal==='ouvert'  ? 'selected':'' ?>>Ouvert</option>
                    <option value="ferme"   <?= $statutVal==='ferme'   ? 'selected':'' ?>>Fermé</option>
                    <option value="complet" <?= $statutVal==='complet' ? 'selected':'' ?>>Complet</option>
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
                  <a href="/projet/view/evenement/html/frontoffice/liste_evenements.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-repeat me-1"></i>Réinitialiser
                  </a>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Events Grid -->
        <div id="eventsContainer">
          <?php echo renderEvents($events); ?>
        </div>
        <div id="paginationContainer"><?= $pagination ?></div>

      </div><!-- /content-wrapper -->

      <footer class="content-footer">
        <div>© <span id="year"></span> EventHub — Plateforme d'événements</div>
        <div>
          <a href="#" class="footer-link">Aide</a>
          <a href="#" class="footer-link">Mentions légales</a>
          <a href="#" class="footer-link">Support</a>
        </div>
      </footer>
    </div><!-- /layout-page -->
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — Confirmation Suppression
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
          <div style="width:40px;height:40px;border-radius:.5rem;background:#fff0ee;
                      display:flex;align-items:center;justify-content:center">
            <i class="bi bi-trash" style="color:#ff3e1d;font-size:1.1rem"></i>
          </div>
          <h5 class="modal-title mb-0" id="deleteModalLabel">Confirmer la suppression</h5>
        </div>
      </div>
      <div class="modal-body pt-2">
        <p>Êtes-vous sûr de vouloir supprimer l'événement<br>
          <strong id="deleteEventName" style="color:#566a7f"></strong> ?
        </p>
        <p class="text-danger small mb-0">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          Cette action est irréversible.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-lg me-1"></i> Annuler
        </button>
        <a id="deleteConfirmBtn" href="#" class="btn btn-danger">
          <i class="bi bi-trash me-1"></i> Supprimer
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — Modifier Événement
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <div style="width:36px;height:36px;border-radius:.375rem;
                      background:linear-gradient(135deg,#696cff,#a3a4ff);
                      display:flex;align-items:center;justify-content:center;color:#fff">
            <i class="bi bi-pencil-square"></i>
          </div>
          <h5 class="modal-title mb-0" id="editModalLabel">Modifier l'événement</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <?php if (!empty($editErrors)): ?>
      <div class="mx-3 mt-3 alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <ul class="mb-0 mt-1">
          <?php foreach ($editErrors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="modal-body">
        <form id="editForm" method="POST" action="/projet/view/evenement/html/frontoffice/liste_evenements.php" novalidate>
          <input type="hidden" name="_action" value="edit">
          <input type="hidden" name="id" id="edit-id" value="<?= $editEvent['ID'] ?? '' ?>">

          <div class="section-divider">Informations générales</div>

          <!-- Titre -->
          <div class="row mb-3">
            <label class="col-sm-3 col-form-label" for="edit-titre">Titre *</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit-titre" name="titre"
                     placeholder="Nom de l'événement" maxlength="200" required />
              <div class="field-error" id="eerr-titre"></div>
            </div>
          </div>

          <!-- Type -->
          <div class="row mb-3">
            <label class="col-sm-3 col-form-label" for="edit-type">Type *</label>
            <div class="col-sm-9">
              <select class="form-select" id="edit-type" name="type" required>
                <option value="" disabled>-- Sélectionner --</option>
                <option value="workshop">Workshop</option>
                <option value="conference">Conférence</option>
                <option value="seminaire">Séminaire</option>
                <option value="hackathon">Hackathon</option>
                <option value="formation">Formation</option>
                <option value="webinar">Webinaire</option>
                <option value="autre">Autre</option>
              </select>
              <div class="field-error" id="eerr-type"></div>
            </div>
          </div>

          <!-- Description -->
          <div class="row mb-3">
            <label class="col-sm-3 col-form-label" for="edit-desc">Description</label>
            <div class="col-sm-9">
              <textarea class="form-control" id="edit-desc" name="description" rows="3"
                        placeholder="Description..."></textarea>
            </div>
          </div>

          <div class="section-divider">Date &amp; Durée</div>

          <!-- Date -->
          <div class="row mb-3">
            <label class="col-sm-3 col-form-label" for="edit-date">Date *</label>
            <div class="col-sm-9">
              <input type="date" class="form-control" id="edit-date" name="dateEvent" required />
              <div class="field-error" id="eerr-date"></div>
            </div>
          </div>

          <!-- Durée -->
          <div class="row mb-3">
            <label class="col-sm-3 col-form-label" for="edit-duree">Durée</label>
            <div class="col-sm-9">
              <div class="input-group">
                <input type="number" class="form-control" id="edit-duree" name="duree" min="0" max="720" />
                <span class="input-group-text">heure(s)</span>
              </div>
              <div class="field-error" id="eerr-duree"></div>
            </div>
          </div>

          <div class="section-divider">Lieu &amp; Accès</div>

          <!-- Lieu -->
          <div class="row mb-3">
            <label class="col-sm-3 col-form-label" for="edit-lieu">Lieu / Lien *</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit-lieu" name="lieu_lien"
                     placeholder="Adresse ou lien" maxlength="255" required />
              <div class="field-error" id="eerr-lieu"></div>
            </div>
          </div>

          <!-- Places -->
          <div class="row mb-3">
            <label class="col-sm-3 col-form-label" for="edit-places">Nb. places *</label>
            <div class="col-sm-9">
              <div class="input-group">
                <input type="number" class="form-control" id="edit-places" name="nbplaces" min="1" required />
                <span class="input-group-text">participants</span>
              </div>
              <div class="field-error" id="eerr-places"></div>
            </div>
          </div>

          <div class="section-divider">Statut</div>

          <div class="row mb-3">
            <label class="col-sm-3 col-form-label" for="edit-statut">Statut *</label>
            <div class="col-sm-9">
              <select class="form-select" id="edit-statut" name="statut" required>
                <option value="" disabled>-- Sélectionner --</option>
                <option value="ouvert">Ouvert</option>
                <option value="ferme">Fermé</option>
                <option value="complet">Complet</option>
              </select>
              <div class="field-error" id="eerr-statut"></div>
            </div>
          </div>

        </form>
      </div><!-- /modal-body -->

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-lg me-1"></i> Annuler
        </button>
        <button type="button" class="btn btn-primary" onclick="submitEditForm()">
          <i class="bi bi-check-lg me-1"></i> Enregistrer
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Vue rapide event -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalTitle">Détail de l'événement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast area -->
<div id="toast-area" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/projet/view/evenement/html/frontoffice/js/common.js"></script>
<script src="/projet/view/evenement/html/frontoffice/js/validate_rules.js"></script>
<script src="/projet/view/evenement/html/frontoffice/js/list_validation.js"></script>
<script>
    // Données PHP → JS : index des événements par ID pour la vue rapide
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
            ];
        }
        echo json_encode($eventsJs);
    ?>;

    <?php if ($editSuccess): ?>
    showToast('Événement modifié avec succès !', 'success');
    <?php elseif ($deleted): ?>
    showToast('Événement supprimé.', 'success');
    <?php endif; ?>

    <?php if (!empty($editErrors) && $editEvent): ?>
    var editEventFromPHP = <?= json_encode($editEvent) ?>;
    <?php else: ?>
    var editEventFromPHP = null;
    <?php endif; ?>

    // Override openDeleteModal to always use the view URL for deletes
    function openDeleteModal(id, title) {
        document.getElementById('deleteEventName').textContent = title;
        document.getElementById('deleteConfirmBtn').href =
            '/projet/view/evenement/html/frontoffice/liste_evenements.php?delete_id=' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Re-open edit modal automatically if server-side validation failed
    if (editEventFromPHP) {
        openEditModal(editEventFromPHP);
    }

    document.getElementById('year').textContent = new Date().getFullYear();

    function goToPage(page) {
        updateResults(page);
    }

    // AJAX search and filter
    function updateResults(page = 1) {
        const search = document.getElementById('searchInput').value;
        const statut = document.querySelector('select[name="statut"]').value;
        const type = document.querySelector('select[name="type"]').value;
        fetch(window.location.pathname + '?ajax=1&search=' + encodeURIComponent(search) + '&statut=' + encodeURIComponent(statut) + '&type=' + encodeURIComponent(type) + '&page=' + page, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.querySelector('.badge.bg-primary').textContent = data.count + ' résultat(s)';
            document.getElementById('eventsContainer').innerHTML = data.html;
            document.getElementById('paginationContainer').innerHTML = data.paginationHtml;
            allEvents = {};
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
                    nbplaces: ev.nbplaces
                };
            });
        })
        .catch(error => console.error('Error:', error));
    }

    document.getElementById('searchInput').addEventListener('input', () => updateResults(1));
    document.querySelector('select[name="statut"]').addEventListener('change', () => updateResults(1));
    document.querySelector('select[name="type"]').addEventListener('change', () => updateResults(1));
</script>
</body>
</html>
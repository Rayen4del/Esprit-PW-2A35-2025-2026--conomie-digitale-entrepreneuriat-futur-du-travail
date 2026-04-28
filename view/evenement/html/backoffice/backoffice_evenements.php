<?php
// ====================== HANDLE DELETES ONLY ======================
include(__DIR__ . '/../../../../config.php');
include(__DIR__ . '/../../../../controller/evenement/EvenementController.php');

$controller = new EvenementController();
$deleted = false;
$error   = '';
$success = '';

// Single Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    if ($id > 0) {
        $ev = $controller->getById($id);
        if ($ev) {
            $controller->deleteEvenement($id);
            $deleted = true;
            $success = "Événement supprimé avec succès.";
        } else {
            $error = "Événement introuvable.";
        }
    } else {
        $error = "ID invalide.";
    }
}

// Bulk Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    if (!empty($_POST['ids'])) {
        $ids = array_map('intval', explode(',', $_POST['ids']));
        foreach ($ids as $id) {
            if ($id > 0) {
                $controller->deleteEvenement($id);
            }
        }
        $deleted = true;
        $success = "Suppression multiple effectuée.";
    } else {
        $error = "Aucun événement sélectionné.";
    }
}

// Registration update/delete from backoffice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_action'])) {
    $registrationAction = $_POST['registration_action'];

    if ($registrationAction === 'update') {
        $id = intval($_POST['reg_id'] ?? 0);
        $idUtilisateur = intval($_POST['reg_idUtilisateur'] ?? 0);
        $idEvent = intval($_POST['reg_idEvent'] ?? 0);
        $statut = trim($_POST['reg_statut'] ?? 'inscrit');

        if ($id <= 0 || $idUtilisateur <= 0 || $idEvent <= 0) {
            $error = "Données d'inscription invalides.";
        } elseif ($controller->updateRegistration($id, $idUtilisateur, $idEvent, $statut)) {
            $success = "Inscription mise à jour avec succès.";
        } else {
            $error = "Erreur mise à jour inscription: " . $controller->getLastError();
        }
    }

    if ($registrationAction === 'delete') {
        $id = intval($_POST['reg_id'] ?? 0);
        if ($id <= 0) {
            $error = "ID d'inscription invalide.";
        } elseif ($controller->deleteRegistration($id)) {
            $success = "Inscription supprimée avec succès.";
        } else {
            $error = "Erreur suppression inscription: " . $controller->getLastError();
        }
    }
}

// Filters & Loading events
$allEvents = $controller->getAll();
$search    = trim($_GET['search'] ?? '');
$statut    = trim($_GET['statut'] ?? '');
$type      = trim($_GET['type']   ?? '');
$events    = $controller->filtrer($search, $statut, $type);
$registrations = $controller->getAllRegistrations();

$searchVal = htmlspecialchars($search);
$statutVal = htmlspecialchars($statut);
$typeVal   = htmlspecialchars($type);

$typeLabels = [
    'workshop'   => 'Workshop',
    'conference' => 'Conférence',
    'seminaire'  => 'Séminaire',
    'hackathon'  => 'Hackathon',
    'formation'  => 'Formation',
    'webinar'    => 'Webinaire',
    'autre'      => 'Autre'
];

$totalEvents = count($allEvents);
$totalRegistrations = count($registrations);
$activeRegistrations = 0;
$fullEvents = 0;
foreach ($allEvents as $evStat) {
    if (($evStat['Statut'] ?? '') === 'complet') {
        $fullEvents++;
    }
}
foreach ($registrations as $regStat) {
    if (in_array($regStat['Statut'], ['inscrit', 'confirmé'], true)) {
        $activeRegistrations++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
  <title>Backoffice - Gestion des Événements</title>
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
      border-radius:.375rem;cursor:pointer;transition:background .15s,color .15s}
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
    /* Table styles */
    .events-table-container {
      background: #fff;
      border-radius: .5rem;
      box-shadow: 0 2px 6px rgba(67,89,113,.12);
      padding: 0;
      overflow: hidden;
    }
    .table-events {
      margin-bottom: 0;
      width: 100%;
    }
    .table-events th {
      background: #f8f9fc;
      border-bottom: 1px solid rgba(67,89,113,.08);
      font-weight: 600;
      font-size: .85rem;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: #566a7f;
      padding: 1rem 1rem;
    }
    .table-events td {
      vertical-align: middle;
      padding: 1rem 1rem;
      border-bottom: 1px solid rgba(67,89,113,.05);
      color: #3b4252;
    }
    .table-events tr:last-child td {
      border-bottom: none;
    }
    .table-events tr:hover {
      background-color: #f9f9ff;
    }
    .status-badge-table {
      padding: .25rem .75rem;
      border-radius: 999px;
      font-size: .7rem;
      font-weight: 700;
      color: #fff;
      display: inline-block;
      text-align: center;
      min-width: 85px;
    }
    .status-badge-table.bg-success { background: #71dd37; }
    .status-badge-table.bg-danger { background: #ff3e1d; }
    .status-badge-table.bg-warning { background: #ffab00; color: #3b4252; }
    .status-badge-table.bg-secondary { background: #a1acb8; }
    .btn-sm-icon {
      padding: .25rem .5rem;
      font-size: .8rem;
    }
    .event-checkbox-table {
      width: 18px;
      height: 18px;
      cursor: pointer;
      margin: 0;
    }
    .select-all-checkbox {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    /* Bulk actions bar */
    .bulk-actions-bar{position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);
      background:#fff;border-radius:2rem;box-shadow:0 8px 24px rgba(0,0,0,.15);
      padding:.5rem 1.5rem;z-index:1050;display:flex;gap:1rem;align-items:center;
      border:1px solid rgba(67,89,113,.1)}
    @keyframes slideIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}
    .detail-row{display:flex;padding:10px 0;border-bottom:1px solid #eef2f6}
    .detail-label{width:120px;font-weight:600;color:#566a7f}
    .detail-value{flex:1;color:#3b4252}
    .event-detail-icon{width:70px;height:70px;background:linear-gradient(135deg,#696cff22,#a3a4ff44);
      border-radius:50%;display:flex;align-items:center;justify-content:center;
      font-size:2rem;color:#696cff;margin:0 auto 1rem auto}
    .content-footer{padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;
      font-size:.8125rem;color:#a1acb8;border-top:1px solid rgba(67,89,113,.08)}
    .footer-link{color:#a1acb8;text-decoration:none;margin-left:1rem}
    .footer-link:hover{color:var(--bs-primary)}
  </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- ═══ SIDEBAR (admin version) ═══ -->
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
        <li class="menu-header">ADMINISTRATION</li>
        <li class="menu-item open">
          <a href="#" class="menu-link menu-toggle active">
            <i class="bi bi-shield-lock menu-icon"></i> Backoffice
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="/projet/view/evenement/html/backoffice/backoffice_evenements.php" class="menu-link active">
                <i class="bi bi-grid-3x3 menu-icon"></i> Événements
              </a>
            </li>
            <li class="menu-item">
              <a href="/projet/view/evenement/html/backoffice/statistics_dashboard.php" class="menu-link">
                <i class="bi bi-bar-chart menu-icon"></i> Statistiques
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
          <form method="GET" action="/projet/view/evenement/html/backoffice/backoffice_evenements.php" id="searchForm">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" name="search" id="searchInput"
                     placeholder="Rechercher un événement..."
                     value="<?= $searchVal ?>"
                     oninput="document.getElementById('searchForm').submit()" />
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
          <p class="page-title mb-0">Gestion des Événements <span>/ Backoffice (suppression uniquement)</span></p>
        </div>

        <?php if ($deleted): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle-fill me-2"></i> Événement(s) supprimé(s) avec succès !
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card mb-0"><div class="card-body"><small class="text-muted">Événements</small><h4 class="mb-0"><?= $totalEvents ?></h4></div></div>
          </div>
          <div class="col-md-3">
            <div class="card mb-0"><div class="card-body"><small class="text-muted">Inscriptions</small><h4 class="mb-0"><?= $totalRegistrations ?></h4></div></div>
          </div>
          <div class="col-md-3">
            <div class="card mb-0"><div class="card-body"><small class="text-muted">Inscriptions actives</small><h4 class="mb-0"><?= $activeRegistrations ?></h4></div></div>
          </div>
          <div class="col-md-3">
            <div class="card mb-0"><div class="card-body"><small class="text-muted">Événements complets</small><h4 class="mb-0"><?= $fullEvents ?></h4></div></div>
          </div>
        </div>

        <!-- Filtres card -->
        <div class="card mb-4">
          <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-funnel"></i></div>
            <span class="card-header-title">Filtres</span>
            <span class="badge bg-primary ms-auto"><?= count($events) ?> événement(s)</span>
          </div>
          <div class="card-body">
            <form method="GET" action="/projet/view/evenement/html/backoffice/backoffice_evenements.php" id="filterForm">
              <input type="hidden" name="search" value="<?= $searchVal ?>">
              <div class="row g-3">
                <div class="col-md-4">
                  <select name="statut" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Tous les statuts</option>
                    <option value="ouvert"  <?= $statutVal==='ouvert'  ? 'selected':'' ?>>Ouvert</option>
                    <option value="ferme"   <?= $statutVal==='ferme'   ? 'selected':'' ?>>Fermé</option>
                    <option value="complet" <?= $statutVal==='complet' ? 'selected':'' ?>>Complet</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <select name="type" class="form-select" onchange="document.getElementById('filterForm').submit()">
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
                  <a href="/projet/view/evenement/html/backoffice/backoffice_evenements.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-repeat me-1"></i>Réinitialiser
                  </a>
                </div>
              </div>
            </form>
          </div>
        </div>

        <?php if (empty($events)): ?>
        <div class="text-center py-5">
          <i class="bi bi-inbox" style="font-size:3rem;color:#a1acb8"></i>
          <p class="mt-3 text-muted">Aucun événement trouvé</p>
        </div>
        <?php else: ?>

        <!-- Table View -->
        <div class="events-table-container">
          <div class="table-responsive">
            <table class="table-events table">
              <thead>
                <tr>
                  <th style="width: 40px">
                    <input type="checkbox" id="selectAllCheckbox" class="select-all-checkbox">
                  </th>
                  <th>Titre</th>
                  <th>Type</th>
                  <th>Date</th>
                  <th>Lieu</th>
                  <th>Places</th>
                  <th>Statut</th>
                  <th style="width: 120px">Actions</th>
                </tr>
              </thead>
              <tbody>
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
                  $typeLabel = $typeLabels[$ev['Type']] ?? ucfirst($ev['Type']);
                  $dateFormatted = date('d/m/Y', strtotime($ev['dateEvent']));
                  $lieuShort = htmlspecialchars(strlen($ev['lieu_lien']) > 40 ? substr($ev['lieu_lien'], 0, 40) . '…' : $ev['lieu_lien']);
                ?>
                <tr>
                  <td class="text-center">
                    <input type="checkbox" class="event-checkbox-table" value="<?= $ev['ID'] ?>" onchange="updateBulkUI()">
                  </td>
                  <td class="fw-semibold"><?= htmlspecialchars($ev['Titre']) ?></td>
                  <td><?= htmlspecialchars($typeLabel) ?></td>
                  <td><?= $dateFormatted ?></td>
                  <td><?= $lieuShort ?></td>
                  <td><?= $ev['nbplaces'] ?></td>
                  <td><span class="status-badge-table <?= $statusClass ?>"><?= $statusText ?></span></td>
                  <td>
                    <div class="d-flex gap-2">
                      <button class="btn btn-sm btn-primary"
                              onclick='viewEventDetails(<?= json_encode($ev, JSON_HEX_TAG) ?>)'
                              title="Voir détail">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger"
                              onclick="openDeleteModal(<?= $ev['ID'] ?>, '<?= addslashes(htmlspecialchars($ev['Titre'])) ?>')"
                              title="Supprimer">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>

        <!-- Registrations management -->
        <div class="card mt-4">
          <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-card-checklist"></i></div>
            <span class="card-header-title">Gestion des inscriptions</span>
            <span class="badge bg-primary ms-auto"><?= count($registrations) ?> inscription(s)</span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-events mb-0">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>ID Utilisateur</th>
                    <th>Événement</th>
                    <th>ID Event</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($registrations)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Aucune inscription trouvée.</td></tr>
                  <?php else: ?>
                    <?php foreach ($registrations as $reg): ?>
                    <tr>
                      <td><?= intval($reg['ID']) ?></td>
                      <td><?= intval($reg['IDUtilisateur']) ?></td>
                      <td><?= htmlspecialchars($reg['Titre']) ?></td>
                      <td><?= intval($reg['IDEvent']) ?></td>
                      <td><?= htmlspecialchars($reg['DateInscription']) ?></td>
                      <td>
                        <?php
                          $regStatusClass = match($reg['Statut']) {
                            'inscrit', 'confirmé' => 'bg-success',
                            'annulé' => 'bg-danger',
                            default => 'bg-secondary'
                          };
                        ?>
                        <span class="status-badge-table <?= $regStatusClass ?>"><?= htmlspecialchars($reg['Statut']) ?></span>
                      </td>
                      <td>
                        <div class="d-flex gap-2">
                          <button type="button"
                                  class="btn btn-sm btn-outline-primary"
                                  data-bs-toggle="modal"
                                  data-bs-target="#editRegistrationModal"
                                  data-id="<?= intval($reg['ID']) ?>"
                                  data-idutilisateur="<?= intval($reg['IDUtilisateur']) ?>"
                                  data-idevent="<?= intval($reg['IDEvent']) ?>"
                                  data-statut="<?= htmlspecialchars($reg['Statut'], ENT_QUOTES) ?>">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <form method="POST" action="" onsubmit="return confirm('Supprimer cette inscription ?');" class="d-inline">
                            <input type="hidden" name="registration_action" value="delete">
                            <input type="hidden" name="reg_id" value="<?= intval($reg['ID']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                          </form>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

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

<!-- Bulk action bar (floating) -->
<div id="bulkActionsBar" class="bulk-actions-bar d-none">
  <span id="selectedCountBadge" class="badge bg-primary">0 sélectionné(s)</span>
  <button class="btn btn-danger btn-sm" onclick="confirmBulkDelete()">
    <i class="bi bi-trash me-1"></i> Supprimer la sélection
  </button>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — Détail de l'événement
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Détail de l'événement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — Confirmation suppression simple
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
          <div style="width:40px;height:40px;border-radius:.5rem;background:#fff0ee;
                      display:flex;align-items:center;justify-content:center">
            <i class="bi bi-trash" style="color:#ff3e1d;font-size:1.1rem"></i>
          </div>
          <h5 class="modal-title mb-0">Confirmer la suppression</h5>
        </div>
      </div>
      <div class="modal-body pt-2">
        <p>Êtes-vous sûr de vouloir supprimer l'événement<br>
          <strong id="deleteEventName"></strong> ?
        </p>
        <p class="text-danger small mb-0">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> Cette action est irréversible.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
        <a id="deleteConfirmBtn" href="#" class="btn btn-danger">Supprimer</a>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — Suppression multiple (bulk)
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Suppression Multiple</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Supprimer <strong id="bulkCount">0</strong> événement(s) ?</p>
        <p class="text-danger small"><i class="bi bi-shield-exclamation me-1"></i> Cette action est irréversible.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
        <form method="POST" action="">
          <input type="hidden" name="bulk_delete" value="1">
          <input type="hidden" name="ids" id="bulkIds">
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — Modifier inscription
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="editRegistrationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
        <input type="hidden" name="registration_action" value="update">
        <input type="hidden" name="reg_id" id="reg-edit-id">
        <div class="modal-header">
          <h5 class="modal-title">Modifier inscription</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="reg-edit-idUtilisateur">ID Utilisateur</label>
            <input type="number" class="form-control" id="reg-edit-idUtilisateur" name="reg_idUtilisateur" min="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="reg-edit-idEvent">ID Event</label>
            <select class="form-select" id="reg-edit-idEvent" name="reg_idEvent" required>
              <option value="" disabled selected>-- Sélectionner --</option>
              <?php foreach ($allEvents as $evOpt): ?>
                <option value="<?= intval($evOpt['ID']) ?>">#<?= intval($evOpt['ID']) ?> - <?= htmlspecialchars($evOpt['Titre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-0">
            <label class="form-label" for="reg-edit-statut">Statut</label>
            <select class="form-select" id="reg-edit-statut" name="reg_statut" required>
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
// Helper: show toast message
function showToast(message, type = 'success') {
    let toastArea = document.getElementById('toast-area');
    if (!toastArea) {
        const div = document.createElement('div');
        div.id = 'toast-area';
        div.style.position = 'fixed';
        div.style.bottom = '1.5rem';
        div.style.right = '1.5rem';
        div.style.zIndex = '9999';
        div.style.display = 'flex';
        div.style.flexDirection = 'column';
        div.style.gap = '.5rem';
        document.body.appendChild(div);
        toastArea = div;
    }
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    toast.style.animation = 'slideIn 0.3s ease';
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    toastArea.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

// View event details
function viewEventDetails(event) {
    const modalBody = document.getElementById('viewModalBody');
    const statusClass = {
        'ouvert': 'bg-success',
        'ferme': 'bg-danger',
        'complet': 'bg-warning'
    }[event.Statut] || 'bg-secondary';
    const statusText = {
        'ouvert': 'Ouvert',
        'ferme': 'Fermé',
        'complet': 'Complet'
    }[event.Statut] || event.Statut;
    const typeLabel = {
        'workshop':'Workshop','conference':'Conférence','seminaire':'Séminaire',
        'hackathon':'Hackathon','formation':'Formation','webinar':'Webinaire','autre':'Autre'
    }[event.Type] || event.Type;

    modalBody.innerHTML = `
        <div class="text-center mb-3">
            <div class="event-detail-icon"><i class="bi bi-calendar-week"></i></div>
            <h4>${escapeHtml(event.Titre)}</h4>
            <span class="status-badge-table ${statusClass}" style="display:inline-block;margin-top:5px">${statusText}</span>
        </div>
        <div class="detail-row"><div class="detail-label"><i class="bi bi-diagram-3 me-2"></i>Type :</div><div>${escapeHtml(typeLabel)}</div></div>
        <div class="detail-row"><div class="detail-label"><i class="bi bi-calendar3 me-2"></i>Date :</div><div>${formatDate(event.dateEvent)}</div></div>
        <div class="detail-row"><div class="detail-label"><i class="bi bi-clock me-2"></i>Durée :</div><div>${event.duree > 0 ? event.duree + ' heure(s)' : 'Non spécifiée'}</div></div>
        <div class="detail-row"><div class="detail-label"><i class="bi bi-geo-alt me-2"></i>Lieu :</div><div>${escapeHtml(event.lieu_lien)}</div></div>
        <div class="detail-row"><div class="detail-label"><i class="bi bi-people me-2"></i>Places :</div><div>${event.nbplaces}</div></div>
        <div class="detail-row"><div class="detail-label"><i class="bi bi-chat-text me-2"></i>Description :</div><div>${escapeHtml(event.Description) || 'Aucune description'}</div></div>
    `;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

// Delete single event
function openDeleteModal(id, title) {
    document.getElementById('deleteEventName').textContent = title;
    document.getElementById('deleteConfirmBtn').href = `/projet/view/evenement/html/backoffice/backoffice_evenements.php?delete_id=${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Bulk delete UI
function updateBulkUI() {
    const checkboxes = document.querySelectorAll('.event-checkbox-table');
    const checked = Array.from(checkboxes).filter(cb => cb.checked);
    const count = checked.length;
    const bar = document.getElementById('bulkActionsBar');
    const badge = document.getElementById('selectedCountBadge');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    if (count > 0) {
        bar.classList.remove('d-none');
        badge.textContent = `${count} sélectionné(s)`;
    } else {
        bar.classList.add('d-none');
    }
    
    // Update select all checkbox state
    if (selectAllCheckbox) {
        const totalCheckboxes = checkboxes.length;
        const checkedCount = checked.length;
        if (totalCheckboxes === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === totalCheckboxes) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount > 0 && checkedCount < totalCheckboxes) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }
}

function confirmBulkDelete() {
    const ids = Array.from(document.querySelectorAll('.event-checkbox-table:checked')).map(cb => cb.value);
    if (ids.length === 0) return;
    document.getElementById('bulkCount').textContent = ids.length;
    document.getElementById('bulkIds').value = ids.join(',');
    new bootstrap.Modal(document.getElementById('bulkDeleteModal')).show();
}

// Select All functionality
function initSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (!selectAllCheckbox) return;
    
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        const allCheckboxes = document.querySelectorAll('.event-checkbox-table');
        allCheckboxes.forEach(cb => {
            cb.checked = isChecked;
        });
        updateBulkUI();
    });
}

// Utilities
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR');
}

document.getElementById('year').textContent = new Date().getFullYear();

// Initialize select all
initSelectAll();

// Registration edit modal prefill
document.querySelectorAll('[data-bs-target="#editRegistrationModal"]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('reg-edit-id').value = btn.getAttribute('data-id') || '';
        document.getElementById('reg-edit-idUtilisateur').value = btn.getAttribute('data-idutilisateur') || '';
        document.getElementById('reg-edit-idEvent').value = btn.getAttribute('data-idevent') || '';
        document.getElementById('reg-edit-statut').value = btn.getAttribute('data-statut') || 'inscrit';
    });
});

// Auto-show toasts if any deletion happened
<?php if ($deleted): ?>
showToast('Événement(s) supprimé(s) avec succès', 'success');
<?php elseif ($error): ?>
showToast('<?= addslashes($error) ?>', 'danger');
<?php endif; ?>
</script>
</body>
</html>
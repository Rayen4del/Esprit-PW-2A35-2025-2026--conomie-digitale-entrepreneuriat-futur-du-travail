<?php
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../Controller/gestion_opportunite/OportunityController.php';

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

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../../assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
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
.sk-modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* when open */
.sk-modal-overlay.open {
  display: flex;
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
    <title>Dashboard - Analytics | Sneat - Bootstrap 5 HTML Admin Template - Pro</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="../../assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="../../assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="../../assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../../assets/js/config.js"></script>
  </head>

  <body data-sort="<?= $sort ?>">
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo">
                            <i class='bx bx-code-alt' style="font-size:2rem;color:#696cff"></i>
                  <defs>
                    <path
                      d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z"
                      id="path-1"
                    ></path>
                    <path
                      d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z"
                      id="path-3"
                    ></path>
                    <path
                      d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z"
                      id="path-4"
                    ></path>
                    <path
                      d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z"
                      id="path-5"
                    ></path>
                  </defs>
                  <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                      <g id="Icon" transform="translate(27.000000, 15.000000)">
                        <g id="Mask" transform="translate(0.000000, 8.000000)">
                          <mask id="mask-2" fill="white">
                            <use xlink:href="#path-1"></use>
                          </mask>
                          <use fill="#696cff" xlink:href="#path-1"></use>
                          <g id="Path-3" mask="url(#mask-2)">
                            <use fill="#696cff" xlink:href="#path-3"></use>
                            <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                          </g>
                          <g id="Path-4" mask="url(#mask-2)">
                            <use fill="#696cff" xlink:href="#path-4"></use>
                            <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                          </g>
                        </g>
                        <g
                          id="Triangle"
                          transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) "
                        >
                          <use fill="#696cff" xlink:href="#path-5"></use>
                          <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                        </g>
                      </g>
                    </g>
                  </g>
                </svg>
              </span>
              <span class="app-brand-text demo menu-text fw-bolder ms-2">Skiller</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

         <ul class="menu-inner py-1 list-unstyled">
            <!-- Dashboard -->
            <li class="menu-item">
              <a href="Frontoffice.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home"></i>
                <div>Backoffice Home</div>
              </a>
            </li>

            <!-- Users Management -->
            <li class="menu-item">
              <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div>User Management</div>
              </a>

              <ul class="menu-sub list-unstyled">

                <li class="menu-item">
                  <a href="../gestion_utilisateur/dashboard.php" class="menu-link">
                    <div>Users</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_utilisateur/frontoffice/profil.php" class="menu-link">
                    <div>My Profile</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_utilisateur/frontoffice/logout.php" class="menu-link">

                    <div>Logout</div>
                  </a>
                </li>

              </ul>
            </li>

            <!-- Events -->
            <li class="menu-item">
              <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                <div>Event Management</div>
              </a>

              <ul class="menu-sub list-unstyled">

                <li class="menu-item">
                  <a href="../gestion_evenemnt/backoffice/backoffice_evenements.php" class="menu-link">
                    <div>Events</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_evenemnt/backoffice/statistics_dashboard.php" class="menu-link">
                    <div>Statistics</div>
                  </a>
                </li>

              </ul>
            </li>

            <!-- Blog -->
            <li class="menu-item">
              <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-news"></i>
                <div>Blog Management</div>
              </a>

              <ul class="menu-sub list-unstyled">

                <li class="menu-item">
                  <a href="../gestion_blog/backoffice/comments/comments.php" class="menu-link">
                    <div>Posts</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Formation/consulterformations.php" class="menu-link">
                    <div>Articles</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Formation/ajouterformation.php" class="menu-link">
                    <div>Comments</div>
                  </a>
                </li>

              </ul>
            </li>

            <!-- Sponsorship -->
            <li class="menu-item">
              <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dollar"></i>
                <div>Sponsorship Management</div>
              </a>

              <ul class="menu-sub list-unstyled">

                <li class="menu-item">
                  <a href="../dashboard.php" class="menu-link">
                    <div>Products</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Formation/consulterformations.php" class="menu-link">
                    <div>Sponsors</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Formation/ajouterformation.php" class="menu-link">
                    <div>Statistics</div>
                  </a>
                </li>

              </ul>
            </li>

            <!-- Training -->
            <li class="menu-item active">
              <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-book-open"></i>
                <div>Training Management</div>
              </a>

              <ul class="menu-sub list-unstyled">

                <li class="menu-item">
                  <a href="dashboard.php" class="menu-link">
                    <div>Dashboard</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Formation/consulterformations.php" class="menu-link">
                    <div>View Trainings</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Formation/ajouterformation.php" class="menu-link">
                    <div>Add Training</div>
                  </a>
                </li>

                <li class="menu-item active">
                  <a href="consulterchapitres.php" class="menu-link">
                    <div>Chapters</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="ajouterchapitre.php" class="menu-link">
                    <div>Add Chapter</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Test/consultertests.php" class="menu-link">
                    <div>Tests</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Test/ajoutertest.php" class="menu-link">
                    <div>Add Test</div>
                  </a>
                </li>

              </ul>
            </li>

            <!-- Opportunities -->
            <li class="menu-item">
              <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-briefcase"></i>
                <div>Opportunities Management</div>
              </a>

              <ul class="menu-sub list-unstyled">

                <li class="menu-item">
                  <a href="../opportunities/dashboard.php" class="menu-link">
                    <div>Opportunities Dashboard</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../opportunities/applications.php" class="menu-link">
                    <div>Applications</div>
                  </a>
                </li>

              </ul>
            </li>

          </ul>
        </aside>
        <!-- / Menu ------------------------------------------------------------------------------------------------->

        <!-- Layout container ------------------------------------------------------------------------------------------------->
        <div class="layout-page">

<div
  class="offcanvas offcanvas-end"
  tabindex="-1"
  id="offcanvasEnd"
  aria-labelledby="offcanvasEndLabel"
>

  <div class="offcanvas-header">
    <h5 id="offcanvasEndLabel" class="offcanvas-title">Mon Profil</h5>
    <button type="button"
            class="btn-close text-reset"
            data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">

    <!-- ================= PROFILE SECTION ================= -->
    <div class="text-center mb-4">

      <img
        src="../../assets/img/avatars/1.png"
        class="rounded-circle mb-3"
        width="80"
        alt="Profile"
      />

      <h6 class="mb-1">
        <?= $_SESSION['user_nom'] ?? 'Invité' ?>
      </h6>

      <small class="text-muted">
        <?= $_SESSION['user_type'] ?? 'Non connecté' ?>
      </small>

    </div>

    <!-- ================= MENU ================= -->
    <div class="list-group">

      <!-- ================= NOT LOGGED IN ================= -->
      <?php if (!isset($_SESSION['user_id'])): ?>

        <a href="../../login.php"
           class="list-group-item list-group-item-action text-primary">
          <i class="bx bx-log-in me-2"></i> Sign in
        </a>

      <?php else: ?>

        <!-- ================= ADMIN ONLY ================= -->
        <?php if (($_SESSION['user_type'] ?? '') === 'admin'): ?>

          <a href="../Formation/consulterformations.php"
             class="list-group-item list-group-item-action">
            <i class="bx bx-shield me-2"></i> Interface Admin
          </a>

        <!-- ================= USER + ADMIN ================= -->
        <a href="../../FrontOffice/interfacesuperutilisateur.php"
           class="list-group-item list-group-item-action">
          <i class="bx bx-user-circle me-2"></i> Interface Utilisateur
        </a>
        <?php endif; ?>
        <!-- ================= LOGOUT ================= -->
        <a href="../../signout.php"
           class="list-group-item list-group-item-action text-danger">
          <i class="bx bx-log-out me-2"></i> Déconnexion
        </a>

      <?php endif; ?>

    </div>

  </div>
</div>
<!-- ================= END OFFCANVAS ================= -->

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">
              
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
              </div> 
                 
          </div>
        <!-- / Layout container ------------------------------------------------------------------------------------------------->
      </div>
      <!-- / Layout wrapper -->
    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="../../assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="../../assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="../../assets/js/dashboards-analytics.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
       
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




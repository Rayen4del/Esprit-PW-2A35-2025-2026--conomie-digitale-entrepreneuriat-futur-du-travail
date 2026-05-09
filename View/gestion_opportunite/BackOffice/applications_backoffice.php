<?php
// View/BackOffice/applications_backoffice.php
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../Controller/gestion_opportunite/ApplicationController.php';



$assetPath = '../assets/';
$applicationCtrl = new ApplicationController();
$successMsg = '';
$errorMsg = '';

// ── AJAX must be caught FIRST, before any other output ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === '1') {
    $search    = trim($_POST['search'] ?? '');
    $sortBy    = $_POST['sort_by']    ?? 'DateCondidature';
    $sortOrder = $_POST['sort_order'] ?? 'DESC';
    $typeJob   = trim($_POST['type_job'] ?? '');
    $applicationsData = $applicationCtrl
        ->listApplicationsWithDetails($search, $sortBy, $sortOrder, $typeJob)
        ->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($applicationsData, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Delete action ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $appId = $_POST['app_id'] ?? null;
    if ($appId) {
        try {
            $applicationCtrl->deleteApplication((int)$appId);
            $successMsg = 'Application deleted successfully!';
        } catch (Exception $e) {
            $errorMsg = 'Error: ' . $e->getMessage();
        }
    }
}

// ── Fetch all data for initial render ────────────────────────────────────────
$applicationsData = $applicationCtrl->listApplicationsWithDetails()->fetchAll(PDO::FETCH_ASSOC);
$applicationTypes = $applicationCtrl->listApplicationTypes()->fetchAll(PDO::FETCH_COLUMN);
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
<div class="sk-page">

  <div class="sk-page-header">
    <div class="sk-page-title">
      Applications Manager
      <small>Review and delete applications (Admin)</small>
    </div>
    <span class="sk-badge sk-badge-jobs" style="font-size:.7rem;align-self:flex-start">Admin View</span>
  </div>

  <!-- ── Search / Sort bar ── -->
  <div class="sk-card" style="margin-bottom:20px">
    <div class="sk-filter-bar">
      <div class="sk-filter-field sk-filter-search">
        <label class="sk-label">Search</label>
        <div style="position:relative">
          <i class="bx bx-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--sk-muted)"></i>
          <input type="text" id="searchInput" class="sk-input"
                 placeholder="Opportunity, user ID, status, motivation…"
                 style="padding-left:34px" autocomplete="off">
        </div>
      </div>
      <div class="sk-filter-field">
        <label class="sk-label">Sort By</label>
        <select id="sortBy" class="sk-select">
          <option value="ID">ID</option>
          <option value="opportunity_title">Opportunity</option>
          <option value="IDUtilisateur">User ID</option>
          <option value="DateCondidature" selected>Date Applied</option>
          <option value="Type_job">Job Type</option>
        </select>
      </div>
      <div class="sk-filter-field">
        <label class="sk-label">Job Type</label>
        <select id="typeFilter" class="sk-select">
          <option value="">All types</option>
          <?php foreach ($applicationTypes as $type): ?>
            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars(ucfirst($type)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="sk-filter-field">
        <label class="sk-label">Order</label>
        <select id="sortOrder" class="sk-select">
          <option value="DESC">Descending</option>
          <option value="ASC">Ascending</option>
        </select>
      </div>
      <div class="sk-filter-field" style="align-self:flex-end">
        <button class="sk-btn sk-btn-ghost" onclick="resetFilters()">
          <i class="bx bx-refresh"></i> Reset
        </button>
      </div>
    </div>
  </div>

  <?php if ($successMsg): ?>
    <div class="sk-toast sk-toast-success" id="sk-toast">
      <i class="bx bx-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
    </div>
  <?php elseif ($errorMsg): ?>
    <div class="sk-toast sk-toast-danger" id="sk-toast">
      <i class="bx bx-x-circle"></i> <?= htmlspecialchars($errorMsg) ?>
    </div>
  <?php endif; ?>

  <!-- Results count -->
  <div style="font-size:.8rem;color:var(--sk-muted);margin-bottom:10px" id="resultCount">
    <?= count($applicationsData) ?> application<?= count($applicationsData) !== 1 ? 's' : '' ?>
  </div>

  <div class="sk-card">
    <table class="sk-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Opportunity</th>
          <th>User ID</th>
          <th>Date Applied</th>
          <th>Status</th>
          <th>Motivation</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php foreach ($applicationsData as $app): ?>
          <?php $statut = strtolower($app['Statut'] ?? 'pending'); ?>
          <tr>
            <td style="color:var(--sk-muted);font-size:.8rem">#<?= htmlspecialchars($app['ID']) ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($app['opportunity_title'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($app['IDUtilisateur']) ?></td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($app['DateCondidature'] ?? '—') ?></td>
            <td>
              <span class="sk-badge sk-badge-<?= $statut ?>">
                <?= htmlspecialchars(ucfirst($app['Statut'] ?? 'Pending')) ?>
              </span>
            </td>
            <td class="sk-motivation-cell" title="<?= htmlspecialchars($app['motivation'] ?? '') ?>">
              <?= htmlspecialchars(mb_substr($app['motivation'] ?? '', 0, 60)) ?><?= strlen($app['motivation'] ?? '') > 60 ? '…' : '' ?>
            </td>
            <td style="white-space:nowrap">
              <button class="sk-btn sk-btn-ghost sk-btn-sm"
                      onclick="showDetails(<?= (int)$app['ID'] ?>, <?= htmlspecialchars(json_encode($app['opportunity_title'] ?? 'N/A'), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($app['motivation'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($app['CV'] ?? ''), ENT_QUOTES) ?>)">
                <i class="bx bx-show"></i> View Application
              </button>
              <form method="POST" style="display:inline" onsubmit="return confirm('Delete this application?')">
                <input type="hidden" name="action"  value="delete">
                <input type="hidden" name="app_id"  value="<?= (int)$app['ID'] ?>">
                <button type="submit" class="sk-btn sk-btn-danger sk-btn-sm"><i class="bx bx-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($applicationsData)): ?>
          <tr><td colspan="7" class="sk-empty">No applications found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div id="emptyState" class="sk-empty" style="display:none">No applications match your search.</div>
  </div>
</div>

<!-- Details Modal -->
<div class="sk-modal-overlay" id="detailsModal">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <span class="sk-modal-title">Application Details</span>
      <button class="sk-modal-close" onclick="closeModal()">×</button>
    </div>
    <div class="sk-modal-body">
      <div class="sk-form-group">
        <label class="sk-label">Opportunity</label>
        <p id="modalOpportunity" style="font-weight:600;color:var(--sk-text)"></p>
      </div>
      <div class="sk-form-group">
        <label class="sk-label">Motivation</label>
        <p id="modalMotivation" style="line-height:1.6;color:var(--sk-text);white-space:pre-wrap"></p>
      </div>
      <div class="sk-form-group">
        <label class="sk-label">CV / Resource</label>
        <div id="modalCV"></div>
      </div>
    </div>
    <div class="sk-modal-footer">
      <button class="sk-btn sk-btn-ghost" onclick="closeModal()">Close</button>
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
       

<script>
// Seed data from PHP — source of truth for client-side filter
const ALL_DATA = <?= json_encode($applicationsData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

let debounceTimer = null;
let currentTableData = ALL_DATA;

function scheduleSearch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(performSearch, 220);
}

function performSearch() {
  const sortBy    = document.getElementById('sortBy').value;
  const sortOrder = document.getElementById('sortOrder').value;
  const typeJob   = document.getElementById('typeFilter').value;

  // Send to PHP for authoritative filter+sort (handles edge cases server-side)
  fetch(window.location.pathname, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax=1'
        + '&search='     + encodeURIComponent(document.getElementById('searchInput').value.trim())
        + '&sort_by='    + encodeURIComponent(sortBy)
        + '&sort_order=' + encodeURIComponent(sortOrder)
        + '&type_job='   + encodeURIComponent(typeJob)
  })
  .then(r => {
    if (!r.ok) throw new Error('Server error ' + r.status);
    return r.json();
  })
  .then(data => renderTable(data))
  .catch(err => console.error('Search error:', err));
}

function renderTable(data) {
  const tbody      = document.getElementById('tableBody');
  const emptyState = document.getElementById('emptyState');
  const counter    = document.getElementById('resultCount');
  currentTableData = Array.isArray(data) ? data : [];

  counter.textContent = currentTableData.length + ' application' + (currentTableData.length !== 1 ? 's' : '');

  if (currentTableData.length === 0) {
    tbody.innerHTML = '';
    emptyState.style.display = 'block';
    return;
  }

  emptyState.style.display = 'none';

  tbody.innerHTML = currentTableData.map((app, index) => {
    const statut   = (app.Statut || 'pending').toLowerCase();
    const ucStatut = statut.charAt(0).toUpperCase() + statut.slice(1);
    const motiv    = app.motivation || '';
    const short    = motiv.length > 60 ? motiv.substring(0, 60) + '…' : motiv;

    return `
      <tr>
        <td style="color:var(--sk-muted);font-size:.8rem">#${esc(app.ID)}</td>
        <td style="font-weight:600">${esc(app.opportunity_title || 'N/A')}</td>
        <td>${esc(app.IDUtilisateur)}</td>
        <td style="color:var(--sk-muted)">${esc(app.DateCondidature || '—')}</td>
        <td><span class="sk-badge sk-badge-${statut}">${ucStatut}</span></td>
        <td class="sk-motivation-cell" title="${esc(motiv)}">${esc(short)}</td>
        <td style="white-space:nowrap">
          <button class="sk-btn sk-btn-ghost sk-btn-sm"
                  onclick="showDetailsFromRendered(${index})">
            <i class="bx bx-show"></i> View Application
          </button>
          <form method="POST" style="display:inline" onsubmit="return confirm('Delete this application?')">
            <input type="hidden" name="action"  value="delete">
            <input type="hidden" name="app_id"  value="${parseInt(app.ID)}">
            <button type="submit" class="sk-btn sk-btn-danger sk-btn-sm"><i class="bx bx-trash"></i></button>
          </form>
        </td>
      </tr>`;
  }).join('');
}

function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showDetailsFromRendered(index) {
  const app = currentTableData[index];
  if (!app) return;
  showDetails(parseInt(app.ID), app.opportunity_title || 'N/A', app.motivation || '', app.CV || '');
}

function showDetails(id, opportunity, motivation, cv) {
  document.getElementById('modalOpportunity').textContent = opportunity;
  document.getElementById('modalMotivation').textContent  = motivation || '(none)';
  const cvEl = document.getElementById('modalCV');
  if (cv) {
    cvEl.innerHTML = `<a href="${esc(cv)}" target="_blank" rel="noopener" style="color:var(--sk-accent)">${esc(cv)}</a>`;
  } else {
    cvEl.innerHTML = '<span style="color:var(--sk-muted)">No CV provided</span>';
  }
  document.getElementById('detailsModal').classList.add('open');
}

function closeModal() {
  document.getElementById('detailsModal').classList.remove('open');
}

function resetFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('sortBy').value      = 'DateCondidature';
  document.getElementById('sortOrder').value   = 'DESC';
  document.getElementById('typeFilter').value = '';
  performSearch();
}

// Event listeners
document.getElementById('searchInput').addEventListener('input',  scheduleSearch);
document.getElementById('sortBy').addEventListener('change',      performSearch);
document.getElementById('sortOrder').addEventListener('change',   performSearch);
document.getElementById('typeFilter').addEventListener('change', performSearch);

// Close modal on backdrop click
document.getElementById('detailsModal').addEventListener('click', e => {
  if (e.target === document.getElementById('detailsModal')) closeModal();
});

// Auto-dismiss toasts
const toast = document.getElementById('sk-toast');
if (toast) {
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .4s'; setTimeout(() => toast.remove(), 400); }, 4000);
}
</script>
</body>
</html>




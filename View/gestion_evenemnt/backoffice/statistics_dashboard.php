<?php
session_start();
include_once(__DIR__ . '/../../../config.php');
include_once(__DIR__ . '/../../../Controller/gestion_evenemnt/EvenementController.php');

$controller = new EvenementController();
$stats = $controller->getStatistics();

$typeLabels = [
    'workshop'   => 'Workshop',
    'conference' => 'Conférence',
    'seminaire'  => 'Séminaire',
    'hackathon'  => 'Hackathon',
    'formation'  => 'Formation',
    'webinar'    => 'Webinaire',
    'autre'      => 'Autre'
];
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
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>Dashboard - Analytics | Sneat - Bootstrap 5 HTML Admin Template - Pro</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />

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

  <body>
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
              <a href="../../acceuill/Backoffice.php" class="menu-link">
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
            <li class="menu-item active">
              <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                <div>Event Management</div>
              </a>

              <ul class="menu-sub list-unstyled">

                <li class="menu-item">
                  <a href="backoffice_evenements.php" class="menu-link">
                    <div>Events</div>
                  </a>
                </li>

                <li class="menu-item active">
                  <a href="statistics_dashboard.php" class="menu-link">
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
            <li class="menu-item">
              <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-book-open"></i>
                <div>Training Management</div>
              </a>

              <ul class="menu-sub list-unstyled">

                <li class="menu-item">
                  <a href="../gestion_formation/BackOffice/dashboard.php" class="menu-link">
                    <div>Dashboard</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_formation/BackOffice/Formation/consulterformations.php" class="menu-link">
                    <div>View Trainings</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_formation/BackOffice/Formation/ajouterformation.php" class="menu-link">
                    <div>Add Training</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_formation/BackOffice/Chapitre/consulterchapitres.php" class="menu-link">
                    <div>Chapters</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_formation/BackOffice/Chapitre/ajouterchapitre.php" class="menu-link">
                    <div>Add Chapter</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_formation/BackOffice/Test/consultertests.php" class="menu-link">
                    <div>Tests</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../gestion_formation/BackOffice/Test/ajoutertest.php" class="menu-link">
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
          <!-- Navbar -->
<!-- Navbar -->
<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar"
>
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    
    <!-- Search -->
    <div class="navbar-nav align-items-center">
      <div class="nav-item d-flex align-items-center">
        <i class="bx bx-search fs-4 lh-0"></i>
          <input
          type="text"
          id="searchInput"
          class="form-control border-0 shadow-none"
          placeholder="Search..."
        >
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">
      
      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
          <button
          class="border-0 bg-transparent p-0"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasEnd"
          aria-controls="offcanvasEnd"
          style="transition:0.3s;"
          onmouseover="this.style.transform='scale(1.08)'"
          onmouseout="this.style.transform='scale(1)'"
        >
          <div class="avatar avatar-online">
            <img
              src="../../../assetsjs/../assets/img/avatars/1.png"
              alt="Profile"
              class="w-px-40 h-auto rounded-circle"
            />
          </div>
        </button>
      </li>
      <!--/ User -->

    </ul>
  </div>
</nav>
<!-- ================= OFFCANVAS PROFILE ================= -->
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
        src="../../../assetsjs/../assets/img/avatars/1.png"
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

          <a href="consulterformations.php"
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


          
<!-- Content wrapper -->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- TITLE -->
    <div class="mb-4">
      <h3 class="fw-bold">
        <i class="bi bi-bar-chart-line me-2 text-primary"></i>
        Tableau de Bord Statistiques
      </h3>
      <p class="text-muted">
        Bienvenue dans votre centre de contrôle • Mise à jour en temps réel
      </p>
    </div>

    <!-- KPI ROW 1 -->
    <div class="row g-3 mb-4">

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <small class="text-muted">Événements Total</small>
            <h3 class="fw-bold"><?= $stats['totalEvents'] ?></h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <small class="text-muted">Inscriptions Total</small>
            <h3 class="fw-bold"><?= $stats['totalRegistrations'] ?></h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <small class="text-muted">Événements à Venir</small>
            <h3 class="fw-bold"><?= $stats['upcomingEvents'] ?></h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <small class="text-muted">Événements Passés</small>
            <h3 class="fw-bold"><?= $stats['pastEvents'] ?></h3>
          </div>
        </div>
      </div>

    </div>

    <!-- KPI ROW 2 -->
    <div class="row g-3 mb-4">

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <small class="text-muted">Confirmés</small>
            <h3 class="fw-bold text-danger">
              <?= $stats['registrationsByStatus']['confirmé'] ?? 0 ?>
            </h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <small class="text-muted">Inscrits</small>
            <h3 class="fw-bold text-success">
              <?= $stats['registrationsByStatus']['inscrit'] ?? 0 ?>
            </h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <small class="text-muted">Annulés</small>
            <h3 class="fw-bold">
              <?= $stats['registrationsByStatus']['annulé'] ?? 0 ?>
            </h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <small class="text-muted">Moy. / Événement</small>
            <h3 class="fw-bold text-info">
              <?= $stats['avgRegistrationsPerEvent'] ?>
            </h3>
          </div>
        </div>
      </div>

    </div>

    <!-- CHARTS -->
    <div class="row g-3 mb-4">

      <div class="col-lg-6">
        <div class="card shadow-sm p-3">
          <h6 class="fw-bold mb-3">Événements par Statut</h6>
          <canvas id="statusChart"></canvas>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card shadow-sm p-3">
          <h6 class="fw-bold mb-3">Événements par Type</h6>
          <canvas id="typeChart"></canvas>
        </div>
      </div>

    </div>

    <!-- TOP EVENTS -->
    <div class="card shadow-sm">
      <div class="card-body">

        <h6 class="fw-bold mb-4">
          <i class="bi bi-trophy me-1"></i>
          Top 5 Événements les Plus Populaires
        </h6>

        <?php if (!empty($stats['popularEvents'])): ?>
          <?php foreach ($stats['popularEvents'] as $idx => $event): ?>

            <div class="d-flex justify-content-between align-items-center border-bottom py-3">

              <div class="d-flex align-items-center gap-3">

                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                     style="width:40px;height:40px;">
                  <?= $idx + 1 ?>
                </div>

                <div>
                  <div class="fw-bold"><?= htmlspecialchars($event['Titre']) ?></div>
                  <small class="text-muted">
                    <?= $typeLabels[$event['Type']] ?? $event['Type'] ?>
                    • <?= date('d/m/Y', strtotime($event['dateEvent'])) ?>
                  </small>
                </div>

              </div>

              <div class="text-end">
                <div class="fw-bold text-primary">
                  <?= intval($event['reg_count'] ?? 0) ?>
                </div>
                <small class="text-muted">inscriptions</small>
              </div>

            </div>

          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center text-muted py-4">
            Aucun événement enregistré
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>
<!-- / Layout container ------------------------------------------------------------------------------------------------->
          
    <!-- build:js ../../assets/vendor/js/core.js -->
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js">
    </script><script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script>
  // STATUS CHART
  new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
      labels: ['Ouvert', 'Fermé', 'Complet'],
      datasets: [{
        data: [
          <?= $stats['eventsByStatus']['ouvert'] ?>,
          <?= $stats['eventsByStatus']['ferme'] ?>,
          <?= $stats['eventsByStatus']['complet'] ?>
        ],
        backgroundColor: ['#28a745', '#dc3545', '#ffc107']
      }]
    }
  });

  // TYPE CHART
  new Chart(document.getElementById('typeChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_map(fn($t) => $typeLabels[$t] ?? $t, array_keys($stats['eventsByType']))) ?>,
      datasets: [{
        label: 'Événements',
        data: <?= json_encode(array_values($stats['eventsByType'])) ?>,
        backgroundColor: '#0d6efd'
      }]
    },
    options: {
      responsive: true
    }
  });
</script>
</body>
<?php

include_once __DIR__ . '/../../../Controller/gestion_sponsor/sponsoringController.php';
include_once __DIR__ . '/../../../Controller/gestion_sponsor/produitController.php';


$view = isset($_GET['view']) ? $_GET['view'] : 'statistics';
$produitC = new ProduitController();
$sponsoringC = new SponsoringController();

$produits = [];
$sponsors = [];
$statistics = [];

if ($view === 'products') {
    $produits_stmt = $produitC->listProduits();
    $produits = $produits_stmt->fetchAll();
} elseif ($view === 'sponsors') {
    $sponsors_stmt = $sponsoringC->listSponsoring();
    $sponsors = $sponsors_stmt->fetchAll();
} elseif ($view === 'statistics') {
    $produits_stmt = $produitC->listProduits();
    $sponsors_stmt = $sponsoringC->listSponsoring();
    
    // Fetch data from PDOStatement objects
    $produits = $produits_stmt->fetchAll();
    $sponsors = $sponsors_stmt->fetchAll();
    
    // Calculate statistics
    $statistics = [
        'total_products' => count($produits),
        'total_sponsors' => count($sponsors),
        'total_value' => array_sum(array_column($produits, 'prix')),
        'avg_product_price' => count($produits) > 0 ? array_sum(array_column($produits, 'prix')) / count($produits) : 0,
        'product_categories' => array_count_values(array_column($produits, 'categrie')),
        'active_sponsors' => 0,
        'expired_sponsors' => 0
    ];
    
    // Count active and expired sponsors
    $current_date = date('Y-m-d');
    foreach ($sponsors as $sponsor) {
        if ($sponsor['date_fin'] >= $current_date) {
            $statistics['active_sponsors']++;
        } else {
            $statistics['expired_sponsors']++;
        }
    }
}

// Determine title and breadcrumb based on view
$pageTitle = "Book Store Dashboard";
$breadcrumbText = "Book Store";
switch($view) {
    case 'products':
        $pageTitle = "Product Catalogue";
        $breadcrumbText = "Products";
        break;
    case 'sponsors':
        $pageTitle = "Sponsors Management";
        $breadcrumbText = "Sponsors";
        break;
    case 'statistics':
        $pageTitle = "Tableau de Bord Statistiques";
        $breadcrumbText = "Statistiques";
        break;
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
  data-assets-path="../../assets/"
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
         <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
    <title>Skiller | Dashboard</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/lineicons.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
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
                  <a href="../dashboard.php" class="menu-link">
                    <div>Dashboard</div>
                  </a>
                </li>

                <li class="menu-item active">
                  <a href="consulterformations.php" class="menu-link">
                    <div>View Trainings</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="ajouterformation.php" class="menu-link">
                    <div>Add Training</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Chapitre/consulterchapitres.php" class="menu-link">
                    <div>Chapters</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="../Chapitre/ajouterchapitre.php" class="menu-link">
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
      <header class="header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-5 col-md-5 col-6">
              <div class="header-left d-flex align-items-center">
                <div class="menu-toggle-btn mr-15">
                  <button id="menu-toggle" class="main-btn danger-btn btn-hover">
                    <i class="lni lni-chevron-left me-2"></i> Menu
                  </button>
                </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-7 col-6">
              <div class="header-right">
                
              
                <div class="profile-box ml-15">
                  <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-info">
                      <div class="info">
                        <div class="image">
                          <img src="assets/images/profile/profile-image.png" alt="" />
                        </div>
                        <div>
                          <p>Admin</p>
                        </div>
                      </div>
                    </div>
                  </button>
                 
                </div>
                <!-- profile end -->
              </div>
            </div>
          </div>
        </div>
      </header>
      <!-- ========== header end ========== -->

      <!-- ========== section start ========== -->
      <section class="section">
        <div class="container-fluid">
          <!-- ========== title-wrapper start ========== -->
          <div class="title-wrapper pt-30">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="title">
                  <h2><?php echo $pageTitle; ?></h2>
                </div>
              </div>
              <!-- end col -->
              <?php if ($view === 'statistics'): ?>
              <div class="col-md-6">
                <div class="text-end">
                  <button class="main-btn primary-btn btn-hover" onclick="exportToPDF('statistics')">
                    <i class="lni lni-download"></i> Exporter PDF
                  </button>
                  <small class="text-muted d-block mt-1">Ouvre une fenêtre avec dialogue d'impression PDF</small>
                </div>
              </div>
              <?php endif; ?>
              <!-- end col -->
              <div class="col-md-6">
                <div class="breadcrumb-wrapper">
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item active" aria-current="page">
                        <?php echo $breadcrumbText; ?>
                      </li>
                    </ol>
                  </nav>
                </div>
              </div>
              <!-- end col -->
            </div>
            <!-- end row -->
          </div>
          <!-- ========== title-wrapper end ========== -->

          <?php if ($view === 'products'): ?>
          <!-- ========== PRODUCTS VIEW ========== -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card-style mb-30">
                <div class="title d-flex flex-wrap justify-content-between align-items-center">
                  <div class="left">
                    <h6 class="text-medium mb-30">Products List</h6>
                  </div>
                </div>
                <!-- End Title -->
                <div class="mb-4">
                  <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                      <label for="produit-search-category" class="form-label">Rechercher par</label>
                      <select id="produit-search-category" class="form-select">
                        <option value="nom">Nom du produit</option>
                        <option value="categrie">Catégorie</option>
                        <option value="prix">Prix</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="produit-search-input" class="form-label">Recherche</label>
                      <input type="text" id="produit-search-input" class="form-control" placeholder="Rechercher..." />
                    </div>
                    <div class="col-md-3">
                      <label for="produit-sort-select" class="form-label">Trier par</label>
                      <select id="produit-sort-select" class="form-select">
                        <option value="none">Aucun</option>
                        <option value="id_p_asc">ID Produit asc</option>
                        <option value="id_p_desc">ID Produit desc</option>
                        <option value="prix_asc">Prix asc</option>
                        <option value="prix_desc">Prix desc</option>
                      </select>
                    </div>
                    <div class="col-md-2 d-grid">
                      <button type="button" id="produit-search-clear" class="btn btn-outline-secondary">Effacer</button>
                    </div>
                  </div>
                </div>
                <div class="table-responsive">
                  <table id="produitTable" class="table top-selling-table">
                    <thead>
                      <tr>
                        <th class="min-width">
                          <h6 class="text-sm text-medium">ID</h6>
                        </th>
                        <th>
                          <h6 class="text-sm text-medium">Nom</h6>
                        </th>
                        <th>
                          <h6 class="text-sm text-medium">Catégorie</h6>
                        </th>
                        <th>
                          <h6 class="text-sm text-medium">Prix</h6>
                        </th>
                        <th>
                          <h6 class="text-sm text-medium">Description</h6>
                        </th>
                        <th>
                          <h6 class="text-sm text-medium">Image</h6>
                        </th>
                        <th>
                          <h6 class="text-sm text-medium">ID Sponsor</h6>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        foreach($produits as $produit)
                        {
                      ?>
                      <tr>
                        <td>
                          <p class="text-sm"><?php echo $produit['id_p']; ?></p>
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $produit['nom']; ?></p>
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $produit['categrie']; ?></p>
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $produit['prix']; ?> TND</p>
                        </td>
                        <td>
                          <p class="text-sm"><?php echo substr($produit['description'], 0, 50) . '...'; ?></p>
                        </td>
                        <td>
                          <img src="<?php echo $produit['image']; ?>" alt="Product" style="height: 50px; width: auto; border-radius: 4px;">
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $produit['id_sp']; ?></p>
                        </td>
                      </tr>
                      <?php
                        }
                      ?>
                    </tbody>
                  </table>
                  <!-- End Table -->
                </div>
              </div>
            </div>
            <!-- End Col -->
          </div>
          <!-- End Row -->
          <?php elseif ($view === 'sponsors'): ?>
          <!-- ========== SPONSORS VIEW ========== -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card-style mb-30">
                <div class="title d-flex flex-wrap justify-content-between align-items-center">
                  <div class="left">
                    <h6 class="text-medium mb-30">Sponsors List</h6>
                  </div>
                </div>
                <!-- End Title -->
                <div class="mb-4">
                  <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                      <label for="sponsor-search-category" class="form-label">Rechercher par</label>
                      <select id="sponsor-search-category" class="form-select">
                        <option value="nom_ent">Nom Entreprise</option>
                        <option value="date_deb">Date Début</option>
                        <option value="date_fin">Date Fin</option>
                        <option value="mail_event">Email Event</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="sponsor-search-input" class="form-label">Recherche</label>
                      <input type="text" id="sponsor-search-input" class="form-control" placeholder="Rechercher..." />
                    </div>
                    <div class="col-md-3">
                      <label for="sponsor-sort-select" class="form-label">Trier par</label>
                      <select id="sponsor-sort-select" class="form-select">
                        <option value="none">Aucun</option>
                        <option value="id_sp_asc">ID Sponsor asc</option>
                        <option value="id_sp_desc">ID Sponsor desc</option>
                        <option value="date_deb_asc">Date début asc</option>
                        <option value="date_deb_desc">Date début desc</option>
                        <option value="date_fin_asc">Date fin asc</option>
                        <option value="date_fin_desc">Date fin desc</option>
                      </select>
                    </div>
                    <div class="col-md-2 d-grid">
                      <button type="button" id="sponsor-search-clear" class="btn btn-outline-secondary">Effacer</button>
                    </div>
                  </div>
                </div>
                <div class="table-responsive">
                  <table id="sponsorTable" class="table top-selling-table">
                    <thead>
                      <tr>
                        <th>
                          <h6 class="text-sm text-medium">ID</h6>
                        </th>
                        <th>
                          <h6 class="text-sm text-medium">User ID</h6>
                        </th>
                        <th class="min-width">
                          <h6 class="text-sm text-medium">Company Name</h6>
                        </th>
                        <th class="min-width">
                          <h6 class="text-sm text-medium">Company Logo</h6>
                        </th>
                        <th class="min-width">
                          <h6 class="text-sm text-medium">Start Date</h6>
                        </th>
                        <th class="min-width">
                          <h6 class="text-sm text-medium">End Date</h6>
                        </th>
                        <th class="min-width">
                          <h6 class="text-sm text-medium">Email</h6>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        foreach($sponsors as $sponsor)
                        {
                      ?>
                      <tr>
                        <td>
                          <p class="text-sm"><?php echo $sponsor['id_sp']; ?></p>
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $sponsor['id_u']; ?></p>
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $sponsor['nom_ent']; ?></p>
                        </td>
                        <td>
                          <img src="<?php echo $sponsor['logo_entp']; ?>" alt="Logo" style="height: 50px; width: auto; border-radius: 4px;">
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $sponsor['date_deb']; ?></p>
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $sponsor['date_fin']; ?></p>
                        </td>
                        <td>
                          <p class="text-sm"><?php echo $sponsor['mail_event']; ?></p>
                        </td>
                      </tr>
                      <?php
                        }
                      ?>
                    </tbody>
                  </table>
                  <!-- End Table -->
                </div>
              </div>
            </div>
            <!-- End Col -->
          </div>
          <!-- End Row -->
          <?php elseif ($view === 'statistics'): ?>
          <!-- ========== STATISTICS VIEW ========== -->
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon purple">
                  <i class="lni lni-cart-full"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Total Produits</h6>
                  <h3 class="text-bold mb-10"><?php echo $statistics['total_products']; ?></h3>
                  <p class="text-sm text-info">
                    <i class="lni lni-package"></i> Articles Disponibles
                  </p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon success">
                  <i class="lni lni-dollar"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Valeur Totale</h6>
                  <h3 class="text-bold mb-10"><?php echo number_format($statistics['total_value'], 2); ?> TND</h3>
                  <p class="text-sm text-success">
                    <i class="lni lni-calculator"></i> Valeur du Stock
                  </p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon primary">
                  <i class="lni lni-users"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Total Sponsors</h6>
                  <h3 class="text-bold mb-10"><?php echo $statistics['total_sponsors']; ?></h3>
                  <p class="text-sm text-primary">
                    <i class="lni lni-handshake"></i> Entreprises Partenaires
                  </p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon orange">
                  <i class="lni lni-coin"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Prix Moyen</h6>
                  <h3 class="text-bold mb-10"><?php echo number_format($statistics['avg_product_price'], 2); ?> TND</h3>
                  <p class="text-sm text-warning">
                    <i class="lni lni-bar-chart"></i> Prix Moyen
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Sponsor Status Row -->
          <div class="row">
            <div class="col-lg-6">
              <div class="card-style mb-30">
                <div class="title">
                  <h6 class="text-medium mb-15">Statut des Sponsors</h6>
                </div>
                <div class="sponsor-stats">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-sm">Sponsors Actifs</span>
                    <span class="badge bg-success"><?php echo $statistics['active_sponsors']; ?></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-sm">Sponsors Expirés</span>
                    <span class="badge bg-danger"><?php echo $statistics['expired_sponsors']; ?></span>
                  </div>
                  <div class="progress mb-3" style="height: 8px;">
                    <?php 
                    $total_sponsors = $statistics['total_sponsors'];
                    $active_percentage = $total_sponsors > 0 ? ($statistics['active_sponsors'] / $total_sponsors) * 100 : 0;
                    ?>
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $active_percentage; ?>%;" aria-valuenow="<?php echo $active_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <p class="text-sm text-gray"><?php echo round($active_percentage, 1); ?>% des sponsors sont actifs</p>
                </div>
              </div>
            </div>
            
            <!-- Product Categories -->
            <div class="col-lg-6">
              <div class="card-style mb-30">
                <div class="title">
                  <h6 class="text-medium mb-15">Catégories de Produits</h6>
                </div>
                <div class="category-stats">
                  <?php if (!empty($statistics['product_categories'])): ?>
                    <?php foreach ($statistics['product_categories'] as $category => $count): ?>
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-sm"><?php echo htmlspecialchars($category); ?></span>
                        <span class="badge bg-info"><?php echo $count; ?></span>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="text-sm text-gray">Aucun produit disponible</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Charts Row -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card-style mb-30">
                <div class="title">
                  <h6 class="text-medium mb-15">Aperçu des Statistiques</h6>
                </div>
                <div class="row">
                  <div class="col-md-6 text-center">
                    <div style="height: 250px; position: relative;">
                      <canvas id="sponsorChart"></canvas>
                    </div>
                    <p class="text-sm mt-2">Statut des Sponsors</p>
                  </div>
                  <div class="col-md-6 text-center">
                    <div style="height: 250px; position: relative;">
                      <canvas id="categoryChart"></canvas>
                    </div>
                    <p class="text-sm mt-2">Catégories</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php else: ?>
          <!-- ========== DEFAULT DASHBOARD VIEW ========== -->
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon purple">
                  <i class="lni lni-cart-full"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">New Orders</h6>
                  <h3 class="text-bold mb-10">34567</h3>
                  <p class="text-sm text-success">
                    <i class="lni lni-arrow-up"></i> +2.00%
                    <span class="text-gray">(30 days)</span>
                  </p>
                </div>
              </div>
              <!-- End Icon Cart -->
            </div>
            <!-- End Col -->
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon success">
                  <i class="lni lni-dollar"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Total Income</h6>
                  <h3 class="text-bold mb-10">$74,567</h3>
                  <p class="text-sm text-success">
                    <i class="lni lni-arrow-up"></i> +5.45%
                    <span class="text-gray">Increased</span>
                  </p>
                </div>
              </div>
              <!-- End Icon Cart -->
            </div>
            <!-- End Col -->
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon primary">
                  <i class="lni lni-credit-cards"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Total Expense</h6>
                  <h3 class="text-bold mb-10">$24,567</h3>
                  <p class="text-sm text-danger">
                    <i class="lni lni-arrow-down"></i> -2.00%
                    <span class="text-gray">Expense</span>
                  </p>
                </div>
              </div>
              <!-- End Icon Cart -->
            </div>
            <!-- End Col -->
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon orange">
                  <i class="lni lni-user"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">New User</h6>
                  <h3 class="text-bold mb-10">34567</h3>
                  <p class="text-sm text-danger">
                    <i class="lni lni-arrow-down"></i> -25.00%
                    <span class="text-gray"> Earning</span>
                  </p>
                </div>
              </div>
              <!-- End Icon Cart -->
            </div>
            <!-- End Col -->
          </div>
          <!-- End Row -->
          <div style="text-align: center; padding: 40px 0;">
            <p class="text-medium">Select a management option from the sidebar to view catalogues.</p>
          </div>
          <?php endif; ?>
        </div>
        <!-- end container -->
      </section>
      <!-- ========== section end ========== -->

      <!-- ========== footer start =========== -->
      <footer class="footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-6 order-last order-md-first">
              <div class="copyright text-center text-md-start">
                <p class="text-sm">
                  © 2024 Skiller. All rights reserved.
                </p>
              </div>
            </div>
            <!-- end col-->
            <div class="col-md-6">
              <div class="terms d-flex justify-content-center justify-content-md-end">
                <a href="#0" class="text-sm">Documentation</a>
                <a href="#0" class="text-sm ml-15">Support</a>
              </div>
            </div>
          </div>
          <!-- end row -->
        </div>   
      </div>
      <!-- / Layout wrapper -->
    <!-- Core JS -->
     <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/Chart.min.js"></script>
        <script src="assets/js/apexcharts.min.js"></script>
        <script src="assets/js/fullcalendar.min.js"></script>
        <script src="assets/js/jvectormap.min.js"></script>
        <script src="assets/js/world-merc.js"></script>
        <script src="assets/js/polyfill.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script src="assets/js/main.js"></script>
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
          function updateSponsorSearchInputType() {
            const category = document.getElementById('sponsor-search-category');
            const input = document.getElementById('sponsor-search-input');
            if (!category || !input) return;

            if (category.value === 'date_deb' || category.value === 'date_fin') {
              input.type = 'date';
              input.placeholder = 'Sélectionner une date';
            } else {
              input.type = 'text';
              input.placeholder = 'Rechercher ' + (category.value === 'nom_ent' ? 'un nom' : 'un email');
            }
          }

          function filterSponsorTable() {
            const category = document.getElementById('sponsor-search-category')?.value;
            const query = document.getElementById('sponsor-search-input')?.value.trim().toLowerCase() || '';
            const rows = document.querySelectorAll('#sponsorTable tbody tr');
            if (!category || !rows.length) return;

            const columnIndex = {
              id_sp: 0,
              id_u: 1,
              nom_ent: 2,
              date_deb: 4,
              date_fin: 5,
              mail_event: 6
            }[category];

            rows.forEach(row => {
              const cell = row.children[columnIndex];
              const cellText = cell ? cell.innerText.trim().toLowerCase() : '';
              if (!query || cellText.includes(query)) {
                row.style.display = '';
              } else {
                row.style.display = 'none';
              }
            });
            sortSponsorTable();
          }

      function sortSponsorTable() {
  const sortValue = document.getElementById('sponsor-sort-select')?.value;
  const tbody = document.querySelector('#sponsorTable tbody');
  if (!tbody) return;

  const rows = Array.from(tbody.querySelectorAll('tr'));
  if (!sortValue || sortValue === 'none') return;

  const parts = sortValue.split('_');
  const direction = parts.pop();
  const field = parts.join('_');
  const columnIndex = {
    id_sp: 0,
    date_deb: 4,
    date_fin: 5
  }[field] ?? 0;

  rows.sort((a, b) => {
    const aText = a.children[columnIndex]?.innerText.trim() || '';
    const bText = b.children[columnIndex]?.innerText.trim() || '';

    if (field === 'id_sp') {
      const aNum = parseInt(aText, 10) || 0;
      const bNum = parseInt(bText, 10) || 0;
      return direction === 'asc' ? aNum - bNum : bNum - aNum;
    }

    const aDate = new Date(aText);
    const bDate = new Date(bText);
    if (isNaN(aDate) || isNaN(bDate)) {
      return 0;
    }
    return direction === 'asc' ? aDate - bDate : bDate - aDate;
  });

  rows.forEach(row => tbody.appendChild(row));
}

      function setupSponsorSearchControls() {
  const category = document.getElementById('sponsor-search-category');
  const input = document.getElementById('sponsor-search-input');
  const sortSelect = document.getElementById('sponsor-sort-select');
  const clearBtn = document.getElementById('sponsor-search-clear');
  if (!category || !input || !sortSelect || !clearBtn) return;

  category.addEventListener('change', () => {
    updateSponsorSearchInputType();
    filterSponsorTable();
  });
  input.addEventListener('input', filterSponsorTable);
  sortSelect.addEventListener('change', filterSponsorTable);
  clearBtn.addEventListener('click', () => {
    input.value = '';
    sortSelect.value = 'none';
    filterSponsorTable();
  });
  updateSponsorSearchInputType();
}

      function updateProduitSearchInputType() {
  const category = document.getElementById('produit-search-category');
  const input = document.getElementById('produit-search-input');
  if (!category || !input) return;

  input.type = category.value === 'prix' ? 'number' : 'text';
  if (category.value === 'prix') {
    input.placeholder = 'Prix';
  } else if (category.value === 'categrie') {
    input.placeholder = 'Catégorie';
  } else {
    input.placeholder = 'Recherche...';
  }
}

      function filterProduitTable() {
  const category = document.getElementById('produit-search-category')?.value;
  const rawValue = document.getElementById('produit-search-input')?.value.trim();
  const rows = document.querySelectorAll('#produitTable tbody tr');
  if (!category || !rows.length) return;

  const columnIndex = category === 'prix' ? 3 : category === 'categrie' ? 2 : 1;

  rows.forEach(row => {
    const cell = row.children[columnIndex];
    const cellText = cell ? cell.innerText.trim().toLowerCase() : '';
    let match = false;

    if (!rawValue) {
      match = true;
    } else if (category === 'prix') {
      const queryNumber = parseFloat(rawValue.replace(',', '.'));
      const cellNumber = parseFloat(cellText.replace(',', '.'));
      match = !isNaN(queryNumber) && !isNaN(cellNumber) && cellNumber === queryNumber;
    } else {
      match = cellText.includes(rawValue.toLowerCase());
    }

    row.style.display = match ? '' : 'none';
  });
  sortProduitTable();
}

      function sortProduitTable() {
  const sortValue = document.getElementById('produit-sort-select')?.value;
  const tbody = document.querySelector('#produitTable tbody');
  if (!tbody) return;

  const rows = Array.from(tbody.querySelectorAll('tr'));
  if (!sortValue || sortValue === 'none') return;

  const parts = sortValue.split('_');
  const direction = parts.pop();
  const field = parts.join('_');
  const columnIndex = field === 'id_p' ? 0 : field === 'prix' ? 3 : 0;

  rows.sort((a, b) => {
    const aText = a.children[columnIndex]?.innerText.trim() || '';
    const bText = b.children[columnIndex]?.innerText.trim() || '';

    if (field === 'id_p') {
      const aNum = parseInt(aText, 10) || 0;
      const bNum = parseInt(bText, 10) || 0;
      return direction === 'asc' ? aNum - bNum : bNum - aNum;
    }

    const aNum = parseFloat(aText.replace(',', '.')) || 0;
    const bNum = parseFloat(bText.replace(',', '.')) || 0;
    return direction === 'asc' ? aNum - bNum : bNum - aNum;
  });

  rows.forEach(row => tbody.appendChild(row));
}

      function setupProduitSearchControls() {
  const category = document.getElementById('produit-search-category');
  const input = document.getElementById('produit-search-input');
  const sortSelect = document.getElementById('produit-sort-select');
  const clearBtn = document.getElementById('produit-search-clear');
  if (!category || !input || !sortSelect || !clearBtn) return;

  category.addEventListener('change', () => {
    updateProduitSearchInputType();
    filterProduitTable();
  });
  input.addEventListener('input', filterProduitTable);
  sortSelect.addEventListener('change', filterProduitTable);
  clearBtn.addEventListener('click', () => {
    input.value = '';
    sortSelect.value = 'none';
    filterProduitTable();
  });
  updateProduitSearchInputType();
}

      document.addEventListener('DOMContentLoaded', function() {
        setupSponsorSearchControls();
        setupProduitSearchControls();
        
        // Initialize statistics charts if on statistics view
        var isStatisticsView = <?php echo ($view === 'statistics') ? 'true' : 'false'; ?>;
        if (isStatisticsView) {
          initializeStatisticsCharts();
        }
      });

      function initializeStatisticsCharts() {
        try {
          // Sponsor Status Chart
          const sponsorCtx = document.getElementById('sponsorChart');
          if (sponsorCtx) {
            const activeSponsors = <?php echo $statistics['active_sponsors']; ?>;
            const expiredSponsors = <?php echo $statistics['expired_sponsors']; ?>;
            
            new Chart(sponsorCtx, {
              type: 'pie',
              data: {
                labels: ['Sponsors Actifs', 'Sponsors Expirés'],
                datasets: [{
                  data: [activeSponsors, expiredSponsors],
                  backgroundColor: ['#10b981', '#ef4444'],
                  borderWidth: 0
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                  legend: {
                    position: 'bottom',
                    labels: {
                      padding: 8,
                      font: {
                        size: 10
                      }
                    }
                  }
                }
              }
            });
          }

          // Categories Chart
          const categoryCtx = document.getElementById('categoryChart');
          if (categoryCtx) {
            const categoryLabels = <?php echo json_encode(array_keys($statistics['product_categories'])); ?>;
            const categoryData = <?php echo json_encode(array_values($statistics['product_categories'])); ?>;
            
            // Ensure we have valid data
            const labels = Array.isArray(categoryLabels) && categoryLabels.length > 0 ? categoryLabels : ['Aucune Donnée'];
            const data = Array.isArray(categoryData) && categoryData.length > 0 ? categoryData : [0];
            
            new Chart(categoryCtx, {
              type: 'bar',
              data: {
                labels: labels,
                datasets: [{
                  label: 'Produits par Catégorie',
                  data: data,
                  backgroundColor: '#6366f1',
                  borderWidth: 0
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                  y: {
                    beginAtZero: true,
                    max: Math.max(...data) + 1,
                    ticks: {
                      stepSize: 1
                    }
                  }
                },
                plugins: {
                  legend: {
                    display: false
                  }
                }
              }
            });
          }
        } catch (error) {
          console.error('Error initializing charts:', error);
        }
      }

      // PDF Export Function using html2pdf.js
      function exportToPDF(type) {
        console.log('Exporting PDF for type:', type);
        try {
          // Fetch the HTML content from export_pdf.php
          fetch('export_pdf.php?type=' + type)
            .then(response => response.text())
            .then(html => {
              // Configure html2pdf options
              const opt = {
                margin: 10,
                filename: 'skiller_' + type + '_' + new Date().toISOString().split('T')[0] + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
              };
              
              // Generate PDF
              html2pdf().set(opt).from(html).save();
            })
            .catch(error => {
              console.error('Error fetching HTML content:', error);
              alert('Erreur lors de la récupération du contenu: ' + error.message);
            });
        } catch (error) {
          console.error('Error exporting PDF:', error);
          alert('Erreur lors de l\'exportation PDF: ' + error.message);
        }
      }

          </script>
</body>
</html>
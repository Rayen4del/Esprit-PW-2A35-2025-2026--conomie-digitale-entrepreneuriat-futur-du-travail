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
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
    <title>Skiller | Dashboard</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/lineicons.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
  </head>
  <body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->

    <!-- ======== sidebar-nav start =========== -->
    <aside class="sidebar-nav-wrapper">
      <div class="navbar-logo">
        <div style="display: flex; align-items: center; cursor: default;">
          <div style="margin-right: 10px;">
            <svg viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" width="30" height="30">
              <path d="M12.5 2L22 7V18L12.5 23L3 18V7L12.5 2Z" fill="#696cff" fill-opacity=".15" stroke="#696cff" stroke-width="1.5"/>
              <path d="M12.5 7L17 9.5V14.5L12.5 17L8 14.5V9.5L12.5 7Z" fill="#696cff"/>
            </svg>
          </div>
          <span style="font-size: 1.25rem; font-weight: 700; color: #566a7f;">Skiller</span>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li class="nav-item nav-item-has-children">
            <a
              href="#0"
              class="collapsed"
              data-bs-toggle="collapse"
              data-bs-target="#ddmenu_6"
              aria-controls="ddmenu_6"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
             <span class="icon">
  <svg width="20" height="20" viewBox="0 0 20 20" 
       xmlns="http://www.w3.org/2000/svg">
    <path
      d="M4.16666 3.33335C4.16666 2.41288 4.91285 1.66669 5.83332 1.66669H14.1667C15.0872 1.66669 15.8333 2.41288 15.8333 3.33335V16.6667C15.8333 17.5872 15.0872 18.3334 14.1667 18.3334H5.83332C4.91285 18.3334 4.16666 17.5872 4.16666 16.6667V3.33335ZM6.04166 5.00002C6.04166 5.3452 6.32148 5.62502 6.66666 5.62502H13.3333C13.6785 5.62502 13.9583 5.3452 13.9583 5.00002C13.9583 4.65485 13.6785 4.37502 13.3333 4.37502H6.66666C6.32148 4.37502 6.04166 4.65485 6.04166 5.00002ZM6.66666 6.87502C6.32148 6.87502 6.04166 7.15485 6.04166 7.50002C6.04166 7.8452 6.32148 8.12502 6.66666 8.12502H13.3333C13.6785 8.12502 13.9583 7.8452 13.9583 7.50002C13.9583 7.15485 13.6785 6.87502 13.3333 6.87502H6.66666ZM6.04166 10C6.04166 10.3452 6.32148 10.625 6.66666 10.625H9.99999C10.3452 10.625 10.625 10.3452 10.625 10C10.625 9.65485 10.3452 9.37502 9.99999 9.37502H6.66666C6.32148 9.37502 6.04166 9.65485 6.04166 10ZM9.99999 16.6667C10.9205 16.6667 11.6667 15.9205 11.6667 15C11.6667 14.0795 10.9205 13.3334 9.99999 13.3334C9.07949 13.3334 8.33332 14.0795 8.33332 15C8.33332 15.9205 9.07949 16.6667 9.99999 16.6667Z"
      fill="red" />
  </svg>
</span>

              <span class="text"> Product Management </span>
            </a>
            <ul id="ddmenu_6" class="collapse dropdown-nav">
              <li>
                <a href="index.php?view=products" class="<?php echo ($view === 'products') ? 'active' : ''; ?>"> Product List</a>
              </li>
            </ul>
          </li>
          <li class="nav-item nav-item-has-children">
            <a
              href="#0"
              class="collapsed"
              data-bs-toggle="collapse"
              data-bs-target="#ddmenu_7"
              aria-controls="ddmenu_7"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
             <span class="icon">
  <svg width="20" height="20" viewBox="0 0 20 20" 
       xmlns="http://www.w3.org/2000/svg">
    <path
      d="M4.16666 3.33335C4.16666 2.41288 4.91285 1.66669 5.83332 1.66669H14.1667C15.0872 1.66669 15.8333 2.41288 15.8333 3.33335V16.6667C15.8333 17.5872 15.0872 18.3334 14.1667 18.3334H5.83332C4.91285 18.3334 4.16666 17.5872 4.16666 16.6667V3.33335ZM6.04166 5.00002C6.04166 5.3452 6.32148 5.62502 6.66666 5.62502H13.3333C13.6785 5.62502 13.9583 5.3452 13.9583 5.00002C13.9583 4.65485 13.6785 4.37502 13.3333 4.37502H6.66666C6.32148 4.37502 6.04166 4.65485 6.04166 5.00002ZM6.66666 6.87502C6.32148 6.87502 6.04166 7.15485 6.04166 7.50002C6.04166 7.8452 6.32148 8.12502 6.66666 8.12502H13.3333C13.6785 8.12502 13.9583 7.8452 13.9583 7.50002C13.9583 7.15485 13.6785 6.87502 13.3333 6.87502H6.66666ZM6.04166 10C6.04166 10.3452 6.32148 10.625 6.66666 10.625H9.99999C10.3452 10.625 10.625 10.3452 10.625 10C10.625 9.65485 10.3452 9.37502 9.99999 9.37502H6.66666C6.32148 9.37502 6.04166 9.65485 6.04166 10ZM9.99999 16.6667C10.9205 16.6667 11.6667 15.9205 11.6667 15C11.6667 14.0795 10.9205 13.3334 9.99999 13.3334C9.07949 13.3334 8.33332 14.0795 8.33332 15C8.33332 15.9205 9.07949 16.6667 9.99999 16.6667Z"
      fill="red" />
  </svg>
</span>

              <span class="text"> Sponsor Management </span>
            </a>
            <ul id="ddmenu_7" class="collapse dropdown-nav">
              <li>
                <a href="index.php?view=sponsors" class="<?php echo ($view === 'sponsors') ? 'active' : ''; ?>"> Sponsor List</a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="index.php?view=statistics" class="<?php echo ($view === 'statistics') ? 'active' : ''; ?>">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" 
                     xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M4.16666 3.33335C4.16666 2.41288 4.91285 1.66669 5.83332 1.66669H14.1667C15.0872 1.66669 15.8333 2.41288 15.8333 3.33335V16.6667C15.8333 17.5872 15.0872 18.3334 14.1667 18.3334H5.83332C4.91285 18.3334 4.16666 17.5872 4.16666 16.6667V3.33335ZM6.04166 5.00002C6.04166 5.3452 6.32148 5.62502 6.66666 5.62502H13.3333C13.6785 5.62502 13.9583 5.3452 13.9583 5.00002C13.9583 4.65485 13.6785 4.37502 13.3333 4.37502H6.66666C6.32148 4.37502 6.04166 4.65485 6.04166 5.00002ZM6.66666 6.87502C6.32148 6.87502 6.04166 7.15485 6.04166 7.50002C6.04166 7.8452 6.32148 8.12502 6.66666 8.12502H13.3333C13.6785 8.12502 13.9583 7.8452 13.9583 7.50002C13.9583 7.15485 13.6785 6.87502 13.3333 6.87502H6.66666ZM6.04166 10C6.04166 10.3452 6.32148 10.625 6.66666 10.625H9.99999C10.3452 10.625 10.625 10.3452 10.625 10C10.625 9.65485 10.3452 9.37502 9.99999 9.37502H6.66666C6.32148 9.37502 6.04166 9.65485 6.04166 10ZM9.99999 16.6667C10.9205 16.6667 11.6667 15.9205 11.6667 15C11.6667 14.0795 10.9205 13.3334 9.99999 13.3334C9.07949 13.3334 8.33332 14.0795 8.33332 15C8.33332 15.9205 9.07949 16.6667 9.99999 16.6667Z"
                    fill="blue" />
                </svg>
              </span>
              <span class="text">Statistics</span>
            </a>
          </li>
        </ul>
      </nav>
     
    </aside>
    <div class="overlay"></div>
    <!-- ======== sidebar-nav end =========== -->

    <!-- ======== main-wrapper start =========== -->
    <main class="main-wrapper">
      <!-- ========== header start ========== -->
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
        <!-- end container -->

        <!-- ========== All JS files linkup ========== -->
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/Chart.min.js"></script>
        <script src="assets/js/apexcharts.min.js"></script>
        <script src="assets/js/fullcalendar.min.js"></script>
        <script src="assets/js/jvectormap.min.js"></script>
        <script src="assets/js/world-merc.js"></script>
        <script src="assets/js/polyfill.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script src="assets/js/main.js"></script>
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

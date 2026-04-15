<?php
require_once("../../config.php");
require_once("../../controle/FormationController.php");

$controller = new FormationController($conn);
$result = $controller->list();
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Gestion Formation</title>

  <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
  <link rel="stylesheet" href="assets/vendor/css/core.css" />
  <link rel="stylesheet" href="assets/vendor/css/theme-default.css" />
  <link rel="stylesheet" href="assets/css/demo.css" />
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <script src="assets/vendor/js/helpers.js"></script>
  <script src="assets/js/config.js"></script>
</head>

<body>

<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- MENU -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

      <div class="app-brand demo">
        <a href="#" class="app-brand-link">
          <span class="app-brand-text menu-text fw-bolder ms-2">Gestion</span>
        </a>
      </div>

      <ul class="menu-inner py-1">
        <li class="menu-item">
          <a href="#" class="menu-link">
            <i class="menu-icon bx bx-home-circle"></i>
            <div>Dashboard</div>
          </a>
        </li>

        <li class="menu-item active open">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon bx bx-book"></i>
            <div>Formations</div>
          </a>

          <ul class="menu-sub">
            <li class="menu-item active">
              <a href="frontoffice.php" class="menu-link">
                <div>Front Office</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="backoffice.php" class="menu-link">
                <div>Back Office</div>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </aside>

    <!-- PAGE -->
    <div class="layout-page">

      <!-- NAVBAR -->
      <nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
          <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
          </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
          <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
              <i class="bx bx-search fs-4 lh-0"></i>
              <input type="text" class="form-control border-0 shadow-none" placeholder="Search..." />
            </div>
          </div>
        </div>
      </nav>

      <!-- CONTENT -->
      <div class="content-wrapper">
        <div class="container-fluid py-4">

          <h4 class="fw-bold mb-4">Liste des formations</h4>

          <div class="row">

            <?php while ($row = $result->fetch_assoc()) { ?>

              <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">

                  <img
                    src="uploads/<?= $row['image'] ?>"
                    class="card-img-top"
                    style="height: 220px; object-fit: cover;"
                  >

                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($row['titre']) ?></h5>

                    <p class="card-text text-muted mb-2">
                      <?= htmlspecialchars($row['description']) ?>
                    </p>

                    <p class="mb-1">
                      <strong>Domaine :</strong> <?= htmlspecialchars($row['domaine']) ?>
                    </p>

                    <p class="mb-1">
                      <strong>Date :</strong> <?= htmlspecialchars($row['date_creation']) ?>
                    </p>

                    <p class="mb-3">
                      <strong>Etat :</strong>
                      <span class="badge bg-primary">
                        <?= htmlspecialchars($row['etat']) ?>
                      </span>
                    </p>

                    <a href="#" class="btn btn-primary mt-auto">
                      Voir détails
                    </a>
                  </div>
                </div>
              </div>

            <?php } ?>

          </div>

        </div>
      </div>

    </div>
  </div>
</div>

</body>
</html>
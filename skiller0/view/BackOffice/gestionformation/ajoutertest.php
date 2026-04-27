
<?php
include_once __DIR__ . '/../../../Controller/TestController.php';
include_once __DIR__ . '/../../../Controller/QuestionController.php';
include __DIR__ . '/../../../Controller/FormationController.php';
include __DIR__ . '/../../../Controller/ChapitreController.php';
$formationC = new FormationController();
$formations = $formationC->listFormations();

$error = "";
$chapitreC = new ChapitreController();
$chapitres = $chapitreC->listChapitres();

$testC = new TestController();
$questionC = new QuestionController();

$tests = $testC->listTests();

$error = "";
$success = "";

/* =========================
   ENREGISTRER QUESTIONS
========================= */
if(isset($_POST['add_test'])) {

    $type = $_POST['type_test'];
    $id_f = isset($_POST['id_f']) ? (int) $_POST['id_f'] : null;
    $id_c = isset($_POST['id_c']) ? (int) $_POST['id_c'] : null;
    $score_min = isset($_POST['score_min']) ? (int) $_POST['score_min'] : null;
    $date_creation = $_POST['date_creation'];

    if (!$type || (!$id_f && !$id_c) || !$score_min || !$date_creation) {
        die("Tous les champs sont requis");
    }
    if (empty($type) || empty($score_min) || empty($date_creation)) {
    die("Champs obligatoires manquants");
}

if ($type == "niveau" && !$id_f) {
    die("Formation requise");
}

if ($type == "quiz" && !$id_c) {
    die("Chapitre requis");
}
$test = new Test(
    null,
    $id_c,   // chapitreId
    $id_f,   // formationId
    $score_min,
    new DateTime($date_creation)
);
    
    $testC->addTest($test);
}
if (isset($_POST['save_questions'])) {

    $id_test = isset($_POST['id_test']) ? (int) $_POST['id_test'] : null;

    if (!$id_test) {
        die("Test non sélectionné");
    }

          if (!empty($_POST['questions'])) {

            foreach ($_POST['questions'] as $q) {

          if (!isset($q['question'], $q['reponse'], $q['type'])) {
              continue;
          }

          $question = new Question(
              null,
              $id_test,
              $q['type'],
              $q['question'],
              $q['reponse']
          );

          $questionC->addQuestion($question);
      }

        $success = "Questions ajoutées avec succès";
    } else {
        $error = "Aucune question ajoutée";
    }
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
    <style>
#preview .item {
  width: 120px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  overflow: hidden;
  background: #f8f9fa;
  text-align: center;
}

#preview .item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

#preview .item video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Dashboard - Analytics | Sneat - Bootstrap 5 HTML Admin Template - Pro</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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

          <ul class="menu-inner py-1">
            

            <!-- Layouts -->
            <li class="menu-item active">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Gestion formations</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="dashboard.php" class="menu-link">
                    <div data-i18n="Without menu">dashbord</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="consulterformations.php" class="menu-link">
                    <div data-i18n="Without navbar">consulter formations</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ajouterformation.php" class="menu-link">
                    <div data-i18n="Without navbar">ajouter formations</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="consulterchapitres.php" class="menu-link">
                    <div data-i18n="Container">consulter chapitres</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ajouterchapitre.php" class="menu-link">
                    <div data-i18n="Without navbar">ajouter chapitre et son contenu</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="consultertests.php" class="menu-link">
                    <div data-i18n="Container">consulter tests</div>
                  </a>
                </li>
                <li class="menu-item active">
                  <a href="ajoutertest.php" class="menu-link">
                    <div data-i18n="Without navbar">ajouter test</div>
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
                    src="../../assets/img/avatars/1.png"
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
<!-- Offcanvas End -->
      <div
        class="offcanvas offcanvas-end"
        tabindex="-1"
        id="offcanvasEnd"
        aria-labelledby="offcanvasEndLabel"
      >
        <div class="offcanvas-header">
          <h5 id="offcanvasEndLabel" class="offcanvas-title">Mon Profil</h5>
          <button
            type="button"
            class="btn-close text-reset"
            data-bs-dismiss="offcanvas"
            aria-label="Close"
          ></button>
        </div>

        <div class="offcanvas-body">
          <div class="text-center mb-4">
            <img
              src="../../assets/img/avatars/1.png"
              alt="Profile"
              class="rounded-circle mb-3"
              width="80"
            />
            <h6 class="mb-1">Amen Allah</h6>
            <small class="text-muted">Utilisateur</small>
          </div>

          <div class="list-group">
            <a href="profile.php" class="list-group-item list-group-item-action">
              <i class="bx bx-user me-2"></i> Mon Profil
            </a>

            <a href="settings.php" class="list-group-item list-group-item-action">
              <i class="bx bx-cog me-2"></i> Paramètres
            </a>

            <a href="logout.php" class="list-group-item list-group-item-action text-danger">
              <i class="bx bx-log-out me-2"></i> Déconnexion
            </a>
          </div>
        </div>
      </div>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
              <div class="container-xxl flex-grow-1 container-p-y">
                <div class="row">

                  <!-- FORMULAIRE -->
                  <div class="col-12">
                    <div class="card mb-4">
                      <div class="card-header">
                        <h5 class="mb-0">test Chapitre</h5>
                      </div>

                      <div class="card-body">

                       <form method="POST">
                        <div class="mb-4">
                          <label class="form-label fw-bold">Type de test</label>

                          <div class="d-flex gap-4">

                            <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="type_test" value="niveau" id="niveau" checked>
                              <label class="form-check-label" for="niveau">
                                Test / Niveau
                              </label>
                            </div>

                            <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="type_test" value="quiz" id="quiz">
                              <label class="form-check-label" for="quiz">
                                Quiz / Compréhension
                              </label>
                            </div>

                          </div>
                        </div>
                         <div class="row g-3">

                          <!-- Formation -->
                          <div class="col-md-6">
                            <label class="form-label">Formation</label>
                            <select class="form-select" name="id_f" required id="formationSelect">
                              <option value="" disabled selected>Choisir une formation</option>

                              <?php foreach ($formations as $formation) { ?>
                                <option value="<?= $formation['id_f']; ?>">
                                  <?= $formation['id_f'] . ' | ' . $formation['titre']; ?>
                                </option>
                              <?php } ?>

                            </select>
                          </div>

                          <!-- Chapitre -->
                          <div class="col-md-6">
                            <label class="form-label">Chapitre</label>
                            <select class="form-select" name="id_c" required id="chapitreSelect">
                              <option value="" disabled selected>Choisir un chapitre</option>

                              <?php foreach ($chapitres as $c) { ?>
                                <option value="<?= $c['id_c'] ?>">
                                  #<?= $c['id_c'] ?> - <?= $c['titre_c'] ?>
                                </option>
                              <?php } ?>

                            </select>
                          </div>

                          <!-- Score -->
                          <div class="col-md-6">
                            <label class="form-label">Score minimum</label>
                            <input type="number" name="score_min" class="form-control" placeholder="Ex: 10" min="0" required>
                          </div>

                          <!-- Date -->
                          <div class="col-md-6">
                            <label class="form-label">Date de création</label>
                            <input type="date" name="date_creation" class="form-control" 
       value="<?= date('Y-m-d') ?>" readonly>
                          </div>

                          <!-- Button -->
                          <div class="col-12 text-end mt-2">
                            <button type="submit" name="add_test" class="btn btn-primary px-4">
                              Ajouter Test
                            </button>
                          </div>

                        </div>

                      </form>

                      <div class="card">
  <div class="card-header">
    <h5>Ajouter des questions à un test existant</h5>
  </div>

  <div class="card-body">

    <!-- CHOIX TEST -->
    <label class="form-label">Choisir un test</label>
    <select id="testSelect" class="form-select mb-3">
        <option disabled selected>Choisir un test</option>
        <?php foreach ($tests as $t) { ?>
            <option value="<?= $t['id_t'] ?>">
                Test #<?= $t['id_t'] ?> | Formation <?= $t['id_f'] ?> | Chapitre <?= $t['id_c'] ?>
            </option>
        <?php } ?>
    </select>

    <!-- NOMBRE QUESTIONS -->
    <label class="form-label">Nombre de questions</label>
    <input type="number" id="count" class="form-control mb-3" min="1">

    <button type="button" class="btn btn-primary mb-3" onclick="generate()">
        Générer
    </button>

    <!-- FORM QUESTIONS -->
    <form method="POST">

        <input type="hidden" name="id_test" id="id_test_hidden">

        <div id="container"></div>

        <button type="submit" name="save_questions" class="btn btn-success mt-3">
            Enregistrer
        </button>

    </form>

  </div>
</div>
                  </div>
                  <!____________________________________________________>
                

                  <!____________________________________________________>
                  
                </div>
                </div>
              </div>
          </div>
          <!-- / Content wrapper -->
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
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  </div>
</div>

  </div>
</div>
  </div>
</div>
</div>
<div class="modal fade" id="fileModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Aperçu fichier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center" id="fileViewer">
      </div>

    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const radioNiveau = document.getElementById("niveau");
    const radioQuiz = document.getElementById("quiz");

    const formation = document.getElementById("formationSelect");
    const chapitre = document.getElementById("chapitreSelect");

    function updateFields() {
        if (radioNiveau.checked) {
            formation.disabled = false;
            formation.required = true;

            chapitre.disabled = true;
            chapitre.required = false;
            chapitre.value = "";
        } else if (radioQuiz.checked) {
            chapitre.disabled = false;
            chapitre.required = true;

            formation.disabled = true;
            formation.required = false;
            formation.value = "";
        }
    }

    // Initial state
    updateFields();

    // Events
    radioNiveau.addEventListener("change", updateFields);
    radioQuiz.addEventListener("change", updateFields);
});
</script>
<script>
function generate() {

    const count = document.getElementById("count").value;
    const container = document.getElementById("container");

    const testId = document.getElementById("testSelect").value;
    document.getElementById("id_test_hidden").value = testId;

    container.innerHTML = "";

    for (let i = 0; i < count; i++) {

        container.innerHTML += `
            <div class="border p-3 mb-3">

                <h6>Question ${i + 1}</h6>

                <input type="text"
                       name="questions[${i}][question]"
                       class="form-control mb-2"
                       placeholder="Question">

                <input type="text"
                       name="questions[${i}][reponse]"
                       class="form-control mb-2"
                       placeholder="Réponse">

                <select name="questions[${i}][type]" class="form-select">
                    <option value="text">Text</option>
                    <option value="qcm">QCM</option>
                </select>

            </div>
        `;
    }
}
</script>
</body>
</html>




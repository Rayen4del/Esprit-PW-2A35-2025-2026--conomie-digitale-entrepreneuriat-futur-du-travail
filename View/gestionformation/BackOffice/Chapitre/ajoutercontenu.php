
<?php
include __DIR__ . '/../../../../Controller/ChapitreController.php';
$id_c_selected = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = "";
$chapitreC = new ChapitreController();
$chapitres = $chapitreC->getChapitreById($id_c_selected);
session_start();
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
    <link rel="stylesheet" href="../../assetsjs/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../assetsjs/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../assetsjs/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../assetsjs/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../assetsjs/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="../../assetsjs/assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="../../assetsjs/assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../../assetsjs/assets/js/config.js"></script>
  </head>

  <body>
      
          <!-- Content wrapper -->
          <div class="content-wrapper">
              <div class="container-xxl flex-grow-1 container-p-y">
                <div class="row">

                  <!____________________________________________________>
                  <div class="col-12">
                      <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                          <form id="formContenu">
                            
                                

                            <div class="mb-3 w-50">
                              <label class="form-label fw-bold">Chapitre sélectionné</label>
                                <input type="hidden" name="chapid" value="<?= $id_c_selected ?>">   
                              <div class="form-control bg-light">
                                  <?php if (!empty($chapitres)) { ?>
                                      #<?= $chapitres['id_c'] ?> - <?= $chapitres['titre_c'] ?>
                                  <?php } else { ?>
                                      Aucun chapitre trouvé
                                  <?php } ?>
                              </div>
                          
                            </div>

                            <!-- ================= CONTENU TEXT ================= -->
                            <div class="mb-4">
                              <label class="form-label fw-bold">Contenu texte</label>

                              <div id="toolbar">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                                <button class="ql-underline"></button>
                                <select class="ql-color"></select>
                                <select class="ql-background"></select>
                                <select class="ql-header">
                                  <option selected></option>
                                  <option value="1"></option>
                                  <option value="2"></option>
                                </select>
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                                <button class="ql-link"></button>
                              </div>

                              <div id="editor" style="height: 250px;" class="border rounded"></div>

                              <div class="d-flex justify-content-end mt-2">
                                <button type="button" id="addText" class="btn btn-success px-4">
                                  Ajouter texte
                                </button>
                              </div>
                            </div>

                            <hr>

                            <!-- ================= CHAPITRE SELECT ================= -->
                            <div class="d-flex justify-content-between align-items-center mb-3">

                              <div>
                                <h5 class="mb-0">Création du contenu</h5>
                                <small class="text-muted">Choisissez un chapitre et ajoutez du contenu</small>
                              </div>


                            </div>

                            <!-- ================= UPLOAD ================= -->
                            <div class="text-center mb-4">
                              <div id="drop-zone" class="border p-4 rounded bg-light">
                                Glissez vos fichiers ici
                              </div>

                              <input type="file" id="fileInput" multiple hidden>

                              <div id="preview" class="mt-3 d-flex flex-wrap gap-2 justify-content-center"></div>
                            </div>

                            <!-- ================= BUTTON ================= -->
                            <div class="text-end">
                              <button type="submit" class="btn btn-primary btn-lg px-4">
                                Valider le contenu
                              </button>
                            </div>

                          </form>

                        </div>
                      </div>
                    </div>
                    <a href="consulterchapitres.php?open=<?= $id_c_selected?>" class="btn btn-secondary">
                        Retour
                    </a>
                  <!____________________________________________________>
                  
                </div>
                </div>
              </div>
          </div>
          <!-- / Content wrapper -->
        </div>
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../../assetsjs/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../assetsjs/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../assetsjs/assets/vendor/js/bootstrap.js"></script>
    <script src="../../assetsjs/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../../assetsjs/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="../../assetsjs/assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="../../assetsjs/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="../../assetsjs/assets/js/dashboards-analytics.js"></script>

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
<script src="contenu.js"></script>
</body>
</html>




<?php
// ─── Self-contained: handle form submission directly here ───────────
include_once __DIR__ . '/../../../../config.php';
include_once __DIR__ . '/../../../../model/Evenement.php';
include_once __DIR__ . '/../../../../controller/evenement/EvenementController.php';

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre       = trim($_POST['titre']       ?? '');
    $type        = trim($_POST['type']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $dateEvent   = trim($_POST['dateEvent']   ?? '');
    $duree       = intval($_POST['duree']     ?? 0);
    $lieu_lien   = trim($_POST['lieu_lien']   ?? '');
    $statut      = trim($_POST['statut']      ?? '');
    $nbplaces    = intval($_POST['nbplaces']  ?? 0);

    $evenement = new Evenement($titre, $type, $description, $dateEvent, $duree, $lieu_lien, $statut, $nbplaces);

    $controller = new EvenementController();
    if ($controller->addEvenement($evenement)) {
        $success = true;
        header('Location: liste_evenements.php');
        exit;
    } else {
        $errors[] = "Erreur lors de l'ajout de l'événement.";
    }
}

$old = function(string $key, $default = '') {
    return htmlspecialchars($_POST[$key] ?? $default);
};
?>
<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
  <title>Créer un Événement</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    :root {
      --bs-primary:#696cff; --menu-bg:#fff; --menu-text:#566a7f;
      --menu-active-bg:#f1f1ff; --menu-active-text:#696cff;
      --body-bg:#f5f5f9; --navbar-bg:#fff;
      --sidebar-width:260px; --header-height:64px;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Public Sans',sans-serif;font-size:.9375rem;background:var(--body-bg);color:#566a7f}
    .layout-wrapper{display:flex;min-height:100vh}
    .layout-container{display:flex;flex:1}
    .layout-menu{width:var(--sidebar-width);background:var(--menu-bg);flex-shrink:0;display:flex;
      flex-direction:column;box-shadow:0 0 0 1px rgba(67,89,113,.05),0 2px 6px rgba(67,89,113,.12);
      position:fixed;top:0;left:0;bottom:0;z-index:1100;overflow-y:auto}
    .app-brand{display:flex;align-items:center;padding:1.5rem 1.5rem .5rem;min-height:var(--header-height)}
    .app-brand-text{font-size:1.25rem;font-weight:700;color:#566a7f;margin-left:.6rem}
    .menu-inner{list-style:none;padding:.5rem 0 1rem;flex:1}
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
    .section-divider{font-size:.75rem;font-weight:700;color:#a1acb8;letter-spacing:.8px;
      text-transform:uppercase;margin:1.25rem 0 .75rem;padding-bottom:.4rem;
      border-bottom:1px dashed #e7eaf0}
    .content-footer{padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;
      font-size:.8125rem;color:#a1acb8;border-top:1px solid rgba(67,89,113,.08)}
    .footer-link{color:#a1acb8;text-decoration:none;margin-left:1rem}
    .footer-link:hover{color:var(--bs-primary)}
    .field-error{font-size:.8rem;color:#ff3e1d;margin-top:.25rem}
    .form-control.is-invalid,.form-select.is-invalid{border-color:#ff3e1d}
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
              <a href="/projet/view/evenement/html/frontoffice/liste_evenements.php" class="menu-link">
                <i class="bi bi-list-ul menu-icon"></i> Liste
              </a>
            </li>
            <li class="menu-item">
              <a href="/projet/view/evenement/html/frontoffice/form_evenement.php" class="menu-link active">
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
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" placeholder="Rechercher..." />
          </div>
        </div>
        <div class="navbar-nav-right">
          <button class="nav-icon-btn"><i class="bi bi-bell"></i><span class="nav-badge"></span></button>
          <button class="nav-icon-btn"><i class="bi bi-chat-dots"></i></button>
          <div class="user-avatar">AD<span class="online-dot"></span></div>
        </div>
      </nav>
      <!-- / NAVBAR -->

      <div class="content-wrapper">

        <p class="page-title">
          Événements <span>/ Créer un événement</span>
        </p>

        <?php if ($success): ?>
        <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
          <i class="bi bi-check-circle-fill fs-5"></i>
          <div>Événement publié avec succès ! <a href="/projet/view/evenement/html/frontoffice/liste_evenements.php" class="alert-link">Voir la liste →</a></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <strong>Veuillez corriger les erreurs suivantes :</strong>
          <ul class="mb-0 mt-1">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-calendar-plus"></i></div>
            <span class="card-header-title">Informations de l'événement</span>
          </div>
          <div class="card-body">

            <!-- action="" → posts back to this same view file, URL never changes -->
            <form id="eventForm" action="" method="POST" novalidate>

              <div class="section-divider">Informations générales</div>

              <!-- Titre -->
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="event-titre">Titre *</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-type"></i></span>
                    <input type="text" class="form-control <?= !empty($errors) && empty(trim($_POST['titre'] ?? '')) ? 'is-invalid' : '' ?>"
                           id="event-titre" name="titre"
                           value="<?= $old('titre') ?>"
                           placeholder="Nom de l'événement" maxlength="200" />
                  </div>
                  <div class="field-error" id="err-titre"></div>
                </div>
              </div>

              <!-- Type -->
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="event-type">Type *</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                    <select class="form-select" id="event-type" name="type" required>
                      <option value="" disabled <?= empty($_POST['type'] ?? '') ? 'selected' : '' ?>>-- Sélectionner un type --</option>
                      <?php
                        $types = ['workshop'=>'Workshop','conference'=>'Conférence','seminaire'=>'Séminaire',
                                  'hackathon'=>'Hackathon','formation'=>'Formation','webinar'=>'Webinaire','autre'=>'Autre'];
                        foreach ($types as $val => $label):
                          $sel = (($_POST['type'] ?? '') === $val) ? 'selected' : '';
                      ?>
                      <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="field-error" id="err-type"></div>
                </div>
              </div>

              <!-- Description -->
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="event-desc">Description</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-text-paragraph"></i></span>
                    <textarea class="form-control" id="event-desc" name="description"
                              placeholder="Décrivez votre événement..." rows="3"><?= $old('description') ?></textarea>
                  </div>
                </div>
              </div>

              <div class="section-divider">Date &amp; Durée</div>

              <!-- Date -->
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="event-date">Date de l'événement *</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                    <input type="date" class="form-control" id="event-date" name="dateEvent"
                           value="<?= $old('dateEvent') ?>" required />
                  </div>
                  <div class="field-error" id="err-date"></div>
                </div>
              </div>

              <!-- Durée -->
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="event-duree">Durée</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                    <input type="number" class="form-control" id="event-duree" name="duree"
                           value="<?= $old('duree', '0') ?>" placeholder="ex: 2" min="0" max="720" />
                    <span class="input-group-text">heure(s)</span>
                  </div>
                  <div class="form-text">Durée estimée en heures</div>
                  <div class="field-error" id="err-duree"></div>
                </div>
              </div>

              <div class="section-divider">Lieu &amp; Accès</div>

              <!-- Lieu / Lien -->
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="event-lieu">Lieu / Lien *</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <input type="text" class="form-control" id="event-lieu" name="lieu_lien"
                           value="<?= $old('lieu_lien') ?>"
                           placeholder="Adresse physique ou lien (ex: https://meet.google.com/...)"
                           maxlength="255" required />
                  </div>
                  <div class="form-text">Indiquez une adresse ou un lien de réunion en ligne</div>
                  <div class="field-error" id="err-lieu"></div>
                </div>
              </div>

              <!-- Nombre de places -->
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label" for="event-places">Nombre de places *</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <input type="number" class="form-control" id="event-places" name="nbplaces"
                           value="<?= $old('nbplaces') ?>"
                           placeholder="ex: 30" min="1" required />
                    <span class="input-group-text">participants</span>
                  </div>
                  <div class="field-error" id="err-places"></div>
                </div>
              </div>

              <div class="section-divider">Statut</div>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label" for="event-statut">Statut *</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                    <select class="form-select" id="event-statut" name="statut" required
                            onchange="updateStatutColor(this)">
                      <option value="" disabled <?= empty($_POST['statut'] ?? '') ? 'selected' : '' ?>>-- Sélectionner un statut --</option>
                      <?php
                        $statuts = ['ouvert'=>'Ouvert','ferme'=>'Fermé','complet'=>'Complet'];
                        foreach ($statuts as $val => $label):
                          $sel = (($_POST['statut'] ?? '') === $val) ? 'selected' : '';
                      ?>
                      <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-text" id="statut-hint"></div>
                  <div class="field-error" id="err-statut"></div>
                </div>
              </div>

              <div class="row justify-content-end mt-2">
                <div class="col-sm-9 d-flex gap-2 flex-wrap">
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Publier l'événement
                  </button>
                  <button type="button" class="btn btn-outline-danger" onclick="resetForm()">
                    <i class="bi bi-x-lg me-1"></i> Annuler
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <div id="toast-area" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;"></div>

      </div><!-- /content-wrapper -->

      <footer class="content-footer">
        <div>© <span id="year"></span> EventHub — Plateforme d'événements</div>
        <div>
          <a href="#" class="footer-link">Aide</a>
          <a href="#" class="footer-link">Mentions légales</a>
          <a href="#" class="footer-link">Support</a>
        </div>
      </footer>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/projet/view/evenement/html/frontoffice/js/common.js"></script>
<script src="/projet/view/evenement/html/frontoffice/js/validate_rules.js"></script>
<script src="/projet/view/evenement/html/frontoffice/js/form_validation.js"></script>
<script>
document.getElementById('year').textContent = new Date().getFullYear();
<?php if ($success): ?>
showToast('Événement publié avec succès !', 'success');
<?php elseif (!empty($errors)): ?>
showToast('Veuillez corriger les erreurs.', 'error');
<?php endif; ?>
</script>
</body>
</html>

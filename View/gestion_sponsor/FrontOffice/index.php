<?php
// ─── Self-contained: handle form submission directly here ───────────
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../Model/Sponsoring.php';
include_once __DIR__ . '/../../Controller/sponsoringController.php';
include_once __DIR__ . '/../../Model/produit.php';
include_once __DIR__ . '/../../Controller/produitController.php';

$success = false;
$errors  = [];
$produitSuccess = false;
$produitErrors = [];
$editing = false;
$produitEditing = false;
$currentSponsoring = null;
$currentProduit = null;
$activeTab = 'sponsor';

$sponsoringController = new SponsoringController();
$produitController = new ProduitController();
$deleteSponsorError = '';

// Handle GET actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $id = intval($_GET['id']);

        if ($_GET['action'] === 'delete_sp') {
            $productCount = $produitController->getProduitCountBySponsoring($id);
            if ($productCount > 0 && empty($_GET['cascade'])) {
                $deleteSponsorError = 'Ce sponsoring possède ' . $productCount . ' produit(s) associé(s). Confirmez la suppression pour supprimer également ces produits.';
            } else {
                if ($productCount > 0) {
                    $produitController->deleteProduitsBySponsoring($id);
                }
                $sponsoringController->deleteSponsoring($id);
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        }

        if ($_GET['action'] === 'edit_sp') {
            $currentSponsoring = $sponsoringController->showSponsoring($id);
            if ($currentSponsoring) {
                $editing = true;
                $activeTab = 'sponsor';
            }
        }

        if ($_GET['action'] === 'delete_prod') {
            $produitController->deleteProduit($id);
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=produit');
            exit;
        }

        if ($_GET['action'] === 'edit_prod') {
            $currentProduit = $produitController->showProduit($id);
            if ($currentProduit) {
                $produitEditing = true;
                $activeTab = 'produit';
            }
        }
    }

    if (isset($_GET['tab'])) {
        if ($_GET['tab'] === 'produit') {
            $activeTab = 'produit';
        } elseif ($_GET['tab'] === 'catalogue') {
            $activeTab = 'catalogue';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? 'sponsor';

    if ($formType === 'produit') {
        $id_p       = intval($_POST['id_p']       ?? 0);
        $nom        = trim($_POST['nom']        ?? '');
        $categrie   = trim($_POST['categrie']   ?? '');
        $prix       = floatval($_POST['prix']   ?? 0);
        $description= trim($_POST['description']?? '');
        $image      = trim($_POST['image']      ?? '');
        $id_sp      = intval($_POST['id_sp']    ?? 0);

        if ($nom === '') {
            $produitErrors['nom'] = 'Le nom du produit est requis.';
        }
        if ($categrie === '') {
            $produitErrors['categrie'] = 'La catégorie est requise.';
        }
        if ($prix <= 0) {
            $produitErrors['prix'] = 'Le prix doit être supérieur à 0.';
        }
        if ($description === '') {
            $produitErrors['description'] = 'La description est requise.';
        }
        if ($image === '') {
            $produitErrors['image'] = 'L\'URL de l\'image est requise.';
        } elseif (!filter_var($image, FILTER_VALIDATE_URL)) {
            $produitErrors['image'] = 'L\'URL de l\'image n\'est pas valide.';
        }
        if ($id_sp <= 0) {
            $produitErrors['id_sp'] = 'L\'ID du sponsoring est requis.';
        } elseif (!$sponsoringController->showSponsoring($id_sp)) {
            $produitErrors['id_sp'] = 'Ce sponsoring n\'existe pas.';
        }

        $produit = new Produit($id_p, $nom, $categrie, $prix, $description, $image, $id_sp);

        if (empty($produitErrors)) {
            if ($id_p > 0) {
                $produitController->updateProduit($produit, $id_p);
                $produitSuccess = true;
                $produitEditing = false;
                $currentProduit = null;
            } else {
                $produitController->addProduit($produit);
                $produitSuccess = true;
            }
        } else {
            $produitEditing = $id_p > 0;
            $activeTab = 'produit';
            if ($produitEditing) {
                $currentProduit = $produitController->showProduit($id_p);
            }
        }

        $activeTab = 'produit';
    } else {
        $id_sp      = intval($_POST['id_sp']      ?? 0);
        $id_u       = intval($_POST['id_u']       ?? 0);
        $nom_ent    = trim($_POST['nom_ent']    ?? '');
        $logo_entp  = trim($_POST['logo_entp']  ?? '');
        $date_deb   = trim($_POST['date_deb']   ?? '');
        $date_fin   = trim($_POST['date_fin']   ?? '');
        $mail_event = trim($_POST['mail_event'] ?? '');

        // Validation du logo
        if ($logo_entp !== '' && !filter_var($logo_entp, FILTER_VALIDATE_URL)) {
            $errors['logo_entp'] = 'L\'URL du logo n\'est pas valide.';
        }

        $dateDebObj = !empty($date_deb) ? new DateTime($date_deb) : null;
        $dateFinObj = !empty($date_fin) ? new DateTime($date_fin) : null;

        $sponsoring = new Sponsoring($id_sp, $id_u, $nom_ent, $logo_entp, $dateDebObj, $dateFinObj, $mail_event);

        if ($id_sp > 0) {
            $sponsoringController->updateSponsoring($sponsoring, $id_sp);
            $success = true;
            $editing = false;
            $currentSponsoring = null;
        } else {
            $sponsoringController->addSponsoring($sponsoring);
            $success = true;
        }

        $activeTab = 'sponsor';
    }
}

$sponsorings = $sponsoringController->listSponsoring();
$sponsorings = $sponsorings->fetchAll(PDO::FETCH_ASSOC);
$produits = $produitController->listProduits();
$produits = $produits->fetchAll(PDO::FETCH_ASSOC);

$old = function(string $key, $default = '') use ($currentSponsoring) {
    if ($currentSponsoring) {
        return htmlspecialchars($currentSponsoring[$key] ?? $default);
    }
    return htmlspecialchars($_POST[$key] ?? $default);
};

$oldProduit = function(string $key, $default = '') use ($currentProduit) {
    if ($currentProduit) {
        return htmlspecialchars($currentProduit[$key] ?? $default);
    }
    return htmlspecialchars($_POST[$key] ?? $default);
};
?>
<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
  <title>Ajouter un Sponsoring</title>
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
      <ul class="menu-inner">
        <li class="menu-header"></li>
        <li class="menu-item">
          <a href="#" onclick="switchTab('sponsor')" class="menu-link">
            <i class="bi bi-building menu-icon"></i> Sponsorings
          </a>
        </li>
        <li class="menu-item">
          <a href="#" onclick="switchTab('produit')" class="menu-link">
            <i class="bi bi-box-seam menu-icon"></i> Produits
          </a>
        </li>
        <li class="menu-item">
          <a href="#" onclick="switchTab('catalogue')" class="menu-link">
            <i class="bi bi-eye menu-icon"></i> Catalogue
          </a>
        </li>
      </ul>
    </aside>
    <!-- ═══ / SIDEBAR ═══ -->

    <div class="layout-page">

      <!-- NAVBAR -->
      <nav class="layout-navbar">
        <div class="navbar-nav-right">
          <button class="nav-icon-btn"><i class="bi bi-bell"></i><span class="nav-badge"></span></button>
          <button class="nav-icon-btn"><i class="bi bi-chat-dots"></i></button>
          <div class="user-avatar">AD<span class="online-dot"></span></div>
        </div>
      </nav>
      <!-- / NAVBAR -->

      <div class="content-wrapper">

        <p class="page-title">
          Gestion des Sponsorings et Produits <span>/ Catalogue</span>
        </p>

        <ul class="nav nav-tabs mb-4" id="crudTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'sponsor' ? 'active' : '' ?>" id="sponsor-tab" data-bs-toggle="tab" data-bs-target="#tab-sponsor" type="button" role="tab" aria-controls="tab-sponsor" aria-selected="<?= $activeTab === 'sponsor' ? 'true' : 'false' ?>">Sponsorings</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'produit' ? 'active' : '' ?>" id="produit-tab" data-bs-toggle="tab" data-bs-target="#tab-produit" type="button" role="tab" aria-controls="tab-produit" aria-selected="<?= $activeTab === 'produit' ? 'true' : 'false' ?>">Produits</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'catalogue' ? 'active' : '' ?>" id="catalogue-tab" data-bs-toggle="tab" data-bs-target="#tab-catalogue" type="button" role="tab" aria-controls="tab-catalogue" aria-selected="<?= $activeTab === 'catalogue' ? 'true' : 'false' ?>">Catalogue</button>
          </li>
        </ul>

        <div class="tab-content" id="crudTabsContent">
          <div class="tab-pane fade <?= $activeTab === 'sponsor' ? 'show active' : '' ?>" id="tab-sponsor" role="tabpanel" aria-labelledby="sponsor-tab">
            <div class="card">
              <div class="card-header">
                <div class="card-header-icon"><i class="bi bi-building"></i></div>
                <span class="card-header-title">Informations du sponsoring</span>
              </div>
              <div class="card-body">

                <!-- action="" → posts back to this same view file, URL never changes -->
                <form id="sponsoringForm" action="" method="POST" novalidate>
                  <input type="hidden" name="form_type" value="sponsor">
                  <input type="hidden" name="id_sp" value="<?= $currentSponsoring['id_sp'] ?? '' ?>">

                  <div class="section-divider">Informations générales</div>

                  <!-- ID User -->
                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="sponsoring-idu">ID User *</label>
                    <div class="col-sm-9">
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="number" class="form-control" id="sponsoring-idu" name="id_u"
                               value="<?= $old('id_u') ?>" placeholder="ID de l'utilisateur" min="1" required />
                      </div>
                      <div class="field-error" id="err-idu"></div>
                    </div>
                  </div>

                  <!-- Nom Entreprise -->
                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="sponsoring-nom">Nom Entreprise *</label>
                    <div class="col-sm-9">
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <input type="text" class="form-control" id="sponsoring-nom" name="nom_ent"
                               value="<?= $old('nom_ent') ?>" placeholder="Nom de l'entreprise" maxlength="255" required />
                      </div>
                      <div class="field-error" id="err-nom"></div>
                    </div>
                  </div>

                  <!-- Logo Entreprise -->
                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="sponsoring-logo">Logo Entreprise</label>
                    <div class="col-sm-9">
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-image"></i></span>
                        <input type="url" class="form-control" id="sponsoring-logo" name="logo_entp"
                               value="<?= $old('logo_entp') ?>" placeholder="URL du logo" maxlength="255" />
                      </div>
                      <div class="field-error" id="err-logo"><?= $errors['logo_entp'] ?? '' ?></div>
                    </div>
                  </div>

                  <!-- Email Event -->
                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="sponsoring-email">Email Event *</label>
                    <div class="col-sm-9">
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="sponsoring-email" name="mail_event"
                               value="<?= $old('mail_event') ?>" placeholder="email@example.com" required />
                      </div>
                      <div class="field-error" id="err-email"></div>
                    </div>
                  </div>

                  <div class="section-divider">Dates</div>

                  <!-- Date Début -->
                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="sponsoring-date-deb">Date Début *</label>
                    <div class="col-sm-9">
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date" class="form-control" id="sponsoring-date-deb" name="date_deb"
                               value="<?= $old('date_deb') ?>" required />
                      </div>
                      <div class="field-error" id="err-date-deb"></div>
                    </div>
                  </div>

                  <!-- Date Fin -->
                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="sponsoring-date-fin">Date Fin *</label>
                    <div class="col-sm-9">
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date" class="form-control" id="sponsoring-date-fin" name="date_fin"
                               value="<?= $old('date_fin') ?>" required />
                      </div>
                      <div class="field-error" id="err-date-fin"></div>
                    </div>
                  </div>

                  <div class="row justify-content-end mt-2">
                    <div class="col-sm-9 d-flex gap-2 flex-wrap">
                      <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> <?= $editing ? 'Mettre à jour' : 'Ajouter' ?> le sponsoring
                      </button>
                      <button type="button" class="btn btn-outline-danger" onclick="resetForm('sponsoringForm')">
                        <i class="bi bi-x-lg me-1"></i> Annuler
                      </button>
                    </div>
                  </div>

                </form>
              </div>
            </div>

            <!-- Sponsoring List -->
            <div class="card">
              <div class="card-header">
                <div class="card-header-icon"><i class="bi bi-building"></i></div>
                <span class="card-header-title">Liste des Sponsorings</span>
              </div>
              <div class="card-body">
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
                <table id="sponsorTable" class="table table-striped">
                  <thead>
                    <tr>
                      <th>ID Sponsoring</th>
                      <th>ID User</th>
                      <th>Nom Entreprise</th>
                      <th>Logo Entreprise</th>
                      <th>Date Début</th>
                      <th>Date Fin</th>
                      <th>Email Event</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($sponsorings as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['id_sp']); ?></td>
                      <td><?php echo htmlspecialchars($row['id_u']); ?></td>
                      <td><?php echo htmlspecialchars($row['nom_ent']); ?></td>
                      <td>
                        <?php if (!empty($row['logo_entp'])): ?>
                          <img src="<?php echo htmlspecialchars($row['logo_entp']); ?>" alt="Logo entreprise" style="max-width: 80px; max-height: 60px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';" />
                          <span style="display: none;"><?php echo htmlspecialchars($row['logo_entp']); ?></span>
                        <?php else: ?>
                          Aucun logo
                        <?php endif; ?>
                      </td>
                      <td><?php echo htmlspecialchars($row['date_deb']); ?></td>
                      <td><?php echo htmlspecialchars($row['date_fin']); ?></td>
                      <td><?php echo htmlspecialchars($row['mail_event']); ?></td>
                      <td>
                        <a href="?action=edit_sp&id=<?php echo $row['id_sp']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="?action=delete_sp&id=<?php echo $row['id_sp']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDeleteSponsor(<?php echo $row['id_sp']; ?>, <?php echo $produitController->getProduitCountBySponsoring($row['id_sp']); ?>)">Supprimer</a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="tab-pane fade <?= $activeTab === 'produit' ? 'show active' : '' ?>" id="tab-produit" role="tabpanel" aria-labelledby="produit-tab">
            <div class="card">
              <div class="card-header">
                <div class="card-header-icon"><i class="bi bi-box-seam"></i></div>
                <span class="card-header-title">Informations du produit</span>
              </div>
              <div class="card-body">
                <form id="produitForm" action="" method="POST" novalidate>
                  <input type="hidden" name="form_type" value="produit">
                  <input type="hidden" name="id_p" value="<?= $currentProduit['id_p'] ?? '' ?>">

                  <div class="section-divider">Détails du produit</div>

                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="produit-nom">Nom *</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="produit-nom" name="nom"
                             value="<?= $oldProduit('nom') ?>" placeholder="Nom du produit" maxlength="255" required />
                      <div class="field-error" id="err-produit-nom"><?= $produitErrors['nom'] ?? '' ?></div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="produit-categorie">Catégorie *</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="produit-categorie" name="categrie"
                             value="<?= $oldProduit('categrie') ?>" placeholder="Catégorie" maxlength="255" required />
                      <div class="field-error" id="err-produit-categorie"><?= $produitErrors['categrie'] ?? '' ?></div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="produit-prix">Prix *</label>
                    <div class="col-sm-9">
                      <input type="number" step="0.01" class="form-control" id="produit-prix" name="prix"
                             value="<?= $oldProduit('prix') ?>" placeholder="Prix" required />
                      <div class="field-error" id="err-produit-prix"><?= $produitErrors['prix'] ?? '' ?></div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="produit-description">Description *</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" id="produit-description" name="description" rows="3" placeholder="Description du produit" required maxlength="1000"><?= $oldProduit('description') ?></textarea>
                      <div class="field-error" id="err-produit-description"><?= $produitErrors['description'] ?? '' ?></div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="produit-image">Image URL *</label>
                    <div class="col-sm-9">
                      <input type="url" class="form-control" id="produit-image" name="image"
                             value="<?= $oldProduit('image') ?>" placeholder="URL de l'image" maxlength="255" required />
                      <div class="field-error" id="err-produit-image"><?= $produitErrors['image'] ?? '' ?></div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="produit-idsp">ID Sponsoring</label>
                    <div class="col-sm-9">
                      <select class="form-select" id="produit-idsp" name="id_sp" required>
                        <option value="">Sélectionnez un sponsoring</option>
                        <?php foreach ($sponsorings as $sp): ?>
                          <option value="<?= htmlspecialchars($sp['id_sp']) ?>" <?= (string)$oldProduit('id_sp') === (string)$sp['id_sp'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sp['id_sp'] . ' - ' . $sp['nom_ent']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <div class="field-error" id="err-produit-idsp"><?= $produitErrors['id_sp'] ?? '' ?></div>
                    </div>
                  </div>

                  <div class="row justify-content-end mt-2">
                    <div class="col-sm-9 d-flex gap-2 flex-wrap">
                      <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> <?= $produitEditing ? 'Mettre à jour' : 'Ajouter' ?> le produit
                      </button>
                      <button type="button" class="btn btn-outline-danger" onclick="resetForm('produitForm')">
                        <i class="bi bi-x-lg me-1"></i> Annuler
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <div class="card-header-icon"><i class="bi bi-table"></i></div>
                <span class="card-header-title">Liste des Produits</span>
              </div>
              <div class="card-body">
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
                    <div class="col-md-3 d-grid">
                      <button type="button" id="produit-search-clear" class="btn btn-outline-secondary">Effacer</button>
                    </div>
                  </div>
                </div>
                <table id="produitTable" class="table table-striped">
                  <thead>
                    <tr>
                      <th>ID Produit</th>
                      <th>Nom</th>
                      <th>Catégorie</th>
                      <th>Prix</th>
                      <th>Description</th>
                      <th>Image</th>
                      <th>ID Sponsoring</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($produits as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['id_p']); ?></td>
                      <td><?php echo htmlspecialchars($row['nom']); ?></td>
                      <td><?php echo htmlspecialchars($row['categrie']); ?></td>
                      <td><?php echo htmlspecialchars($row['prix']); ?></td>
                      <td><?php echo htmlspecialchars($row['description']); ?></td>
                      <td>
                        <?php if (!empty($row['image'])): ?>
                          <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Image du produit" style="max-width: 80px; max-height: 60px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';" />
                          <span style="display: none; color: #999; font-size: 0.8em;">Image indisponible</span>
                        <?php else: ?>
                          <span style="color: #999; font-size: 0.8em;">Aucune image</span>
                        <?php endif; ?>
                      </td>
                      <td><?php echo htmlspecialchars($row['id_sp']); ?></td>
                      <td>
                        <a href="?action=edit_prod&id=<?php echo $row['id_p']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="?action=delete_prod&id=<?php echo $row['id_p']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">Supprimer</a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="tab-pane fade <?= $activeTab === 'catalogue' ? 'show active' : '' ?>" id="tab-catalogue" role="tabpanel" aria-labelledby="catalogue-tab">
            <div class="row">
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <div class="card-header-icon"><i class="bi bi-building"></i></div>
                    <span class="card-header-title">Sponsorings Disponibles</span>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <?php foreach ($sponsorings as $sp): ?>
                      <div class="col-md-6 mb-3">
                        <div class="card h-100">
                          <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($sp['nom_ent']); ?></h6>
                            <p class="card-text small">
                              <strong>ID:</strong> <?php echo htmlspecialchars($sp['id_sp']); ?><br>
                              <strong>User:</strong> <?php echo htmlspecialchars($sp['id_u']); ?><br>
                              <strong>Email:</strong> <?php echo htmlspecialchars($sp['mail_event']); ?><br>
                              <strong>Période:</strong> <?php echo htmlspecialchars($sp['date_deb']); ?> - <?php echo htmlspecialchars($sp['date_fin']); ?>
                            </p>
                            <?php if (!empty($sp['logo_entp'])): ?>
                              <img src="<?php echo htmlspecialchars($sp['logo_entp']); ?>" alt="Logo" class="img-fluid mt-2" style="max-height: 60px;" onerror="this.style.display='none';" />
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <div class="card-header-icon"><i class="bi bi-box-seam"></i></div>
                    <span class="card-header-title">Produits Disponibles</span>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <?php foreach ($produits as $prod): ?>
                      <div class="col-md-6 mb-3">
                        <div class="card h-100">
                          <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($prod['nom']); ?></h6>
                            <p class="card-text small">
                              <strong>Catégorie:</strong> <?php echo htmlspecialchars($prod['categrie']); ?><br>
                              <strong>Prix:</strong> <?php echo htmlspecialchars($prod['prix']); ?> €<br>
                              <strong>Description:</strong> <?php echo htmlspecialchars(substr($prod['description'], 0, 50)); ?><?php echo strlen($prod['description']) > 50 ? '...' : ''; ?>
                            </p>
                            <?php if (!empty($prod['image'])): ?>
                              <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="Image du produit" class="img-fluid mt-2" style="max-height: 80px; object-fit: cover;" onerror="this.style.display='none';" />
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="toast-area" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;"></div>

      </div><!-- /content-wrapper -->

      <footer class="content-footer">
        <div>© <span id="year"></span> Skiller — Plateforme de sponsoring</div>
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
<script>
function resetForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return;

  form.reset();
  document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
  document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
  const tab = formId === 'produitForm' ? 'produit' : 'sponsor';
  window.location.href = window.location.pathname + '?tab=' + tab;
}

function validateForm(formId) {
  let isValid = true;
  const fieldSets = {
    sponsoringForm: [
      { id: 'sponsoring-idu', errorId: 'err-idu', required: true, type: 'number', min: 1 },
      { id: 'sponsoring-nom', errorId: 'err-nom', required: true, minLength: 2 },
      { id: 'sponsoring-logo', errorId: 'err-logo', required: false, type: 'url' },
      { id: 'sponsoring-email', errorId: 'err-email', required: true, type: 'email' },
      { id: 'sponsoring-date-deb', errorId: 'err-date-deb', required: true },
      { id: 'sponsoring-date-fin', errorId: 'err-date-fin', required: true }
    ],
    produitForm: [
      { id: 'produit-nom', errorId: 'err-produit-nom', required: true, minLength: 2 },
      { id: 'produit-categorie', errorId: 'err-produit-categorie', required: true, minLength: 2 },
      { id: 'produit-prix', errorId: 'err-produit-prix', required: true, type: 'number', min: 0.01 },
      { id: 'produit-description', errorId: 'err-produit-description', required: true, minLength: 5 },
      { id: 'produit-image', errorId: 'err-produit-image', required: true, type: 'url' },
      { id: 'produit-idsp', errorId: 'err-produit-idsp', required: true, type: 'number', min: 1 }
    ]
  };

  const fields = fieldSets[formId] || [];
  fields.forEach(field => {
    const el = document.getElementById(field.id);
    const errorEl = document.getElementById(field.errorId);
    if (!el || !errorEl) return;

    errorEl.textContent = '';
    el.classList.remove('is-invalid');

    if (field.required && !el.value.trim()) {
      errorEl.textContent = 'Ce champ est requis.';
      el.classList.add('is-invalid');
      isValid = false;
    } else if (field.type === 'number' && field.min && parseFloat(el.value) < field.min) {
      errorEl.textContent = 'Valeur invalide.';
      el.classList.add('is-invalid');
      isValid = false;
    } else if (field.type === 'email' && el.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) {
      errorEl.textContent = 'Email invalide.';
      el.classList.add('is-invalid');
      isValid = false;
    } else if (field.type === 'url' && el.value && !/^(https?:\/\/).+$/i.test(el.value)) {
      errorEl.textContent = 'URL invalide.';
      el.classList.add('is-invalid');
      isValid = false;
    } else if (field.minLength && el.value.length < field.minLength) {
      errorEl.textContent = 'Minimum ' + field.minLength + ' caractères.';
      el.classList.add('is-invalid');
      isValid = false;
    }
  });

  if (formId === 'sponsoringForm') {
    const dateDeb = document.getElementById('sponsoring-date-deb').value;
    const dateFin = document.getElementById('sponsoring-date-fin').value;
    if (dateDeb && dateFin && new Date(dateDeb) >= new Date(dateFin)) {
      const endEl = document.getElementById('sponsoring-date-fin');
      document.getElementById('err-date-fin').textContent = 'La date de fin doit être après la date de début.';
      endEl.classList.add('is-invalid');
      isValid = false;
    }
  }

  return isValid;
}

document.getElementById('sponsoringForm')?.addEventListener('submit', function(e) {
  if (!validateForm('sponsoringForm')) {
    e.preventDefault();
  }
});

document.getElementById('produitForm')?.addEventListener('submit', function(e) {
  if (!validateForm('produitForm')) {
    e.preventDefault();
  }
});

function confirmDeleteSponsor(id, productCount) {
  if (productCount <= 0) {
    return confirm('Êtes-vous sûr de vouloir supprimer ce sponsoring ?');
  }
  const message = 'Ce sponsoring a ' + productCount + ' produit(s) associé(s). Supprimer le sponsoring supprimera également ces produits. Voulez-vous continuer ?';
  if (confirm(message)) {
    window.location.href = '?action=delete_sp&id=' + id + '&cascade=1';
  }
  return false;
}

function switchTab(tabName) {
  // Update URL without page reload
  const url = new URL(window.location);
  url.searchParams.set('tab', tabName);
  window.history.pushState({}, '', url);

  // Update active tab in navigation
  document.querySelectorAll('#crudTabs .nav-link').forEach(tab => {
    tab.classList.remove('active');
  });
  document.querySelectorAll('#crudTabsContent .tab-pane').forEach(pane => {
    pane.classList.remove('show', 'active');
  });

  const targetTab = document.getElementById(tabName + '-tab');
  const targetPane = document.getElementById('tab-' + tabName);

  if (targetTab && targetPane) {
    targetTab.classList.add('active');
    targetPane.classList.add('show', 'active');
  }
}

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

setupSponsorSearchControls();

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

setupProduitSearchControls();

function showToast(message, type) {
  const alertDiv = document.createElement('div');
  alertDiv.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' d-flex align-items-center gap-2';
  alertDiv.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill') + ' fs-5"></i><div>' + message + '</div>';
  const toastArea = document.getElementById('toast-area');
  if (toastArea) {
    toastArea.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
  }
}

document.getElementById('year').textContent = new Date().getFullYear();

<?php if ($success): ?>
window.addEventListener('DOMContentLoaded', function() {
  showToast('Sponsoring <?= isset($_POST['id_sp']) && intval($_POST['id_sp']) > 0 ? 'mis à jour' : 'ajouté' ?> avec succès !', 'success');
  document.getElementById('sponsoringForm').reset();
});
<?php endif; ?>

<?php if ($produitSuccess): ?>
window.addEventListener('DOMContentLoaded', function() {
  showToast('Produit <?= isset($_POST['id_p']) && intval($_POST['id_p']) > 0 ? 'mis à jour' : 'ajouté' ?> avec succès !', 'success');
  document.getElementById('produitForm').reset();
});
<?php endif; ?>

<?php if (!empty($deleteSponsorError)): ?>
window.addEventListener('DOMContentLoaded', function() {
  showToast('<?= addslashes($deleteSponsorError) ?>', 'error');
});
<?php endif; ?>

<?php if (!empty($errors) || !empty($produitErrors)): ?>
window.addEventListener('DOMContentLoaded', function() {
  showToast('Veuillez corriger les erreurs.', 'error');
});
<?php endif; ?>
</script>
</body>
</html>
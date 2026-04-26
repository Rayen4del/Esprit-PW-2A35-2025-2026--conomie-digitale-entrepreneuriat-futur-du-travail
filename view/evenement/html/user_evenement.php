<?php
include(__DIR__ . '/../../../config.php');
include(__DIR__ . '/../../../controller/evenement/EvenementController.php');

$controller = new EvenementController();

$event_id = intval($_GET['event_id'] ?? 0);
$event    = $event_id > 0 ? $controller->getById($event_id) : null;

$success  = false;
$error    = '';

// ── Handle POST: save registration to DB ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUtilisateur = intval($_POST['idUtilisateur'] ?? 0);
    $idEvent       = intval($_POST['idEvent']       ?? 0);
    $statut        = trim($_POST['statut']          ?? 'inscrit');

    // Validate statut against allowed enum values
    $allowedStatuts = ['inscrit', 'annulé'];
    if (!in_array($statut, $allowedStatuts)) {
        $statut = 'inscrit';
    }

    if ($idUtilisateur <= 0) {
        $error = "ID utilisateur invalide.";
    } elseif ($idEvent <= 0) {
        $error = "Événement invalide.";
    } elseif ($controller->isAlreadyRegistered($idUtilisateur, $idEvent)) {
        $error = "Vous êtes déjà inscrit à cet événement.";
    } else {
        if ($controller->inscrire($idUtilisateur, $idEvent, $statut)) {
            $success = true;
        } else {
            $error = "Erreur lors de l'inscription. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inscription - <?= $event ? htmlspecialchars($event['Titre']) : 'Événement' ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    body { font-family: 'Public Sans', sans-serif; background: #f5f5f9; }
    .form-container {
      max-width: 520px; margin: 60px auto; background: #fff;
      border-radius: 12px; padding: 36px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.10);
    }
    .event-badge {
      background: #f1f1ff; color: #696cff; border-radius: 8px;
      padding: 10px 16px; font-size: .9rem; margin-bottom: 1.5rem;
    }
    .form-label { font-weight: 500; color: #566a7f; }
    .readonly-field { background: #f5f5f9; color: #a1acb8; }
    .btn-primary { background: #696cff; border-color: #696cff; }
    .btn-primary:hover { background: #5f5fe8; border-color: #5f5fe8; }
  </style>
</head>
<body>
<div class="container">
  <div class="form-container">

    <h4 class="text-center mb-1 fw-bold" style="color:#566a7f">Inscription à l'événement</h4>
    <p class="text-center text-muted mb-4" style="font-size:.85rem">Remplissez le formulaire pour confirmer votre place</p>

    <?php if ($event): ?>
    <div class="event-badge d-flex align-items-center gap-2">
      <i class="bi bi-calendar-event"></i>
      <div>
        <strong><?= htmlspecialchars($event['Titre']) ?></strong><br>
        <small><?= date('d/m/Y', strtotime($event['dateEvent'])) ?> — <?= htmlspecialchars($event['lieu_lien']) ?></small>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center gap-2">
      <i class="bi bi-check-circle-fill fs-5"></i>
      <div>
        <strong>Inscription confirmée !</strong><br>
        <small>Votre inscription a été enregistrée avec succès.</small>
      </div>
    </div>
    <div class="d-grid mt-3">
      <a href="/projet/view/evenement/html/user_events.php" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i> Retour aux événements
      </a>
    </div>

    <?php elseif (!$event): ?>
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-triangle me-2"></i> Événement introuvable.
    </div>
    <a href="/projet/view/evenement/html/user_events.php" class="btn btn-outline-secondary mt-2">
      <i class="bi bi-arrow-left me-1"></i> Retour
    </a>

    <?php else: ?>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2">
      <i class="bi bi-exclamation-circle-fill"></i>
      <div><?= htmlspecialchars($error) ?></div>
    </div>
    <?php endif; ?>

    <!-- action="" → stays on this view URL -->
    <form method="POST" action="">

      <div class="mb-3">
        <label class="form-label">ID Inscription</label>
        <input type="text" class="form-control readonly-field" value="AUTO-GENERATED" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label" for="idUtilisateur">ID Utilisateur <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="idUtilisateur" name="idUtilisateur"
               placeholder="Entrez votre ID utilisateur" min="1" required
               value="<?= intval($_POST['idUtilisateur'] ?? '') ?>">
        <div class="form-text">Votre identifiant utilisateur dans le système.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">ID Événement</label>
        <input type="number" class="form-control readonly-field" name="idEvent"
               value="<?= $event_id ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">Date d'inscription</label>
        <input type="text" class="form-control readonly-field" value="<?= date('d/m/Y') ?>" readonly>
      </div>

      <div class="mb-4">
        <label class="form-label" for="statut">Statut <span class="text-danger">*</span></label>
        <select class="form-select" id="statut" name="statut" required>
          <option value="inscrit" <?= (($_POST['statut'] ?? '') === 'inscrit' || empty($_POST['statut'])) ? 'selected' : '' ?>>Inscrit</option>
          <option value="annulé"  <?= (($_POST['statut'] ?? '') === 'annulé')  ? 'selected' : '' ?>>Annulé</option>
        </select>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="bi bi-check-lg me-1"></i> Confirmer l'inscription
        </button>
        <a href="/projet/view/evenement/html/user_events.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Retour aux événements
        </a>
      </div>

    </form>
    <?php endif; ?>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

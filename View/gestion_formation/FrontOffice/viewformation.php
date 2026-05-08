<?php
include __DIR__ . '/../../../Controller/ChapitreController.php';
include __DIR__ . '/../../../Controller/FormationController.php';
include __DIR__ . '/../../../Controller/TestController.php';
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../Controller/ProgressionController.php';

session_start();

$formationC = new FormationController();
$chapitreC = new ChapitreController();
$testC = new TestController();
$progressC = new ProgressionController();

$id = $_GET['id'] ?? null;

if (!$id) {
  die("<div class='alert alert-danger p-3'>ID de la formation manquant.</div>");
}

// Formation
$formation = $formationC->getFormationById($id);

if (!$formation) {
  die("<div class='alert alert-danger p-3'>Formation introuvable.</div>");
}

// Chapitres (⚠️ AVANT COUNT)
$chapitres = $chapitreC->listChapitresByFormation($id) ?? [];
$totalChapitres = count($chapitres);

// User
$user_id = $_SESSION['user_id'] ?? null;

// Progression DB
$chapitresFini = 0;

if ($user_id) {
    $chapitresFini = $progressC->countCompletedChapitres($user_id, $id);
}

// % progression
$progression = ($totalChapitres > 0)
    ? ($chapitresFini / $totalChapitres) * 100
    : 0;

// Tests
$db = config::getConnexion();
$stmt = $db->prepare("SELECT * FROM test WHERE id_f = ?");
$stmt->execute([$id]);
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../assetsjs/assets/vendor/css/core.css">
  <link rel="stylesheet" href="../assetsjs/assets/vendor/css/theme-default.css">
</head>

<body class="bg-light p-3">

<div class="container py-3">

  <!-- FORMATION -->
  <div class="card shadow-sm mb-4 border-0">

    <div class="row g-0">

      <!-- IMAGE -->
      <div class="col-md-4">
        <img src="<?= UPLOAD_URL . htmlspecialchars($formation['image'] ?? 'default.png') ?>"
             class="img-fluid rounded-start h-100"
             style="object-fit:cover;">
      </div>

      <!-- INFO -->
      <div class="col-md-8">
        <div class="card-body">

          <h3 class="fw-bold"><?= htmlspecialchars($formation['titre']); ?></h3>

          <p class="text-muted">
            <?= htmlspecialchars($formation['description']); ?>
          </p>

          <p>
            <strong>Etat :</strong>
            <?php if($formation['etat'] == 'active') { ?>
              <span class="badge bg-success">Active</span>
            <?php } elseif($formation['etat'] == 'inactive') { ?>
              <span class="badge bg-danger">Inactive</span>
            <?php } else { ?>
              <span class="badge bg-secondary"><?= $formation['etat']; ?></span>
            <?php } ?>
          </p>

          <p><strong>Créé par :</strong> <?= htmlspecialchars($formation['nom_propr']); ?></p>

          <p><strong>Date :</strong> <?= $formation['date_c']; ?></p>
                    <div class="progress" style="height: 12px; border-radius:10px;">
                      <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                          style="width: <?= $progression ?>%">
                      </div>
                    </div>

                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <?= $chapitresFini ?> / <?= $totalChapitres ?> chapitres terminés
                        </small>
                    </div>
            </div>

        </div>
      </div>

    </div>
  </div>

  <!-- BUTTON START FORMATION -->
  <div class="text-center mb-4">
      <a href="cours.php?id=<?= $formation['id_f']; ?>" 
   class="btn btn-success btn-lg">
   Commencer la formation
</a>
  </div>

  <!-- CHAPITRES -->
  <div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">
      <h5>📚 Chapitres</h5>
    </div>

    <div class="card-body">

      <?php if ($chapitres) { ?>
        <ul class="list-group">

          <?php foreach ($chapitres as $ch) { ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">

              <span>📖 <?= htmlspecialchars($ch['titre_c']); ?></span>

              <span class="badge bg-primary">
                <?= $ch['ordre']; ?>
              </span>

            </li>
          <?php } ?>

        </ul>
      <?php } else { ?>
        <div class="alert alert-warning">Aucun chapitre disponible</div>
      <?php } ?>

    </div>
  </div>

  <!-- TESTS -->
  <div class="card shadow-sm border-0">

    <div class="card-header bg-white">
      <h5>🧪 Tests</h5>
    </div>

    <div class="card-body">

      <?php if ($tests) { ?>
        <div class="list-group">

          <?php foreach ($tests as $t) { ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">

              <div>
                <strong>Test #<?= $t['id_t']; ?></strong><br>
                <small>Score minimum : <?= $t['score_min']; ?></small>
              </div>

              <a href="passTest.php?id=<?= $t['id_t']; ?>" 
                 class="btn btn-primary btn-sm">
                ▶ Passer test
              </a>

            </div>
          <?php } ?>

        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          Aucun test disponible pour cette formation
        </div>
      <?php } ?>

    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../../assets/vendor/js/bootstrap.js"></script>
<script>
let chapitres = <?= json_encode($chapitres) ?>;
let currentIndex = 0;
let modalInstance = null;

document.addEventListener("DOMContentLoaded", function() {
  modalInstance = new bootstrap.Modal(document.getElementById('playerModal'));
});
</script>

</body>
</html>
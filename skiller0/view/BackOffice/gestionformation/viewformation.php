<?php
include __DIR__ . '/../../../Controller/ChapitreController.php';
include __DIR__ . '/../../../Controller/FormationController.php';


$id = $_GET['id'] ?? null;

// Guard: require id
if (!$id) {
  echo "<div class='alert alert-danger p-3'>ID de la formation manquant.</div>";
  exit;
}

// Formation
$formationC = new FormationController();
$formation = $formationC->getFormationById($id);
if (!$formation) {
  echo "<div class='alert alert-danger p-3'>Formation introuvable (ID: " . htmlspecialchars($id) . ").</div>";
  exit;
}

// Chapitres
$chapitreC = new ChapitreController();
$chapitres = $chapitreC->listChapitresByFormation($id); // tu vas ajouter cette fonction
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../../assets/vendor/css/core.css">
  <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
</head>

<body class="bg-light p-3">

<div class="container py-3">

  <!-- FORMATION CARD -->
  <div class="card shadow-sm mb-4 border-0">

    <div class="row g-0">

      <!-- IMAGE -->
      <div class="col-md-4">
        <img src="/skiller5/<?= htmlspecialchars($formation['image']) ?>"
  
    class="img-fluid rounded-start h-100"
             style="object-fit:cover;">>
             
      </div>

      <!-- INFO -->
      <div class="col-md-8">
        <div class="card-body">

          <h3 class="fw-bold"><?= $formation['titre']; ?></h3>

          <p class="text-muted">
            <?= $formation['description']; ?>
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

          <p><strong>Créé par :</strong> <?= $formation['nom_propr']; ?></p>

          <p><strong>Date :</strong> <?= $formation['date_c']; ?></p>

          <p>
            <strong>Chapitres :</strong>
            <span class="badge bg-primary"><?= count($chapitres); ?></span>
          </p>

        </div>
      </div>

    </div>
  </div>

  <!-- CHAPITRES -->
  <div class="card shadow-sm border-0">

    <div class="card-header bg-white">
      <h5 class="mb-0">📚 Chapitres</h5>
    </div>

    <div class="card-body">

      <?php if($chapitres) { ?>
        <ul class="list-group list-group-flush">

          <?php foreach($chapitres as $ch) { ?>
            <li class="list-group-item d-flex justify-content-between">

              <span><?= $ch['titre_c']; ?></span>

              <span class="badge bg-label-primary">
                <?= $ch['ordre']; ?>
              </span>

            </li>
          <?php } ?>

        </ul>
      <?php } else { ?>
        <div class="alert alert-warning">
          Aucun chapitre disponible
        </div>
      <?php } ?>

    </div>

  </div>

</div>

</body>
</html>
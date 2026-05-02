<?php
function getYoutubeId($url) {
    preg_match(
        '/(youtu\.be\/|youtube\.com\/(watch\?v=|shorts\/|embed\/))([a-zA-Z0-9_-]{11})/',
        $url,
        $matches
    );
    return $matches[3] ?? null;
}
?>
<?php
include __DIR__ . '/../../../Controller/ChapitreController.php';
include __DIR__ . '/../../../Controller/FormationController.php';
include __DIR__ . '/../../../Controller/Chap_contenuController.php';

$id = $_GET['id'] ?? null;

if (!$id) {
  die("<div class='alert alert-danger'>ID manquant</div>");
}

// CHAPITRE
$chapitreC = new ChapitreController();
$chapitre = $chapitreC->getChapitreById($id);

if (!$chapitre) {
  die("<div class='alert alert-danger'>Chapitre introuvable</div>");
}

// FORMATION
$formationC = new FormationController();
$formation = $formationC->getFormationById($chapitre['id_f']);

// CONTENUS
$contenuC = new ChapContenuController();
$contenus = $contenuC->listContenusByChapitre($id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="../../assets/vendor/css/core.css">
<link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">

<style>
.content-body {
  display: none;
  padding: 10px;
}

.card-header {
  cursor: pointer;
}
</style>

</head>

<body class="bg-light">

<div class="container py-4">

  <!-- FORMATION -->
  <div class="card mb-3 bg-primary text-white">
    <div class="card-body">
      <h5>📚 Formation</h5>
      <h3><?= htmlspecialchars($formation['titre'] ?? '') ?></h3>
    </div>
  </div>

  <!-- CHAPITRE -->
  <div class="card mb-4">
    <div class="card-body">
      <h4>📘 <?= htmlspecialchars($chapitre['titre_c']) ?></h4>
      <span class="badge bg-primary">Ordre <?= $chapitre['ordre'] ?></span>
    </div>
  </div>

  <!-- CONTENUS -->
  <?php foreach ($contenus as $c) { ?>

    <div class="card mb-3 content-card">

      <!-- HEADER -->
      <div class="card-header d-flex justify-content-between align-items-center">

        <div>
          <span class="badge bg-dark"><?= $c['type_cc'] ?></span>
          <strong class="ms-2">Contenu #<?= $c['ordre_cc'] ?></strong>
        </div>

        <button class="btn btn-sm btn-outline-primary toggle-btn">▼</button>

      </div>

      <!-- BODY -->
      <div class="content-body">

        <?php if ($c['type_cc'] == 'text') { ?>

          <div><?= $c['contenu'] ?></div>

        <?php } elseif ($c['type_cc'] == 'image') { ?>

          <img src="/skiller6/<?= $c['contenu'] ?>" class="img-fluid rounded">

        <?php } elseif ($c['type_cc'] == 'video') { ?>

          <video class="w-100" controls>
            <source src="/skiller6/<?= $c['contenu'] ?>">
          </video>

           <?php } elseif ($c['type_cc'] == 'youtube') { ?>

    <?php $yt = getYoutubeId($c['contenu']); ?>

    <?php if ($yt) { ?>
      <iframe
        class="w-100"
        height="320"
        src="https://www.youtube.com/embed/<?= $yt ?>"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen>
      </iframe>
    <?php } ?>



        <?php } ?>

      </div>

    </div>

  <?php } ?>

</div>

<!-- ================= JS ================= -->


</body>
</html>
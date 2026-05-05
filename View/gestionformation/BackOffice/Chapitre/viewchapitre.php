<?php
session_start();
function getYoutubeId($url) {
    preg_match(
        '/(youtu\.be\/|youtube\.com\/(watch\?v=|shorts\/|embed\/))([a-zA-Z0-9_-]{11})/',
        $url,
        $matches
    );
    return $matches[3] ?? null;
}

include __DIR__ . '/../../../../Controller/ChapitreController.php';
include __DIR__ . '/../../../../Controller/FormationController.php';
include __DIR__ . '/../../../../Controller/Chap_contenuController.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID manquant");

// controllers
$chapitreC = new ChapitreController();
$chapitre = $chapitreC->getChapitreById($id);

$formationC = new FormationController();
$formation = $formationC->getFormationById($chapitre['id_f']);

$contenuC = new ChapContenuController();
$contenus = $contenuC->listContenusByChapitre($id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="../../assetsjs/assets/vendor/css/core.css">
<link rel="stylesheet" href="../../assetsjs/assets/vendor/css/theme-default.css">

<style>
body {
  background: #eef1f7;
  font-family: Arial;
}

/* PAGE PAPER */
.paper {
  background: white;
  padding: 40px;
  max-width: 950px;
  margin: auto;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* ITEM */
.content-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  border-bottom: 1px solid #eee;
}

/* hover effet */
.content-item:hover {
  background: #f9fbff;
}

/* flèches */
.arrows {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

/* bouton delete */
.delete-btn {
  margin-left: auto;
}

/* pdf */
.pdf-box {
  border: 1px solid #ddd;
  padding: 12px;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* ADD BUTTON */
.add-box {
  text-align: center;
  margin-top: 25px;
}
</style>

</head>

<body>

<div class="container py-4">

<!-- FORMATION -->
<div class="card mb-3 bg-primary text-white">
  <div class="card-body">
    <h5><?= htmlspecialchars($formation['titre']) ?></h5>
  </div>
</div>

<!-- CHAPITRE -->
<div class="card mb-4">
  <div class="card-body">
    <h4><?= htmlspecialchars($chapitre['titre_c']) ?></h4>
  </div>
</div>

<!-- PAPER -->


<?php foreach ($contenus as $c) { ?>

  <div class="content-item" data-id="<?= $c['id_cc'] ?>">

    <!-- FLÈCHES -->
    <div class="arrows">
      <button class="btn btn-light btn-sm">⬆</button>
      <button class="btn btn-light btn-sm">⬇</button>
    </div>

    <!-- CONTENU -->
    <div class="flex-grow-1">

      <?php if ($c['type_cc'] == 'text') { ?>
        <div><?= $c['contenu'] ?></div>
$
      <?php } elseif ($c['type_cc'] == 'youtube') { ?>
        <?php $yt = getYoutubeId($c['contenu']); ?>
        <iframe class="w-100" height="300"
          src="https://www.youtube.com/embed/<?= $yt ?>"></iframe>

      <?php } elseif ($c['type_cc'] == 'image') { ?>

    <img src="<?= UPLOAD_URL . htmlspecialchars($c['contenu']) ?>"
         class="img-fluid rounded">

      <?php } elseif ($c['type_cc'] == 'video') { ?>

          <video class="w-100" controls>
              <source src="<?= UPLOAD_URL . htmlspecialchars($c['contenu']) ?>">
          </video>

      <?php } elseif ($c['type_cc'] == 'pdf') { ?>

          <div class="pdf-box">
              <span>📄 PDF</span>

              <a href="<?= UPLOAD_URL . htmlspecialchars($c['contenu']) ?>"
                target="_blank"
                class="btn btn-primary btn-sm">
                  Visualiser
              </a>
          </div>

      <?php } ?>

    </div>

    <!-- DELETE BUTTON -->
    <a href="deleteContenu.php?id=<?= $c['id_cc'] ?>&chapitre=<?= $id ?>"
       class="btn btn-danger btn-sm delete-btn"
       onclick="return confirm('Supprimer ce contenu ?')">
      🗑
    </a>

  </div>

<?php } ?>

<!-- ADD BUTTON -->


</div>

</body>
</html>
<?php
include_once __DIR__ . '/../../../Controller/ChapitreController.php';
include_once __DIR__ . '/../../../Controller/Chap_contenuController.php';

$formation_id = $_GET['id'] ?? null;

if (!$formation_id) {
    die("<div class='alert alert-danger m-3'>ID formation manquant</div>");
}

$chapitreC = new ChapitreController();
$contenuC = new ChapContenuController();

$chapitres = $chapitreC->listChapitresByFormation($formation_id);

$currentIndex = isset($_GET['step']) ? (int)$_GET['step'] : 0;

if (!isset($chapitres[$currentIndex])) {
    echo "<div class='alert alert-success m-4 text-center'>
            🎉 Formation terminée avec succès !
          </div>";
    exit;
}

$chapitre = $chapitres[$currentIndex];
$contenus = $contenuC->listContenusByChapitre($chapitre['id_c']);

function getYoutubeId($url) {
    preg_match('/(youtu\.be\/|youtube\.com\/(watch\?v=|shorts\/|embed\/))([a-zA-Z0-9_-]{11})/', $url, $m);
    return $m[3] ?? null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Cours</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6f9;
}

.content-box {
    background: #fff;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.header-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.chapter-title {
    font-weight: 600;
}

.progress {
    height: 8px;
}
</style>
</head>

<body>

<div class="container py-4">

<!-- HEADER -->
<div class="header-card shadow-sm">

    <h4>📚 Formation en cours</h4>

    <div class="progress mb-3">
        <div class="progress-bar bg-success"
             style="width: <?= (($currentIndex+1)/count($chapitres))*100 ?>%">
        </div>
    </div>

    <h5 class="text-primary">
        Chapitre <?= $currentIndex + 1 ?> / <?= count($chapitres) ?>
    </h5>

    <h3 class="chapter-title">
        <?= htmlspecialchars($chapitre['titre_c']) ?>
    </h3>

</div>

<!-- CONTENU -->
<?php foreach ($contenus as $c): ?>

    <div class="content-box">

        <?php if ($c['type_cc'] == 'text'): ?>

            <div class="quill-content">
                <?= $c['contenu'] ?>
            </div>

        <?php elseif ($c['type_cc'] == 'image'): ?>

            <img src="/skiller6/<?= htmlspecialchars($c['contenu']) ?>" 
                 class="img-fluid rounded">

        <?php elseif ($c['type_cc'] == 'video'): ?>

            <video class="w-100 rounded" controls>
                <source src="/skiller6/<?= htmlspecialchars($c['contenu']) ?>">
            </video>

        <?php elseif ($c['type_cc'] == 'youtube'): ?>

            <?php $yt = getYoutubeId($c['contenu']); ?>

            <?php if ($yt): ?>
                <div class="ratio ratio-16x9">
                    <iframe src="https://www.youtube.com/embed/<?= $yt ?>" 
                            allowfullscreen></iframe>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

<?php endforeach; ?>

<!-- NAVIGATION -->
<div class="d-flex justify-content-between mt-4">

    <!-- PREV -->
    <?php if ($currentIndex > 0): ?>
        <a href="?id=<?= $formation_id ?>&step=<?= $currentIndex - 1 ?>" 
           class="btn btn-secondary">
            ⬅ Chapitre précédent
        </a>
    <?php else: ?>
        <div></div>
    <?php endif; ?>

    <!-- NEXT / FINISH -->
    <?php if (isset($chapitres[$currentIndex + 1])): ?>
        <a href="?id=<?= $formation_id ?>&step=<?= $currentIndex + 1 ?>" 
           class="btn btn-primary">
            Chapitre suivant ➡
        </a>
    <?php else: ?>
        <a href="certificat.php?id=<?= $formation_id ?>" 
        class="btn btn-success">
        🎉 Terminer la formation
        </a>
    <?php endif; ?>

</div>

</div>

</body>
</html>
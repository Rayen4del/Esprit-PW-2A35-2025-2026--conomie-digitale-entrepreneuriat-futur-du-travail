<?php
include_once __DIR__ . '/../../../../config.php';
include_once(__DIR__ . '/../../../../Controller/gestion_formation/FormationController.php');
include_once(__DIR__ . '/../../../../Controller/gestion_formation/ChapitreController.php');
include_once(__DIR__ . '/../../../../Controller/gestion_formation/TestController.php');
session_start();
$id = $_GET['id'] ?? null;
$isAjax = isset($_GET['ajax']);

// Guard
if (!$id) {
    echo "<div class='alert alert-danger p-3'>ID manquant</div>";
    exit;
}

// Controllers
$formationC = new FormationController();
$chapitreC = new ChapitreController();
$testC = new TestController();

// Data
$formation = $formationC->getFormationById($id);

if (!$formation) {
    echo "<div class='alert alert-danger p-3'>Formation introuvable</div>";
    exit;
}

$chapitres = $chapitreC->listChapitresByFormation($id);
$tests = $testC->listTestsByFormation($id);
?>

<?php if (!$isAjax): ?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../../assetsjs/assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assetsjs/assets/vendor/css/theme-default.css">
</head>
<body class="bg-light p-3">
<div class="container py-3">
<?php endif; ?>

<!-- ================= FORMATION ================= -->
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

                <h3 class="fw-bold"><?= htmlspecialchars($formation['titre']) ?></h3>

                <p class="text-muted">
                    <?= htmlspecialchars($formation['description']) ?>
                </p>

                <p>
                    <strong>État :</strong>
                    <?php if ($formation['etat'] == 'active') { ?>
                        <span class="badge bg-success">Active</span>
                    <?php } elseif ($formation['etat'] == 'inactive') { ?>
                        <span class="badge bg-danger">Inactive</span>
                    <?php } else { ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($formation['etat']) ?></span>
                    <?php } ?>
                </p>

                <p><strong>Créé par :</strong> <?= htmlspecialchars($formation['nom_propr']) ?></p>
                <p><strong>Date :</strong> <?= htmlspecialchars($formation['date_c']) ?></p>

                <p>
                    <strong>Chapitres :</strong>
                    <span class="badge bg-primary"><?= count($chapitres) ?></span>
                </p>

            </div>
        </div>

    </div>
</div>

<!-- ================= CHAPITRES ================= -->
<div class="card shadow-sm border-0 mb-3">

    <div class="card-header bg-white">
        <h5 class="mb-0">📚 Chapitres</h5>
    </div>

    <div class="card-body">

        <?php if (!empty($chapitres)) { ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($chapitres as $ch) { ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($ch['titre_c']) ?></span>
                        <span class="badge bg-primary">
                            <?= htmlspecialchars($ch['ordre']) ?>
                        </span>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <div class="alert alert-warning">Aucun chapitre</div>
        <?php } ?>

    </div>
</div>

<!-- ================= TEST ================= -->
<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <h5 class="mb-0">🧪 Tests</h5>
    </div>

    <div class="card-body">

        <?php if (!empty($tests)) { ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($tests as $test) { ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Test #<?= $test['id_t'] ?></span>
                        <span class="badge bg-primary">
                            Score min: <?= $test['score_min'] ?>
                        </span>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <div class="alert alert-warning">Aucun test</div>
        <?php } ?>

    </div>
</div>

<?php if (!$isAjax): ?>
</div>
</body>
</html>
<?php endif; ?>
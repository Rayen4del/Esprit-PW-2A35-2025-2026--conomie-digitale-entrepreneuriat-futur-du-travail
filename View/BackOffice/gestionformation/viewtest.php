<?php
include __DIR__ . '/../../../Controller/TestController.php';
include __DIR__ . '/../../../Controller/FormationController.php';
include __DIR__ . '/../../../Controller/ChapitreController.php';
include __DIR__ . '/../../../Controller/QuestionController.php';

$id = $_GET['id'] ?? null;

if (!$id) {
  die("<div class='alert alert-danger'>ID manquant</div>");
}

// TEST
$testC = new TestController();
$test = $testC->getTestById($id);

if (!$test) {
  die("<div class='alert alert-danger'>Test introuvable</div>");
}

// FORMATION
$formationC = new FormationController();
$formation = $formationC->getFormationById($test['id_f']);

// CHAPITRE
$chapitreC = new ChapitreController();
$chapitre = $chapitreC->getChapitreById($test['id_c']);

// QUESTIONS
$questionC = new QuestionController();
$questions = $questionC->listQuestionsByTest($id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="../../assets/vendor/css/core.css">
<link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">

<style>
.question-body {
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
  <div class="card mb-3">
    <div class="card-body">
      <h5>📘 Chapitre</h5>
      <h4><?= htmlspecialchars($chapitre['titre_c'] ?? '') ?></h4>
    </div>
  </div>

  <!-- TEST INFO -->
  <div class="card mb-4">
    <div class="card-body">
      <h4>🧪 Test</h4>

      <p><strong>Score minimum :</strong> <?= $test['score_min'] ?></p>
      <p><strong>Date création :</strong> <?= $test['date_creation'] ?></p>

    </div>
  </div>

  <!-- QUESTIONS -->
  <h5 class="mb-3">❓ Questions</h5>

  <?php foreach ($questions as $q) { ?>

    <div class="card mb-3 question-card">

      <!-- HEADER -->
      <div class="card-header d-flex justify-content-between align-items-center">

        <div>
          <span class="badge bg-dark"><?= $q['type'] ?></span>
          <strong class="ms-2">Question #<?= $q['id_q'] ?></strong>
        </div>

        <button class="btn btn-sm btn-outline-primary toggle-btn">▼</button>

      </div>

      <!-- BODY -->
      <div class="question-body">

        <p><strong>Question :</strong></p>
        <p><?= htmlspecialchars($q['contenu_q']) ?></p>

        <hr>

        <p><strong>Réponse :</strong></p>
        <p class="text-success"><?= htmlspecialchars($q['repence']) ?></p>

      </div>

    </div>

  <?php } ?>

</div>

<!-- JS toggle -->
<script>
document.addEventListener("click", function (e) {

  const btn = e.target.closest(".toggle-btn");
  if (!btn) return;

  const card = btn.closest(".question-card");
  const body = card.querySelector(".question-body");

  if (!body) return;

  if (body.style.display === "block") {
    body.style.display = "none";
    btn.innerHTML = "▼";
  } else {
    body.style.display = "block";
    btn.innerHTML = "▲";
  }

});
</script>

</body>
</html>
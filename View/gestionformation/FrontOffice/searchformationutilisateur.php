<?php
include __DIR__ . '/../../../Controller/FormationController.php';

$formationC = new FormationController();

$search = $_GET['search'] ?? "";
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$sort = 'ASC';

$result = $formationC->searchActivePaginated($search, $page, $limit, $sort);

$list = $result['data'];
$totalPages = $result['totalPages'];
$currentPage = $result['currentPage'];
?>

<div class="row">

<?php if (!empty($list)) { ?>

<?php foreach ($list as $formation) { ?>

<?php if ($formation['etat'] == 'active') { ?> <!-- 🔥 IMPORTANT -->

<div class="col-md-4 mb-4">

  <div class="card h-100 shadow-sm">

    <!-- IMAGE -->
    <img
      src="<?= UPLOAD_URL . htmlspecialchars($formation['image'] ?? 'default.png') ?>"
      class="card-img-top"
      style="height:200px;object-fit:cover;"
    >

    <div class="card-body d-flex flex-column">

      <!-- TITRE -->
      <div class="d-flex justify-content-between align-items-center">

      <h5 class="mb-0">
        <?= htmlspecialchars($formation['titre']) ?>
      </h5>

      <!-- ICON CALENDAR -->
      <button class="btn btn-sm btn-outline-primary"
              onclick="openCalendar(<?= $formation['id_f'] ?>)">
        📅
      </button>

    </div>

      <!-- OWNER -->
      <p class="text-muted">
        👤 <?= htmlspecialchars($formation['nom_propr']) ?>
      </p>

      <!-- NOTE -->
      <p>
        ⭐ <span class="badge bg-info">
          <?= htmlspecialchars($formation['evaluation']) ?>
        </span>
      </p>

      <!-- BUTTON -->
      <button class="btn btn-primary mt-auto"
        onclick="openView(<?= $formation['id_f'] ?>)">
        Voir détails
      </button>
    </div>

  </div>

</div>

<?php } ?>

<?php } ?>

<?php } else { ?>

<div class="col-12 text-center">
  <h5>Aucune formation trouvée</h5>
</div>

<?php } ?>

</div>

<!-- 🔥 PAGINATION -->
<nav>
<ul class="pagination justify-content-center">

<li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
  <a class="page-link" href="#" onclick="loadPage(<?= $currentPage - 1 ?>)">«</a>
</li>

<?php for($i = 1; $i <= $totalPages; $i++): ?>
<li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
  <a class="page-link" href="#" onclick="loadPage(<?= $i ?>)">
    <?= $i ?>
  </a>
</li>
<?php endfor; ?>

<li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
  <a class="page-link" href="#" onclick="loadPage(<?= $currentPage + 1 ?>)">»</a>
</li>

</ul>
</nav>
<?php
include __DIR__ . '/../../../Controller/FormationController.php';
session_start();
$formationC = new FormationController();

$search = $_GET['search'] ?? "";
$page = max(1, intval($_GET['page'] ?? 1));
if ($page == 1) {
    $limit = 8; // car 1 carte sera "+"
} else {
    $limit = 9;
}
$sort = 'ASC';

$userId = 1;

$result = $formationC->searchActiveByUser($search, $userId, $page, $limit, $sort);

$list = $result['data'];
$totalPages = $result['totalPages'];
$currentPage = $result['currentPage'];
?>

<div class="row">
<?php if ($currentPage == 1) { ?>
<div class="col-md-4 mb-4">

  <div class="card h-100 shadow-sm add-card text-muted"
       onclick="window.location.href='ajouterFormation.php'"
       style="cursor:pointer;">

    <!-- IMAGE -->
    <div class="position-relative">
      <img src="/skiller6/uploads/default.png"
           class="card-img-top"
           style="height:200px;object-fit:cover;filter: brightness(60%);">

      <!-- ICON + -->
      <div class="position-absolute top-50 start-50 translate-middle text-white">
        <h1 style="font-size:60px;">+</h1>
      </div>
    </div>

    <div class="card-body d-flex flex-column">

      <!-- TITRE -->
      <h5 class="text-secondary">Nouvelle formation</h5>

      <!-- OWNER -->
      <p class="text-muted">
        👤 ---
      </p>

      <!-- NOTE -->
      <p>
        ⭐ <span class="badge bg-secondary">--</span>
      </p>

    </div>

  </div>

</div>
<?php } ?>
    <?php if (!empty($list)) { ?>

    <?php foreach ($list as $formation): ?>

    <?php if ($formation['created_by'] != $userId) continue; ?>

    <div class="col-md-4 mb-4">

        <div class="card h-100 shadow-sm">

            <div class="position-relative">
                <img src="<?= UPLOAD_URL . htmlspecialchars($formation['image'] ?? 'default.png') ?>"
                    class="card-img-top"
                    style="height:200px;object-fit:cover;">

                <div class="position-absolute bottom-0 w-100 p-2"
                    style="background: linear-gradient(transparent, rgba(13,110,253,0.8));">
                    <small class="text-white">
                    👤 <?= htmlspecialchars($formation['nom_propr']) ?>
                    </small>
                </div>
                </div>
            <div class="card-body d-flex flex-column">

                <h5><?= htmlspecialchars($formation['titre']) ?></h5>
               <p>
                ⭐ <span class="badge bg-warning text-dark">
                    <?= htmlspecialchars($formation['evaluation']) ?>
                </span>
                </p>

               <div class="d-flex mt-auto">

                    <button class="btn btn-primary flex-fill rounded-0"
                        data-bs-toggle="modal"
                        data-bs-target="#modalViewFormation"
                        onclick="openView(<?= $formation['id_f'] ?>)">
                        👁
                    </button>

                    <button class="btn btn-warning flex-fill rounded-0 text-white"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditFormation"
                        onclick='openEdit(<?= json_encode($formation) ?>)'>
                        ✏
                    </button>

                    <a href="deleteFormation.php?id=<?= $formation['id_f'] ?>&deleted=1"
                        class="btn btn-danger flex-fill rounded-0">
                        🗑
                    </a>

                    </div>

            </div>

        </div>

    </div>

    <?php endforeach; ?>

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
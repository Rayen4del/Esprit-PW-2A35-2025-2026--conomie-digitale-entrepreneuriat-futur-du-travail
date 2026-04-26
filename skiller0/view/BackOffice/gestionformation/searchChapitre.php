<?php
include __DIR__ . '/../../../Controller/ChapitreController.php';

$chapitreC = new ChapitreController();

$search = $_GET['search'] ?? "";
$page = $_GET['page'] ?? 1;
$limit = 10;
$sort = $_GET['sort'] ?? 'ASC';

$result = $chapitreC->searchPaginated($search, $page, $limit, $sort);

$list = $result['data'];
$totalPages = $result['totalPages'];
$currentPage = $result['currentPage'];
?>

<table class="table table-hover align-middle table table-striped text-center">

  <thead class="table-light">
    <tr>
      <th>ID</th>
      <th>Formation</th>
      <th>Titre Chapitre</th>
      <th>Ordre</th>
      <th class="text-end">Actions</th>
    </tr>
  </thead>

  <tbody class="table-border-bottom-0">

    <!-- 🔁 ROWS -->
    <?php foreach($list as $chapitre): ?>
    <tr>

      <td><strong><?= htmlspecialchars($chapitre['id_c']) ?></strong></td>

      <td><?= htmlspecialchars($chapitre['id_f']) ?></td>

      <td><?= htmlspecialchars($chapitre['titre_c']) ?></td>

      <td>
        <span class="badge bg-info">
          <?= $chapitre['ordre'] ?>
        </span>
      </td>

      <td class="text-end">

        <div class="d-flex justify-content-end gap-2">

          <!-- VIEW -->
          <button class="btn btn-sm btn-outline-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalViewChapitre"
            onclick="openView(<?= $chapitre['id_c'] ?>)">
            <i class="bx bx-show"></i>
          </button>

          <!-- EDIT -->
          <button class="btn btn-sm btn-outline-warning"
            data-bs-toggle="modal"
            data-bs-target="#modalEditChapitre"
            onclick='openEdit(<?= json_encode($chapitre) ?>)'>
            <i class="bx bx-edit"></i>
          </button>

          <!-- DELETE -->
          <a href="deleteChapitre.php?id=<?= $chapitre['id_c'] ?>&deleted=1"
            class="btn btn-sm btn-outline-danger">
            <i class="bx bx-trash"></i>
          </a>

        </div>

      </td>
    </tr>
    <?php endforeach; ?>

  </tbody>
</table>

<!-- 🔥 PAGINATION -->
<br>

<nav>
<ul class="pagination justify-content-center">

<!-- ⬅️ PREV -->
<li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
  <a href="#" class="page-link"
     onclick="loadPage(<?= $currentPage - 1 ?>)">
    <---
  </a>
</li>

<!-- NUMBERS -->
<?php for($i = 1; $i <= $totalPages; $i++): ?>
<li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
  <a href="#" class="page-link"
     onclick="loadPage(<?= $i ?>)">
    <?= $i ?>
  </a>
</li>
<?php endfor; ?>

<!-- ➡️ NEXT -->
<li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
  <a href="#" class="page-link"
     onclick="loadPage(<?= $currentPage + 1 ?>)">
    --->
  </a>
</li>

</ul>
</nav>
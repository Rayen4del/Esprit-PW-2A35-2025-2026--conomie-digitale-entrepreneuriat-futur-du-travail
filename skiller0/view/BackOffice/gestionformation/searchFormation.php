<?php
include __DIR__ . '/../../../Controller/FormationController.php';

$formationC = new FormationController();

$search = $_GET['search'] ?? "";
$page = $_GET['page'] ?? 1;
$limit = 10;
$sort = $_GET['sort'] ?? 'ASC';

$result = $formationC->searchPaginated($search, $page, $limit, $sort);

$list = $result['data'];
$totalPages = $result['totalPages'];
$currentPage = $result['currentPage'];
?>

 <table   class=" table table-hover align-middle table table-striped align-middle text-center">
                    <thead class="table-light">
                      <tr>
                        <th>Formation</th>
                        <th>Owner</th>
                        <th>Image</th>
                        <th>Evaluation</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody  class="table-border-bottom-0">
<!-- 🔁 TABLE ROWS -->
<?php foreach($list as $formation): ?>
<tr>
  <td><strong><?= htmlspecialchars($formation['titre']) ?></strong></td>
  <td><?= htmlspecialchars($formation['nom_propr']) ?></td>
    <td>
    <img src="/skiller5/<?= htmlspecialchars($formation['image']) ?>"
     width="60"
     height="60"
     class="rounded shadow-sm"
     style="object-fit:cover;">
  </td>
   <td>
    <span class="badge bg-info">
      <?= $formation['evaluation'] ?>
    </span>
  </td>
    <td>
    <?php if($formation['etat'] == 'active'): ?>
      <span class="badge bg-success px-3 py-2">Active</span>

    <?php elseif($formation['etat'] == 'inactive'): ?>
      <span class="badge bg-danger px-3 py-2">Inactive</span>

    <?php else: ?>
      <span class="badge bg-secondary px-3 py-2">
        <?= $formation['etat'] ?>
      </span>
    <?php endif; ?>
  </td>
  <td class="text-end">
    <div class="d-flex justify-content-end gap-2">

      <button class="btn btn-sm btn-outline-primary"
        data-bs-toggle="modal"
        data-bs-target="#modalViewFormation"
        onclick="openView(<?= $formation['id_f'] ?>)">
        <i class="bx bx-show"></i>
      </button>

      <button class="btn btn-sm btn-outline-warning"
        data-bs-toggle="modal"
        data-bs-target="#modalEditFormation"
        onclick='openEdit(<?= json_encode($formation) ?>)'>
        <i class="bx bx-edit"></i>
      </button>

      <a href="deleteFormation.php?id=<?= $formation['id_f'] ?>&deleted=1"
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
  <a href="#" class="page-link" onclick="loadPage(<?= $i ?>)">
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

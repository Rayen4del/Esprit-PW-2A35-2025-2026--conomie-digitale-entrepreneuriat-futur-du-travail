<?php
include __DIR__ . '/../../../Controller/TestController.php';

$testC = new TestController();

$search = $_GET['search'] ?? '';
$page   = $_GET['page'] ?? 1;
$sort   = $_GET['sort'] ?? 'ASC';

$limit = 5;
$offset = ($page - 1) * $limit;

// récupérer tests filtrés
$tests = $testC->searchTests($search, $limit, $offset, $sort);

// compter total (pagination)
$total = $testC->countTests($search);
$pages = ceil($total / $limit);
?>

<!-- TABLE -->
<table class="table table-striped">

  <thead>
    <tr>
      <th>ID</th>
      <th>Chapitre</th>
      <th>Formation</th>
      <th>Score min</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>
  </thead>

  <tbody>

    <?php foreach ($tests as $t) { ?>

      <tr>

        <td><?= $t['id_t'] ?></td>
        <td><?= $t['id_c'] ?></td>
        <td><?= $t['id_f'] ?></td>
        <td><?= $t['score_min'] ?></td>
        <td><?= $t['date_creation'] ?></td>


          <td>

            <!-- VIEW -->
            <button class="btn btn-sm btn-outline-info"
                    onclick="openView(<?= $t['id_t'] ?>)">
              <i class="bx bx-show"></i>
            </button>

            <!-- EDIT -->
            <button class="btn btn-sm btn-outline-warning"
                    onclick='openEdit(<?= json_encode($t) ?>)'>
              <i class="bx bx-edit"></i>
            </button>

            <!-- DELETE -->
            <a href="deletetest.php?id=<?= $t['id_t'] ?>"
              class="btn btn-sm btn-outline-danger"
              onclick="return confirm('Supprimer ce test ?')">
              <i class="bx bx-trash"></i>
            </a>

          </td>
      

      </tr>

    <?php } ?>

  </tbody>

</table>

<!-- PAGINATION -->
 <br>
<nav>
  <ul class="pagination justify-content-center">

    <!-- ⬅️ PREV -->
    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
      <a href="#" class="page-link"
         onclick="loadPage(<?= $page - 1 ?>)">
        <---
      </a>
    </li>

    <!-- NUMBERS -->
    <?php for ($i = 1; $i <= $pages; $i++): ?>

      <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">

        <a href="#" class="page-link"
           onclick="loadPage(<?= $i ?>)">
          <?= $i ?>
        </a>

      </li>

    <?php endfor; ?>

    <!-- ➡️ NEXT -->
    <li class="page-item <?= ($page >= $pages) ? 'disabled' : '' ?>">
      <a href="#" class="page-link"
         onclick="loadPage(<?= $page + 1 ?>)">
        --->
      </a>
    </li>

  </ul>
</nav>
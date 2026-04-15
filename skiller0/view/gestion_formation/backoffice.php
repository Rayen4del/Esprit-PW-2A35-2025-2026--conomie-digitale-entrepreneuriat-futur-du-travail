<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("../../config.php");
require_once("../../controle/FormationController.php");

$controller = new FormationController($conn);

$errors = [];
$editMode = false;
$data = [];

/* =======================
   DELETE
======================= */
if (isset($_GET['delete'])) {
    $controller->delete($_GET['delete']);
    header("Location: backoffice.php");
    exit();
}

/* =======================
   EDIT MODE
======================= */
if (isset($_GET['edit'])) {
    $editMode = true;
    $data = $controller->get($_GET['edit']);
}

/* =======================
   ADD
======================= */
if (isset($_POST['submit'])) {
    $controller->add($_POST, $_FILES);
    header("Location: backoffice.php");
    exit();
}

/* =======================
   UPDATE
======================= */
if (isset($_POST['update'])) {
    $controller->update($_POST);
    header("Location: backoffice.php");
    exit();
}

/* =======================
   LIST
======================= */
$result = $controller->list();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BackOffice Formation</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/vendor/css/core.css">
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css">
</head>

<body>

<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">

<!-- SIDEBAR -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <div class="app-brand demo">
        <span class="app-brand-text menu-text fw-bolder ms-2">Gestion</span>
    </div>

    <ul class="menu-inner py-1">

        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <li class="menu-item active open">
            <a class="menu-link menu-toggle">
                <i class="menu-icon bx bx-book"></i>
                <div>Formations</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="frontoffice.php" class="menu-link">Front Office</a>
                </li>
                <li class="menu-item active">
                    <a href="backoffice.php" class="menu-link">Back Office</a>
                </li>
            </ul>
        </li>

    </ul>
</aside>

<!-- CONTENT -->
<div class="layout-page">

<div class="content-wrapper">
<div class="container-fluid py-4">

<h2 class="mb-4">Gestion des formations</h2>

<!-- ERRORS -->
<?php if (!empty($errors)) { ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $e) { ?>
        <div><?= $e ?></div>
    <?php } ?>
</div>
<?php } ?>

<!-- FORM -->
<div class="card mb-4 shadow-sm">
<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?= $editMode ? $data['ID_formation'] : '' ?>">

<div class="mb-3">
    <label>Titre</label>
    <input type="text" name="titre" class="form-control"
    value="<?= $editMode ? $data['titre'] : '' ?>">
</div>

<div class="mb-3">
    <label>Description</label>
    <textarea name="description" class="form-control"><?= $editMode ? $data['description'] : '' ?></textarea>
</div>

<div class="mb-3">
    <label>Domaine</label>
    <input type="text" name="domaine" class="form-control"
    value="<?= $editMode ? $data['domaine'] : '' ?>">
</div>

<div class="mb-3">
    <label>Date</label>
    <input type="date" name="date_creation" class="form-control"
    value="<?= $editMode ? $data['date_creation'] : '' ?>">
</div>

<div class="mb-3">
    <label>Etat</label>
    <input type="text" name="etat" class="form-control"
    value="<?= $editMode ? $data['etat'] : '' ?>">
</div>

<?php if (!$editMode) { ?>
<div class="mb-3">
    <label>Image</label>
    <input type="file" name="image" class="form-control">
</div>
<?php } ?>

<button class="btn btn-primary" type="submit"
name="<?= $editMode ? 'update' : 'submit' ?>">
    <?= $editMode ? 'Modifier' : 'Ajouter' ?>
</button>

</form>

</div>
</div>

<!-- TABLE -->
<div class="card shadow-sm">
<div class="card-body table-responsive">

<table class="table table-hover align-middle">
<thead>
<tr>
    <th>ID</th>
    <th>Titre</th>
    <th>Domaine</th>
    <th>Date</th>
    <th>Etat</th>
    <th>Image</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php if ($result && $result->num_rows > 0) { ?>
<?php while ($row = $result->fetch_assoc()) { ?>

<tr>
    <td><?= $row['ID_formation'] ?></td>
    <td><?= $row['titre'] ?></td>
    <td><?= $row['domaine'] ?></td>
    <td><?= $row['date_creation'] ?></td>
    <td><span class="badge bg-success"><?= $row['etat'] ?></span></td>
    <td>
        <img src="uploads/<?= $row['image'] ?>" width="60" height="60">
    </td>
    <td>
        <a href="?edit=<?= $row['ID_formation'] ?>" class="btn btn-warning btn-sm">Edit</a>
        <a href="?delete=<?= $row['ID_formation'] ?>" class="btn btn-danger btn-sm"
        onclick="return confirm('Supprimer ?')">Delete</a>
    </td>
</tr>

<?php } ?>
<?php } ?>

</tbody>
</table>

</div>
</div>

</div>
</div>

</div>
</div>
</div>

</body>
</html>
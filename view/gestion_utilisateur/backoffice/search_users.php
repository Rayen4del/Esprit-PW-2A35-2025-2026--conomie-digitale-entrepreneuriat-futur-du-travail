<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';

header('Content-Type: application/json; charset=UTF-8');

$userC = new UserController();

$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$sortDate = $_GET['sortDate'] ?? 'desc';
$sortName = $_GET['sortName'] ?? 'none';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = max(1, (int)($_GET['perPage'] ?? 10));

$allowedSortDate = ['asc', 'desc'];
$allowedSortName = ['none', 'asc', 'desc'];

if (!in_array($sortDate, $allowedSortDate, true)) {
    $sortDate = 'desc';
}

if (!in_array($sortName, $allowedSortName, true)) {
    $sortName = 'none';
}

$total = $userC->countFilteredUsers($type, $status, $q);
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$users = $userC->filterUsersPaginated($type, $status, $perPage, $offset, $q, $sortDate, $sortName);

$rows = '';
if ($users && $users->rowCount() > 0) {
    while ($user = $users->fetch()) {
        $rows .= '<tr>';
        $rows .= '<td><span class="badge bg-primary">#' . htmlspecialchars($user['ID']) . '</span></td>';
        $rows .= '<td><strong>' . htmlspecialchars($user['Nom']) . '</strong></td>';
        $rows .= '<td><i class="fas fa-envelope text-muted me-2"></i><small>' . htmlspecialchars($user['Email']) . '</small></td>';

        $rows .= '<td>';
        if ($user['Type'] == 'admin') {
            $rows .= '<span class="badge bg-danger"><i class="fas fa-shield-alt me-1"></i>Administrateur</span>';
        } elseif ($user['Type'] == 'professionnel') {
            $rows .= '<span class="badge bg-success"><i class="fas fa-briefcase me-1"></i>Professionnel</span>';
        } else {
            $rows .= '<span class="badge bg-info"><i class="fas fa-graduation-cap me-1"></i>Étudiant</span>';
        }
        $rows .= '</td>';

        $rows .= '<td>';
        if ($user['Statut'] == 'actif') {
            $rows .= '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Actif</span>';
        } else {
            $rows .= '<span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Suspendu</span>';
        }
        $rows .= '</td>';

        $created = isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '';
        $rows .= '<td><small class="text-muted">' . $created . '</small></td>';

        $rows .= '<td class="text-center">';
        $rows .= '<a href="export_user.php?id=' . urlencode($user['ID']) . '&format=csv" class="btn btn-sm btn-success" title="Exporter CSV"><i class="fas fa-file-csv"></i></a> ';
        $rows .= '<a href="export_user.php?id=' . urlencode($user['ID']) . '&format=pdf" class="btn btn-sm btn-danger" title="Exporter PDF"><i class="fas fa-file-pdf"></i></a> ';
        $rows .= '<a href="edit_user.php?id=' . urlencode($user['ID']) . '" class="btn btn-sm btn-primary" title="Modifier"><i class="fas fa-edit"></i></a> ';
        if ($user['ID'] != $_SESSION['user_id']) {
            $rows .= '<a href="delete_user.php?id=' . urlencode($user['ID']) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cet utilisateur ?\')" title="Supprimer"><i class="fas fa-trash"></i></a>';
        } else {
            $rows .= '<button class="btn btn-sm btn-secondary" disabled title="Vous ne pouvez pas vous supprimer vous-même"><i class="fas fa-lock"></i></button>';
        }
        $rows .= '</td>';

        $rows .= '</tr>';
    }
} else {
    $rows .= '<tr><td colspan="7" class="text-center py-4"><i class="fas fa-inbox text-muted" style="font-size: 2rem; opacity: 0.5;"></i><p class="text-muted mt-2">Aucun utilisateur trouvé</p></td></tr>';
}

// Build pagination HTML
$pagination = '';
if ($totalPages > 1) {
    $baseParams = ['type' => $type, 'status' => $status];

    $pagesArr = [];
    $pagesArr[] = 1;
    for ($p = max(2, $page - 1); $p <= min($totalPages - 1, $page + 1); $p++) {
        $pagesArr[] = $p;
    }
    if ($totalPages > 1) $pagesArr[] = $totalPages;
    $pagesArr = array_values(array_unique($pagesArr));
    sort($pagesArr);

    $pagination .= '<nav aria-label="Pagination utilisateurs"><ul class="pagination justify-content-end mb-0 flex-wrap">';

    $disabledPrev = $page <= 1 ? ' disabled' : '';
    $pagination .= '<li class="page-item' . $disabledPrev . '"><a class="page-link" href="#" data-page="' . max(1, $page - 1) . '">Précédent</a></li>';

    $previous = null;
    foreach ($pagesArr as $p) {
        if ($previous !== null && $p > $previous + 1) {
            $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $active = $p === $page ? ' active' : '';
        $pagination .= '<li class="page-item' . $active . '"><a class="page-link" href="#" data-page="' . $p . '">' . $p . '</a></li>';
        $previous = $p;
    }

    $disabledNext = $page >= $totalPages ? ' disabled' : '';
    $pagination .= '<li class="page-item' . $disabledNext . '"><a class="page-link" href="#" data-page="' . min($totalPages, $page + 1) . '">Suivant</a></li>';

    $pagination .= '</ul></nav>';
}

echo json_encode(['rows' => $rows, 'pagination' => $pagination, 'total' => $total]);

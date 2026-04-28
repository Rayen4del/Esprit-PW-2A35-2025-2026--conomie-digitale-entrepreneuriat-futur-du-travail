<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

if (!isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'admin') {
    header('Location: ../frontoffice/profil.php');
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';

$userC = new UserController();

$typeFilter = $_GET['type'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');
$sortDate = $_GET['sortDate'] ?? 'desc';
$sortName = $_GET['sortName'] ?? 'none';
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$allowedSortDate = ['asc', 'desc'];
$allowedSortName = ['none', 'asc', 'desc'];

if (!in_array($sortDate, $allowedSortDate, true)) {
    $sortDate = 'desc';
}

if (!in_array($sortName, $allowedSortName, true)) {
    $sortName = 'none';
}

$totalUsers = $userC->countFilteredUsers($typeFilter, $statusFilter);
$totalPages = max(1, (int) ceil($totalUsers / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;

$users = $userC->filterUsersPaginated($typeFilter, $statusFilter, $perPage, $offset, null, $sortDate, $sortName);
$stats = $userC->getStats();

$exportBaseParams = [
    'type' => $typeFilter,
    'status' => $statusFilter,
    'sortDate' => $sortDate,
    'sortName' => $sortName,
    'q' => $searchQuery,
];

$csvExportUrl = 'export_users.php?' . http_build_query($exportBaseParams + ['format' => 'csv']);
$pdfExportUrl = 'export_users.php?' . http_build_query($exportBaseParams + ['format' => 'pdf']);

$etudiantCount = 0;
$proCount = 0;
$adminCount = 0;
$actifCount = 0;

foreach ($stats['by_type'] as $t) {
    if ($t['Type'] === 'etudiant') {
        $etudiantCount = (int) $t['count'];
    }
    if ($t['Type'] === 'professionnel') {
        $proCount = (int) $t['count'];
    }
    if ($t['Type'] === 'admin') {
        $adminCount = (int) $t['count'];
    }
}

foreach ($stats['by_status'] as $s) {
    if ($s['Statut'] === 'actif') {
        $actifCount = (int) $s['count'];
    }
}

$rolesTotal = (int) $stats['total'];
$adminPercent = $rolesTotal > 0 ? round(($adminCount / $rolesTotal) * 100, 1) : 0;
$etudiantPercent = $rolesTotal > 0 ? round(($etudiantCount / $rolesTotal) * 100, 1) : 0;
$proPercent = $rolesTotal > 0 ? max(0, round(100 - $adminPercent - $etudiantPercent, 1)) : 0;

$adminStop = $adminPercent;
$etudiantStop = $adminPercent + $etudiantPercent;
$donutGradient = $rolesTotal > 0
    ? 'conic-gradient(#5b63f6 0% ' . $adminStop . '%, #17b890 ' . $adminStop . '% ' . $etudiantStop . '%, #e2951a ' . $etudiantStop . '% 100%)'
    : '#d8dbe2';

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <style>
        .roles-chart-wrap {
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .roles-donut {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            position: relative;
            flex-shrink: 0;
        }

        .roles-donut::after {
            content: '';
            position: absolute;
            inset: 26px;
            border-radius: 50%;
            background: #fff;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.04);
        }

        .roles-donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            font-weight: 700;
            font-size: 1.5rem;
            color: #1f2d3d;
        }

        .roles-legend {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 0.6rem;
            color: #4c5b70;
            font-size: 1.05rem;
        }

        .roles-legend li {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .legend-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .roles-chart-wrap {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-dark vh-100 p-0 sidebar">
                <div class="text-center p-4">
                    <h4 class="text-white mb-1">
                        <i class="fas fa-graduation-cap text-primary"></i> Skiller
                    </h4>
                    <small class="text-muted d-block">Admin Panel</small>
                </div>
                <div class="px-3 py-2">
                    <small class="text-muted text-uppercase d-block mb-2" style="letter-spacing: 0.5px;">Menu</small>
                </div>
                <ul class="nav flex-column px-2">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-users me-3"></i> Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../frontoffice/profil.php">
                            <i class="fas fa-user me-3"></i> Mon Profil
                        </a>
                    </li>
                </ul>
                <div class="px-3 py-4 mt-5">
                    <a class="nav-link text-danger" href="../frontoffice/logout.php">
                        <i class="fas fa-sign-out-alt me-3"></i> Déconnexion
                    </a>
                </div>
            </nav>
            
            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
                    <div>
                        <h1 class="h2 mb-0">Dashboard</h1>
                        <small class="text-muted">Bienvenue, <?php echo htmlspecialchars($_SESSION['user_nom']); ?></small>
                    </div>
                </div>
                
                <!-- Messages flash -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <i class="fas fa-users" style="font-size: 2rem; color: var(--primary-color); opacity: 0.2; position: absolute; top: 1rem; right: 1rem;"></i>
                            <div class="stat-label">Total Utilisateurs</div>
                            <div class="stat-value"><?php echo $stats['total']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <i class="fas fa-graduation-cap" style="font-size: 2rem; color: var(--info-color); opacity: 0.2; position: absolute; top: 1rem; right: 1rem;"></i>
                            <div class="stat-label">Étudiants</div>
                            <div class="stat-value" style="color: var(--info-color);">
                                <?php echo $etudiantCount; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <i class="fas fa-briefcase" style="font-size: 2rem; color: var(--success-color); opacity: 0.2; position: absolute; top: 1rem; right: 1rem;"></i>
                            <div class="stat-label">Professionnels</div>
                            <div class="stat-value" style="color: var(--success-color);">
                                <?php echo $proCount; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--success-color); opacity: 0.2; position: absolute; top: 1rem; right: 1rem;"></i>
                            <div class="stat-label">Comptes Actifs</div>
                            <div class="stat-value" style="color: var(--success-color);">
                                <?php echo $actifCount; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphe de répartition des rôles -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition des rôles</h5>
                    </div>
                    <div class="card-body">
                        <div class="roles-chart-wrap">
                            <div class="roles-donut" style="background: <?php echo htmlspecialchars($donutGradient, ENT_QUOTES, 'UTF-8'); ?>;">
                                <div class="roles-donut-center"><?php echo $rolesTotal; ?></div>
                            </div>
                            <ul class="roles-legend">
                                <li>
                                    <span class="legend-dot" style="background:#5b63f6;"></span>
                                    Administrateurs — <?php echo $adminPercent; ?>% (<?php echo $adminCount; ?>)
                                </li>
                                <li>
                                    <span class="legend-dot" style="background:#17b890;"></span>
                                    Étudiants — <?php echo $etudiantPercent; ?>% (<?php echo $etudiantCount; ?>)
                                </li>
                                <li>
                                    <span class="legend-dot" style="background:#e2951a;"></span>
                                    Professionnels — <?php echo $proPercent; ?>% (<?php echo $proCount; ?>)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrer les utilisateurs</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-user-tag me-2"></i>Filtrer par rôle</label>
                                <select name="type" class="form-select">
                                    <option value="all" <?php echo $typeFilter == 'all' ? 'selected' : ''; ?>>Tous les rôles</option>
                                    <option value="etudiant" <?php echo $typeFilter == 'etudiant' ? 'selected' : ''; ?>>Étudiants</option>
                                    <option value="professionnel" <?php echo $typeFilter == 'professionnel' ? 'selected' : ''; ?>>Professionnels</option>
                                    <option value="admin" <?php echo $typeFilter == 'admin' ? 'selected' : ''; ?>>Administrateurs</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-toggle-on me-2"></i>Filtrer par statut</label>
                                <select name="status" class="form-select">
                                    <option value="all" <?php echo $statusFilter == 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                                    <option value="actif" <?php echo $statusFilter == 'actif' ? 'selected' : ''; ?>>Actifs</option>
                                    <option value="suspendu" <?php echo $statusFilter == 'suspendu' ? 'selected' : ''; ?>>Suspendus</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                            </div>
                            <div class="col-12"><hr class="my-1"></div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-calendar-alt me-2"></i>Tri par date</label>
                                <select name="sortDate" class="form-select">
                                    <option value="desc" <?php echo $sortDate === 'desc' ? 'selected' : ''; ?>>Plus récent d'abord</option>
                                    <option value="asc" <?php echo $sortDate === 'asc' ? 'selected' : ''; ?>>Plus ancien d'abord</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-font me-2"></i>Tri par nom</label>
                                <select name="sortName" class="form-select">
                                    <option value="none" <?php echo $sortName === 'none' ? 'selected' : ''; ?>>Aucun tri alphabétique</option>
                                    <option value="asc" <?php echo $sortName === 'asc' ? 'selected' : ''; ?>>Nom A → Z</option>
                                    <option value="desc" <?php echo $sortName === 'desc' ? 'selected' : ''; ?>>Nom Z → A</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tableau des utilisateurs -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Liste des utilisateurs (<span id="users-count"><?php echo $totalUsers; ?></span>)</h5>
                                <small class="text-muted"><?php echo $perPage; ?> utilisateurs par page</small>
                            </div>
                            <div class="ms-auto" style="min-width:260px;">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input id="user-search" type="search" class="form-control" value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Rechercher par nom ou email..." aria-label="Recherche utilisateurs">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Date inscription</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody">
                                    <?php if ($users && $users->rowCount() > 0): ?>
                                        <?php while ($user = $users->fetch()): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">#<?php echo $user['ID']; ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($user['Nom']); ?></strong>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope text-muted me-2"></i>
                                                    <small><?php echo htmlspecialchars($user['Email']); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($user['Type'] == 'admin'): ?>
                                                        <span class="badge bg-danger"><i class="fas fa-shield-alt me-1"></i>Administrateur</span>
                                                    <?php elseif ($user['Type'] == 'professionnel'): ?>
                                                        <span class="badge bg-success"><i class="fas fa-briefcase me-1"></i>Professionnel</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info"><i class="fas fa-graduation-cap me-1"></i>Étudiant</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['Statut'] == 'actif'): ?>
                                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Suspendu</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <a href="export_user.php?id=<?php echo $user['ID']; ?>&format=csv" 
                                                       class="btn btn-sm btn-success" title="Exporter CSV">
                                                        <i class="fas fa-file-csv"></i>
                                                    </a>
                                                    <a href="export_user.php?id=<?php echo $user['ID']; ?>&format=pdf" 
                                                       class="btn btn-sm btn-danger" title="Exporter PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <a href="edit_user.php?id=<?php echo $user['ID']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($user['ID'] != $_SESSION['user_id']): ?>
                                                        <a href="delete_user.php?id=<?php echo $user['ID']; ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')"
                                                           title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled title="Vous ne pouvez pas vous supprimer vous-même">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-inbox text-muted" style="font-size: 2rem; opacity: 0.5;"></i>
                                                <p class="text-muted mt-2">Aucun utilisateur trouvé</p>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="users-pagination">
                        <?php if ($totalPages > 1): ?>
                        <?php
                        $baseParams = [
                            'type' => $typeFilter,
                            'status' => $statusFilter,
                            'sortDate' => $sortDate,
                            'sortName' => $sortName,
                        ];

                        $pageUrl = function (int $page) use ($baseParams) {
                            $query = http_build_query($baseParams + ['page' => $page]);
                            return '?' . $query;
                        };

                        $pages = [];
                        $pages[] = 1;

                        for ($page = max(2, $currentPage - 1); $page <= min($totalPages - 1, $currentPage + 1); $page++) {
                            $pages[] = $page;
                        }

                        if ($totalPages > 1) {
                            $pages[] = $totalPages;
                        }

                        $pages = array_values(array_unique($pages));
                        sort($pages);
                        ?>
                        <div class="card-footer bg-white border-0 pt-0 pb-4 px-4">
                            <nav aria-label="Pagination utilisateurs">
                                <ul class="pagination justify-content-end mb-0 flex-wrap">
                                    <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo $currentPage <= 1 ? '#' : $pageUrl($currentPage - 1); ?>">Précédent</a>
                                    </li>

                                    <?php
                                    $previousPage = null;
                                    foreach ($pages as $page) {
                                        if ($previousPage !== null && $page > $previousPage + 1) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                        ?>
                                        <li class="page-item <?php echo $page === $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo $pageUrl($page); ?>"><?php echo $page; ?></a>
                                        </li>
                                        <?php
                                        $previousPage = $page;
                                    }
                                    ?>

                                    <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo $currentPage >= $totalPages ? '#' : $pageUrl($currentPage + 1); ?>">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
    (function(){
        const searchInput = document.getElementById('user-search');
        const tbody = document.getElementById('users-tbody');
        const paginationContainer = document.getElementById('users-pagination');
        const usersCount = document.getElementById('users-count');
        const typeSelect = document.querySelector('select[name="type"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const sortDateSelect = document.querySelector('select[name="sortDate"]');
        const sortNameSelect = document.querySelector('select[name="sortName"]');

        let debounceTimer = null;

        function doSearch(page = 1) {
            const q = searchInput.value.trim();
            const type = typeSelect ? typeSelect.value : 'all';
            const status = statusSelect ? statusSelect.value : 'all';
            const sortDate = sortDateSelect ? sortDateSelect.value : 'desc';
            const sortName = sortNameSelect ? sortNameSelect.value : 'none';
            const perPage = <?php echo $perPage; ?>;

            const params = new URLSearchParams({ q: q, type: type, status: status, sortDate: sortDate, sortName: sortName, page: page, perPage: perPage });
            fetch('search_users.php?' + params.toString(), { credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data.error) return;
                    tbody.innerHTML = data.rows;
                    paginationContainer.innerHTML = data.pagination;
                    if (usersCount) usersCount.textContent = data.total;
                }).catch(err => {
                    console.error('Search error', err);
                });
        }

        function scheduleSearch() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => doSearch(1), 300);
        }

        if (searchInput) searchInput.addEventListener('input', scheduleSearch);
        if (typeSelect) typeSelect.addEventListener('change', () => doSearch(1));
        if (statusSelect) statusSelect.addEventListener('change', () => doSearch(1));
        if (sortDateSelect) sortDateSelect.addEventListener('change', () => doSearch(1));
        if (sortNameSelect) sortNameSelect.addEventListener('change', () => doSearch(1));

        // Delegate clicks on pagination links inside the pagination container
        paginationContainer.addEventListener('click', function(e) {
            const link = e.target.closest('a[data-page]');
            if (!link) return;
            e.preventDefault();
            const page = parseInt(link.getAttribute('data-page') || '1', 10);
            doSearch(page);
        });
    })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
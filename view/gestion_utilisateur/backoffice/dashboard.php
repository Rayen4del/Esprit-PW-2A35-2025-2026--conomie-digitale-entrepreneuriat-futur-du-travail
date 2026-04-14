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

$users = $userC->filterUsers($typeFilter, $statusFilter);
$stats = $userC->getStats();

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
                                <?php 
                                $etudiantCount = 0;
                                foreach ($stats['by_type'] as $t) {
                                    if ($t['Type'] == 'etudiant') $etudiantCount = $t['count'];
                                }
                                echo $etudiantCount;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <i class="fas fa-briefcase" style="font-size: 2rem; color: var(--success-color); opacity: 0.2; position: absolute; top: 1rem; right: 1rem;"></i>
                            <div class="stat-label">Professionnels</div>
                            <div class="stat-value" style="color: var(--success-color);">
                                <?php 
                                $proCount = 0;
                                foreach ($stats['by_type'] as $t) {
                                    if ($t['Type'] == 'professionnel') $proCount = $t['count'];
                                }
                                echo $proCount;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--success-color); opacity: 0.2; position: absolute; top: 1rem; right: 1rem;"></i>
                            <div class="stat-label">Comptes Actifs</div>
                            <div class="stat-value" style="color: var(--success-color);">
                                <?php 
                                $actifCount = 0;
                                foreach ($stats['by_status'] as $s) {
                                    if ($s['Statut'] == 'actif') $actifCount = $s['count'];
                                }
                                echo $actifCount;
                                ?>
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
                        </form>
                    </div>
                </div>
                
                <!-- Tableau des utilisateurs -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Liste des utilisateurs (<?php echo $users->rowCount(); ?>)</h5>
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
                                <tbody>
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
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
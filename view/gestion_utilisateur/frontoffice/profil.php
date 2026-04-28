<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';
require_once __DIR__ . '/../../../controller/gestion_utilisateur/ProfilController.php';

$userC = new UserController();
$profilC = new ProfilController();

$userId = $_SESSION['user_id'];
$user = $userC->showUser($userId);
$profil = $profilC->getProfilByUserId($userId);

$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Skiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm position-sticky top-0" style="z-index: 100;">
            <div class="container-lg">
                <a class="navbar-brand fw-bold" href="#">
                    <i class="fas fa-graduation-cap text-primary"></i> <span style="color: var(--primary-color);">Skiller</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php if (strtolower($_SESSION['user_type']) === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../backoffice/dashboard.php">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard Admin
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profil.php">
                                <i class="fas fa-user me-1"></i>Mon Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="edit_profil.php">
                                <i class="fas fa-edit me-1"></i>Modifier Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <div class="container-lg mt-4">
            <?php if ($success == 'updated'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Profil mis à jour avec succès !
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <!-- Carte de profil -->
                <div class="col-lg-4">
                    <div class="card profile-card">
                        <?php if (!empty($profil['Photo'])): ?>
                            <img src="../../../uploads/<?php echo $profil['Photo']; ?>" 
                                 class="profile-avatar" alt="Photo de profil">
                        <?php else: ?>
                            <div class="profile-avatar placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4 class="profile-name"><?php echo htmlspecialchars($user['Nom']); ?></h4>
                        
                        <div class="mb-3">
                            <?php 
                            switch($user['Type']) {
                                case 'admin': 
                                    echo '<span class="badge bg-danger"><i class="fas fa-shield-alt me-1"></i>Administrateur</span>'; 
                                    break;
                                case 'professionnel': 
                                    echo '<span class="badge bg-success"><i class="fas fa-briefcase me-1"></i>Professionnel</span>'; 
                                    break;
                                default: 
                                    echo '<span class="badge bg-info"><i class="fas fa-graduation-cap me-1"></i>Étudiant</span>';
                            }
                            ?>
                        </div>
                        
                        <div class="mb-3">
                            <?php if ($user['Statut'] == 'actif'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Compte actif</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Compte suspendu</span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="edit_profil.php" class="btn btn-primary w-100">
                            <i class="fas fa-edit me-2"></i>Modifier mon profil
                        </a>
                    </div>
                </div>
                
                <!-- Informations personnelles -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations personnelles</h5>
                        </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Nom complet :</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php echo htmlspecialchars($user['Nom']); ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Email :</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php echo htmlspecialchars($user['Email']); ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Membre depuis :</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($profil['Localisation'])): ?>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Localisation :</strong>
                            </div>
                            <div class="col-sm-9">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($profil['Localisation']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($profil['Bio'])): ?>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Bio :</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php echo nl2br(htmlspecialchars($profil['Bio'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Statistiques :</strong>
                            </div>
                            <div class="col-sm-9">
                                <span class="badge bg-primary me-2">ID: <?php echo $user['ID']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
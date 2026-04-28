<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';
require_once __DIR__ . '/../../../controller/gestion_utilisateur/ProfilController.php';
require_once __DIR__ . '/../../../model/gestion_utilisateur/User.php';
require_once __DIR__ . '/../../../model/gestion_utilisateur/Profil.php';

$userC = new UserController();
$profilC = new ProfilController();

$userId = $_SESSION['user_id'];
$user = $userC->showUser($userId);
$profil = $profilC->getProfilByUserId($userId);

$error = '';
$success = '';

$uploadDir = __DIR__ . '/../../../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $localisation = trim($_POST['localisation'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    } elseif (strlen($nom) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères.";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    } elseif ($email !== $user['Email'] && $userC->emailExists($email)) {
        $errors[] = "Cet email est déjà utilisé par un autre compte.";
    }
    
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        } elseif (!preg_match('/[A-Z]/', $newPassword)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
        } elseif (!preg_match('/[0-9]/', $newPassword)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
    }
    
    $photoName = $profil['Photo'] ?? null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($fileExt, $allowed)) {
            $maxSize = 800 * 1024;
            if ($_FILES['photo']['size'] <= $maxSize) {
                $photoName = time() . '_' . uniqid() . '.' . $fileExt;
                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName);
                
                if (!empty($profil['Photo']) && file_exists($uploadDir . $profil['Photo'])) {
                    unlink($uploadDir . $profil['Photo']);
                }
            } else {
                $errors[] = "La photo ne doit pas dépasser 800KB.";
            }
        } else {
            $errors[] = "Format de photo non supporté (JPG, PNG, GIF uniquement).";
        }
    }
    
    if (empty($errors)) {
        $updatedUser = new User($userId, $nom, $email, null, $user['Type'], $user['Statut']);
        $userC->updateUser($updatedUser, $userId);
        
        if (!empty($newPassword)) {
            $userC->updatePassword($userId, $newPassword);
        }
        
        $profilObj = new Profil(null, $userId, $bio, $photoName, $localisation);
        $profilC->saveProfil($profilObj);
        
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_email'] = $email;
        
        $success = "Profil mis à jour avec succès !";
        
        $user = $userC->showUser($userId);
        $profil = $profilC->getProfilByUserId($userId);
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Mon Profil - Skiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link rel="stylesheet" href="../../../assets/css/forms.css">
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
                            <a class="nav-link active" href="edit_profil.php">
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
            <div class="row justify-content-center">
                <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier mon profil</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data" id="editProfilForm">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom complet *</label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($user['Nom']); ?>">
                                <div class="invalid-feedback" id="nomError"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($user['Email']); ?>">
                                <div class="invalid-feedback" id="emailError"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="localisation" class="form-label">Localisation</label>
                                <input type="text" class="form-control" id="localisation" name="localisation"
                                       value="<?php echo htmlspecialchars($profil['Localisation'] ?? ''); ?>"
                                       placeholder="Ex: Tunis, Tunisie">
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"
                                          placeholder="Parlez-nous un peu de vous..."><?php echo htmlspecialchars($profil['Bio'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo de profil</label>
                                <?php if (!empty($profil['Photo'])): ?>
                                    <div class="mb-2">
                                        <img src="../../../uploads/<?php echo $profil['Photo']; ?>" 
                                             style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                <small class="form-text text-muted">Formats acceptés : JPG, PNG, GIF. Max 800KB</small>
                                <div class="invalid-feedback" id="photoError"></div>
                            </div>
                            
                            <hr class="my-4">
                            <h6>Changer le mot de passe (optionnel)</h6>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <small class="form-text text-muted">Min 8 caractères, 1 majuscule, 1 chiffre</small>
                                <div class="invalid-feedback" id="passwordError"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <div class="invalid-feedback" id="confirmError"></div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="profil.php" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Validation JavaScript personnalisée
        document.getElementById('editProfilForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validation nom
            const nom = document.getElementById('nom').value.trim();
            if (nom === '') {
                showError('nom', 'Le nom est obligatoire');
                isValid = false;
            } else if (nom.length < 2) {
                showError('nom', 'Le nom doit contenir au moins 2 caractères');
                isValid = false;
            } else {
                clearError('nom');
            }
            
            // Validation email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
            if (email === '') {
                showError('email', 'L\'email est obligatoire');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                showError('email', 'Format d\'email invalide');
                isValid = false;
            } else {
                clearError('email');
            }
            
            // Validation mot de passe (si rempli)
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const uppercaseRegex = /[A-Z]/;
            const numberRegex = /[0-9]/;
            
            if (newPassword !== '') {
                if (newPassword.length < 8) {
                    showError('new_password', 'Le mot de passe doit contenir au moins 8 caractères');
                    isValid = false;
                } else if (!uppercaseRegex.test(newPassword)) {
                    showError('new_password', 'Le mot de passe doit contenir au moins une majuscule');
                    isValid = false;
                } else if (!numberRegex.test(newPassword)) {
                    showError('new_password', 'Le mot de passe doit contenir au moins un chiffre');
                    isValid = false;
                } else if (newPassword !== confirmPassword) {
                    showError('confirm_password', 'Les mots de passe ne correspondent pas');
                    isValid = false;
                } else {
                    clearError('new_password');
                    clearError('confirm_password');
                }
            }
            
            // Validation photo (taille)
            const photo = document.getElementById('photo').files[0];
            if (photo) {
                const maxSize = 800 * 1024; // 800KB
                if (photo.size > maxSize) {
                    showError('photo', 'La photo ne doit pas dépasser 800KB');
                    isValid = false;
                } else {
                    clearError('photo');
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('is-invalid');
            const errorDiv = document.getElementById(fieldId + 'Error');
            if (errorDiv) errorDiv.textContent = message;
        }
        
        function clearError(fieldId) {
            const field = document.getElementById(fieldId);
            field.classList.remove('is-invalid');
            const errorDiv = document.getElementById(fieldId + 'Error');
            if (errorDiv) errorDiv.textContent = '';
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
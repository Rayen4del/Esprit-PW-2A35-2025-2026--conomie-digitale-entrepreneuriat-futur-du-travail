<?php
// view/frontoffice/inscription.php
session_start();
require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';
require_once __DIR__ . '/../../../model/gestion_utilisateur/User.php';

$error = '';
$success = '';
$userC = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $confirm_mdp = $_POST['confirm_mdp'] ?? '';
    $type = $_POST['type'] ?? 'etudiant';
    
    // Validation personnalisée (pas HTML5 uniquement !)
    $errors = [];
    
    // Validation nom
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    } elseif (strlen($nom) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères.";
    }
    
    // Validation email
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    } elseif ($userC->emailExists($email)) {
        $errors[] = "Cet email est déjà utilisé.";
    }
    
    // Validation mot de passe (personnalisée)
    if (empty($mdp)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($mdp) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $mdp)) {
        $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
    } elseif (!preg_match('/[0-9]/', $mdp)) {
        $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
    }
    
    // Validation confirmation
    if ($mdp !== $confirm_mdp) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($errors)) {
        $user = new User(null, $nom, $email, $mdp, $type, 'actif');
        $userId = $userC->addUser($user);
        
        if ($userId) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $type;
            
            $success = "Inscription réussie ! Redirection...";
            header("refresh:2; url=profil.php");
        } else {
            $error = "Erreur lors de l'inscription.";
        }
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
    <title>Inscription - Skiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <style>
        .auth-bg {
            background: linear-gradient(135deg, #696cff 0%, #80c9f9 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="auth-bg">
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 550px;">
            <div class="auth-header">
                <i class="fas fa-user-plus mb-3" style="font-size: 3rem;"></i>
                <h2>Inscription</h2>
                <p>Rejoignez la communauté Skiller</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="registerForm" novalidate>
                    <div class="row">
                        <div class="col-12 auth-form-group">
                            <label for="nom" class="form-label">
                                <i class="fas fa-user me-1"></i>Nom complet *
                            </label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   placeholder="Jean Dupont"
                                   value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                            <div class="invalid-feedback d-block" id="nomError"></div>
                        </div>
                        
                        <div class="col-12 auth-form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email *
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="votre@email.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <div class="invalid-feedback d-block" id="emailError"></div>
                        </div>
                        
                        <div class="col-12 auth-form-group">
                            <label for="mdp" class="form-label">
                                <i class="fas fa-lock me-1"></i>Mot de passe *
                            </label>
                            <input type="password" class="form-control" id="mdp" name="mdp"
                                   placeholder="••••••••">
                            <small class="form-text text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i>Min 8 caractères, 1 majuscule, 1 chiffre
                            </small>
                            <div class="invalid-feedback d-block" id="mdpError"></div>
                        </div>
                        
                        <div class="col-12 auth-form-group">
                            <label for="confirm_mdp" class="form-label">
                                <i class="fas fa-check me-1"></i>Confirmer le mot de passe *
                            </label>
                            <input type="password" class="form-control" id="confirm_mdp" name="confirm_mdp"
                                   placeholder="••••••••">
                            <div class="invalid-feedback d-block" id="confirmError"></div>
                        </div>
                        
                        <div class="col-12 auth-form-group">
                            <label class="form-label">
                                <i class="fas fa-id-card me-1"></i>Type de compte *
                            </label>
                            <div class="d-flex gap-2">
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input" type="radio" name="type" id="type_student" 
                                           value="etudiant" 
                                           <?php echo (!isset($_POST['type']) || $_POST['type'] == 'etudiant') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="type_student">
                                        <i class="fas fa-graduation-cap me-1"></i>Étudiant
                                    </label>
                                </div>
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input" type="radio" name="type" id="type_pro" 
                                           value="professionnel"
                                           <?php echo (isset($_POST['type']) && $_POST['type'] == 'professionnel') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="type_pro">
                                        <i class="fas fa-briefcase me-1"></i>Professionnel
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-user-check me-2"></i>S'inscrire
                    </button>
                </form>
                
                <div class="auth-divider">
                    <span>Déjà un compte ?</span>
                </div>
                
                <a href="connexion.php" class="btn btn-outline-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </a>
                
                <div class="text-center mt-4">
                    <p class="text-muted mb-0">
                        <small>© 2024 Skiller. Tous droits réservés.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            let isValid = true;
            clearAllErrors();
            
            const nom = document.getElementById('nom').value.trim();
            const email = document.getElementById('email').value.trim();
            const mdp = document.getElementById('mdp').value;
            const confirm_mdp = document.getElementById('confirm_mdp').value;
            
            if (!nom || nom.length < 2) {
                showError('nom', nom ? 'Min 2 caractères' : 'Le nom est obligatoire');
                isValid = false;
            }
            
            if (!email || !validateEmail(email)) {
                showError('email', email ? 'Email invalide' : 'L\'email est obligatoire');
                isValid = false;
            }
            
            if (!mdp || mdp.length < 8) {
                showError('mdp', mdp ? 'Min 8 caractères' : 'Le mot de passe est obligatoire');
                isValid = false;
            } else if (!/[A-Z]/.test(mdp)) {
                showError('mdp', '1 majuscule requise');
                isValid = false;
            } else if (!/[0-9]/.test(mdp)) {
                showError('mdp', '1 chiffre requis');
                isValid = false;
            }
            
            if (mdp !== confirm_mdp) {
                showError('confirm_mdp', 'Les mots de passe ne correspondent pas');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('is-invalid');
            document.getElementById(fieldId + 'Error').textContent = message;
        }
        
        function clearAllErrors() {
            ['nom', 'email', 'mdp', 'confirm_mdp'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                field.classList.remove('is-invalid');
                document.getElementById(fieldId + 'Error').textContent = '';
            });
        }
    </script>
</body>
</html>
            </div>
        </div>
    </div>
    
    <script>
        // Validation JavaScript personnalisée (pas HTML5 uniquement)
        document.getElementById('registerForm').addEventListener('submit', function(e) {
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
            
            // Validation mot de passe
            const mdp = document.getElementById('mdp').value;
            const uppercaseRegex = /[A-Z]/;
            const numberRegex = /[0-9]/;
            
            if (mdp === '') {
                showError('mdp', 'Le mot de passe est obligatoire');
                isValid = false;
            } else if (mdp.length < 8) {
                showError('mdp', 'Le mot de passe doit contenir au moins 8 caractères');
                isValid = false;
            } else if (!uppercaseRegex.test(mdp)) {
                showError('mdp', 'Le mot de passe doit contenir au moins une majuscule');
                isValid = false;
            } else if (!numberRegex.test(mdp)) {
                showError('mdp', 'Le mot de passe doit contenir au moins un chiffre');
                isValid = false;
            } else {
                clearError('mdp');
            }
            
            // Validation confirmation
            const confirm = document.getElementById('confirm_mdp').value;
            if (confirm !== mdp) {
                showError('confirm', 'Les mots de passe ne correspondent pas');
                isValid = false;
            } else {
                clearError('confirm');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('is-invalid');
            document.getElementById(fieldId + 'Error').textContent = message;
        }
        
        function clearError(fieldId) {
            const field = document.getElementById(fieldId);
            field.classList.remove('is-invalid');
            document.getElementById(fieldId + 'Error').textContent = '';
        }
    </script>
</body>
</html>
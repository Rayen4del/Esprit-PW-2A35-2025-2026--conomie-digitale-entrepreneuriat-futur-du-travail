<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: profil.php');
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';

$error = '';
$userC = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    }
    
    if (empty($errors)) {
        $result = $userC->authenticate($email, $password);
        
        if ($result['success']) {
            $user = $result['user'];
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_nom'] = $user['Nom'];
            $_SESSION['user_email'] = $user['Email'];
            $_SESSION['user_type'] = $user['Type'];
            $_SESSION['user_statut'] = $user['Statut'];
            
            if ($remember) {
                setcookie('user_email', $email, time() + (86400 * 30), "/");
                setcookie('user_password', $password, time() + (86400 * 30), "/");
            }
            
            if (strtolower($user['Type']) === 'admin') {
                header('Location: ../backoffice/dashboard.php');
            } else {
                header('Location: profil.php');
            }
            exit;
        } else {
            $error = $result['message'];
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Skiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body class="auth-bg">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-graduation-cap mb-3" style="font-size: 3rem;"></i>
                <h2>Connexion</h2>
                <p>Accédez à votre compte Skiller</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm" novalidate>
                    <div class="auth-form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="votre@email.com"
                               value="<?php echo htmlspecialchars($_COOKIE['user_email'] ?? ''); ?>">
                        <div class="invalid-feedback d-block" id="emailError"></div>
                    </div>
                    
                    <div class="auth-form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Mot de passe
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="••••••••"
                               value="<?php echo htmlspecialchars($_COOKIE['user_password'] ?? ''); ?>">
                        <div class="invalid-feedback d-block" id="passwordError"></div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                    </button>
                </form>
                
                <div class="auth-divider">
                    <span>Nouveau sur Skiller ?</span>
                </div>
                
                <a href="inscription.php" class="btn btn-outline-primary w-100">
                    <i class="fas fa-user-plus me-2"></i>Créer un compte
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
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (email === '') {
                showError('email', 'L\'email est obligatoire');
                isValid = false;
            } else if (!validateEmail(email)) {
                showError('email', 'Email invalide');
                isValid = false;
            } else {
                clearError('email');
            }
            
            if (password === '') {
                showError('password', 'Le mot de passe est obligatoire');
                isValid = false;
            } else {
                clearError('password');
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
        
        function clearError(fieldId) {
            const field = document.getElementById(fieldId);
            field.classList.remove('is-invalid');
            document.getElementById(fieldId + 'Error').textContent = '';
        }
    </script>
</body>
</html>
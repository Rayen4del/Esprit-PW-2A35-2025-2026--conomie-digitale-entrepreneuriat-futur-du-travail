<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: view/gestion_utilisateur/frontoffice/profil.php');
    exit;
}

require_once __DIR__ . '/controller/gestion_utilisateur/UserController.php';

$message = '';
$message_type = '';
$token = $_GET['token'] ?? '';

$userC = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($token)) {
        $errors[] = "Token manquant.";
    }
    
    if (empty($new_password)) {
        $errors[] = "Le nouveau mot de passe est obligatoire.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($errors)) {
        $result = $userC->resetPassword($token, $new_password);
        
        if ($result['success']) {
            $message = "Mot de passe réinitialisé avec succès ! Redirection vers la connexion...";
            $message_type = 'success';
            // Rediriger vers la page de connexion après succès
            header('refresh:3;url=view/gestion_utilisateur/frontoffice/connexion.php');
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = 'error';
    }
} elseif (!empty($token)) {
    // Vérifier le token au chargement de la page
    $verification = $userC->verifyResetToken($token);
    if (!$verification['success']) {
        $message = $verification['message'];
        $message_type = 'error';
        $token = ''; // Désactiver le formulaire
    }
} else {
    $message = "Token manquant dans l'URL.";
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - Skiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-bg">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-lock mb-3" style="font-size: 3rem;"></i>
                <h2>Réinitialiser le mot de passe</h2>
                <p>Entrez votre nouveau mot de passe</p>
            </div>
            
            <div class="auth-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <?php if ($message_type !== 'success'): ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($message_type !== 'success' && !empty($token)): ?>
                <form method="POST" action="" id="resetForm" novalidate>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="auth-form-group">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-key me-1"></i>Nouveau mot de passe
                        </label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               placeholder="Min. 8 caractères, 1 majuscule, 1 chiffre" required>
                        <div class="invalid-feedback d-block" id="passwordError"></div>
                    </div>
                    
                    <div class="auth-form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-key me-1"></i>Confirmer le mot de passe
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirmez votre mot de passe" required>
                        <div class="invalid-feedback d-block" id="confirmError"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-check me-2"></i>Réinitialiser le mot de passe
                    </button>
                </form>
                <?php endif; ?>
                
                <div class="text-center">
                    <a href="view/gestion_utilisateur/frontoffice/connexion.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            const uppercaseRegex = /[A-Z]/;
            const numberRegex = /[0-9]/;
            
            let isValid = true;
            
            if (password === '') {
                showError('new_password', 'Le mot de passe est obligatoire');
                isValid = false;
            } else if (password.length < 8) {
                showError('new_password', 'Min. 8 caractères');
                isValid = false;
            } else if (!uppercaseRegex.test(password)) {
                showError('new_password', 'Au moins 1 majuscule');
                isValid = false;
            } else if (!numberRegex.test(password)) {
                showError('new_password', 'Au moins 1 chiffre');
                isValid = false;
            } else {
                clearError('new_password');
            }
            
            if (confirm === '') {
                showError('confirm_password', 'Confirmez votre mot de passe');
                isValid = false;
            } else if (password !== confirm) {
                showError('confirm_password', 'Les mots de passe ne correspondent pas');
                isValid = false;
            } else {
                clearError('confirm_password');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        function showError(field, message) {
            const errorDiv = document.getElementById(field + 'Error');
            errorDiv.textContent = message;
            document.getElementById(field).classList.add('is-invalid');
        }
        
        function clearError(field) {
            const errorDiv = document.getElementById(field + 'Error');
            errorDiv.textContent = '';
            document.getElementById(field).classList.remove('is-invalid');
        }
    </script>
</body>
</html>

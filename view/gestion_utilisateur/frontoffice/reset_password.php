<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - Skiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body.auth-bg {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .auth-wrapper {
            width: 100%;
            padding: 20px;
        }
        .auth-card {
            max-width: 400px;
            margin: 0 auto;
        }
    </style>
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
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <?php if ($message_type !== 'success'): ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($show_form): ?>
                <form method="POST" action="" id="resetForm" novalidate>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="auth-form-group">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Nouveau mot de passe
                        </label>
                        <input type="password" class="form-control" id="new_password" name="new_password"
                               placeholder="Min. 8 caractères, 1 majuscule, 1 chiffre">
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>
                    
                    <div class="auth-form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Confirmer le mot de passe
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                               placeholder="Confirmez votre mot de passe">
                        <div class="invalid-feedback" id="confirmError"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-save me-2"></i>Réinitialiser le mot de passe
                    </button>
                </form>
                <?php endif; ?>
                
                <div class="text-center">
                    <a href="connexion.php" class="text-decoration-none">
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
                showError('new_password', 'Le mot de passe doit contenir au moins 8 caractères');
                isValid = false;
            } else if (!uppercaseRegex.test(password)) {
                showError('new_password', 'Le mot de passe doit contenir au moins une majuscule');
                isValid = false;
            } else if (!numberRegex.test(password)) {
                showError('new_password', 'Le mot de passe doit contenir au moins un chiffre');
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
        
        const newPasswordField = document.getElementById('new_password');
        const confirmPasswordField = document.getElementById('confirm_password');

        if (newPasswordField) {
            newPasswordField.addEventListener('input', function() {
                clearError('new_password');
            });
        }

        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function() {
                clearError('confirm_password');
            });
        }
        
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
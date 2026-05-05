<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Skiller</title>
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
                <i class="fas fa-key mb-3" style="font-size: 3rem;"></i>
                <h2>Mot de passe oublié</h2>
                <p>Entrez votre email pour recevoir un lien de réinitialisation</p>
            </div>
            
            <div class="auth-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="forgotForm" novalidate>
                    <div class="auth-form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email
                        </label>
                        <input type="text" class="form-control" id="email" name="email" 
                               placeholder="votre@email.com"
                               value="<?php echo htmlspecialchars($old_email ?? ''); ?>">
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-paper-plane me-2"></i>Envoyer le lien de réinitialisation
                    </button>
                </form>
                
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
        const forgotForm = document.getElementById('forgotForm');
        const emailField = document.getElementById('email');

        forgotForm.addEventListener('submit', function(e) {
            const email = emailField.value.trim();

            if (email === '') {
                showError('email', 'L\'email est obligatoire');
                e.preventDefault();
            } else if (!validateEmail(email)) {
                showError('email', 'Email invalide');
                e.preventDefault();
            } else {
                clearError('email');
            }
        });

        emailField.addEventListener('input', function() {
            clearError('email');
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

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    </script>
</body>
</html>
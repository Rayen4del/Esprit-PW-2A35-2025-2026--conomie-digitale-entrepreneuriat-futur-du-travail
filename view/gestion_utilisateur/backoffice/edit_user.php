<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once __DIR__ . '/../../../controller/gestion_utilisateur/UserController.php';
require_once __DIR__ . '/../../../model/gestion_utilisateur/User.php';

$error = '';
$success = '';
$user = null;
$userC = new UserController();

if (isset($_GET['id']) || isset($_POST['id'])) {
    $id = $_GET['id'] ?? $_POST['id'];
    $user = $userC->showUser($id);
}

if (!$user) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = $_POST['type'] ?? 'etudiant';
    $statut = $_POST['statut'] ?? 'actif';
    $newPassword = $_POST['new_password'] ?? '';
    
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }
    
    if (empty($errors)) {
        $updatedUser = new User($id, $nom, $email, null, $type, $statut);
        $userC->updateUser($updatedUser, $id);
        
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            } elseif (!preg_match('/[A-Z]/', $newPassword)) {
                $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
            } elseif (!preg_match('/[0-9]/', $newPassword)) {
                $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
            }
        }
        
        if (empty($error)) {
            $success = "Utilisateur mis à jour avec succès !";
            $user = $userC->showUser($id);
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
<!-- Reste du HTML identique -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur - Back Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link rel="stylesheet" href="../../../assets/css/forms.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Modifier l'utilisateur</h2>
            <a href="dashboard.php" class="btn btn-secondary">← Retour au dashboard</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $user['ID']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom complet</label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?php echo htmlspecialchars($user['Nom']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Rôle</label>
                            <select class="form-select" id="type" name="type">
                                <option value="etudiant" <?php echo $user['Type'] == 'etudiant' ? 'selected' : ''; ?>>Étudiant</option>
                                <option value="professionnel" <?php echo $user['Type'] == 'professionnel' ? 'selected' : ''; ?>>Professionnel</option>
                                <option value="admin" <?php echo $user['Type'] == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="actif" <?php echo $user['Statut'] == 'actif' ? 'selected' : ''; ?>>Actif</option>
                                <option value="suspendu" <?php echo $user['Statut'] == 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                            </select>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <small class="form-text text-muted">Minimum 8 caractères</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="delete_user.php?id=<?php echo $user['ID']; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">Supprimer</a>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Informations supplémentaires</h5>
            </div>
            <div class="card-body">
                <p><strong>Date d'inscription :</strong> <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></p>
                <p><strong>ID utilisateur :</strong> <?php echo $user['ID']; ?></p>
                <?php if (!empty($user['Bio'])): ?>
                    <p><strong>Bio :</strong> <?php echo nl2br(htmlspecialchars($user['Bio'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($user['Localisation'])): ?>
                    <p><strong>Localisation :</strong> <?php echo htmlspecialchars($user['Localisation']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
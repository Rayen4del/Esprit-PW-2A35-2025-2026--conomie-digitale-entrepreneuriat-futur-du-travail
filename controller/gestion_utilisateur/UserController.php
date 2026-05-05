<?php
// controller/gestion_utilisateur/UserController.php
require_once __DIR__ . '/../../model/gestion_utilisateur/config.php';
require_once __DIR__ . '/../../model/gestion_utilisateur/User.php';
require_once __DIR__ . '/../../model/gestion_utilisateur/EmailConfig.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController {
    
    // Lister tous les utilisateurs
    public function listUsers() {
        $sql = "SELECT u.*, p.Bio, p.Photo, p.Localisation 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                ORDER BY u.ID DESC";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
    
    // Filtrer les utilisateurs
    public function filterUsers($type = null, $statut = null) {
        $sql = "SELECT u.*, p.Bio, p.Photo 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                WHERE 1=1";
        
        if ($type && $type !== 'all') {
            $sql .= " AND u.Type = '$type'";
        }
        if ($statut && $statut !== 'all') {
            $sql .= " AND u.Statut = '$statut'";
        }
        $sql .= " ORDER BY u.ID DESC";
        
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Compter les utilisateurs filtrés
    public function countFilteredUsers($type = null, $statut = null, $search = null) {
        $sql = "SELECT COUNT(*) 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                WHERE 1=1";

        $params = [];

        if ($type && $type !== 'all') {
            $sql .= " AND u.Type = :type";
            $params['type'] = $type;
        }

        if ($statut && $statut !== 'all') {
            $sql .= " AND u.Statut = :statut";
            $params['statut'] = $statut;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (u.Nom LIKE :search OR u.Email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return (int) $query->fetchColumn();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Filtrer les utilisateurs avec pagination
    public function filterUsersPaginated($type = null, $statut = null, $limit = 10, $offset = 0, $search = null, $sortDate = 'desc', $sortName = 'none') {
        $sql = "SELECT u.*, p.Bio, p.Photo 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                WHERE 1=1";

        $params = [];

        if ($type && $type !== 'all') {
            $sql .= " AND u.Type = :type";
            $params['type'] = $type;
        }

        if ($statut && $statut !== 'all') {
            $sql .= " AND u.Statut = :statut";
            $params['statut'] = $statut;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (u.Nom LIKE :search OR u.Email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $orderParts = [];

        if ($sortName === 'asc') {
            $orderParts[] = 'u.Nom ASC';
        } elseif ($sortName === 'desc') {
            $orderParts[] = 'u.Nom DESC';
        }

        if ($sortDate === 'asc') {
            $orderParts[] = 'u.created_at ASC';
        } else {
            $orderParts[] = 'u.created_at DESC';
        }

        if (empty($orderParts)) {
            $orderParts[] = 'u.ID DESC';
        }

        $sql .= " ORDER BY " . implode(', ', $orderParts) . " LIMIT :limit OFFSET :offset";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue(':' . $key, $value);
            }

            $query->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $query->execute();
            return $query;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Récupérer toutes les données utilisateurs + profil pour export (CSV/PDF)
    public function getUsersForExport($type = null, $statut = null, $search = null, $sortDate = 'desc', $sortName = 'none') {
        $db = config::getConnexion();

        $uCols = $this->getTableColumns('utilisateur');
        $pCols = $this->getTableColumns('profil');

        $selectParts = [];
        foreach ($uCols as $col) {
            $selectParts[] = "u.`$col` AS `u_$col`";
        }
        foreach ($pCols as $col) {
            $selectParts[] = "p.`$col` AS `p_$col`";
        }

        $sql = "SELECT " . implode(', ', $selectParts) . "
                FROM utilisateur u
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur
                WHERE 1=1";

        $params = [];

        if ($type && $type !== 'all') {
            $sql .= " AND u.Type = :type";
            $params['type'] = $type;
        }

        if ($statut && $statut !== 'all') {
            $sql .= " AND u.Statut = :statut";
            $params['statut'] = $statut;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (u.Nom LIKE :search OR u.Email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $orderParts = [];

        if ($sortName === 'asc') {
            $orderParts[] = 'u.Nom ASC';
        } elseif ($sortName === 'desc') {
            $orderParts[] = 'u.Nom DESC';
        }

        if ($sortDate === 'asc') {
            $orderParts[] = 'u.created_at ASC';
        } else {
            $orderParts[] = 'u.created_at DESC';
        }

        if (empty($orderParts)) {
            $orderParts[] = 'u.ID DESC';
        }

        $sql .= " ORDER BY " . implode(', ', $orderParts);

        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Récupérer un utilisateur par ID pour export (toutes les colonnes)
    public function getUserForExportById($id) {
        $db = config::getConnexion();

        $uCols = $this->getTableColumns('utilisateur');
        $pCols = $this->getTableColumns('profil');

        $selectParts = [];
        foreach ($uCols as $col) {
            $selectParts[] = "u.`$col` AS `u_$col`";
        }
        foreach ($pCols as $col) {
            $selectParts[] = "p.`$col` AS `p_$col`";
        }

        $sql = "SELECT " . implode(', ', $selectParts) . "
                FROM utilisateur u
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur
                WHERE u.ID = :id
                LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    private function getTableColumns($tableName) {
        $db = config::getConnexion();
        $columns = [];

        try {
            $query = $db->query("SHOW COLUMNS FROM `$tableName`");
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['Field'])) {
                    $columns[] = $row['Field'];
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $columns;
    }
    
    // Supprimer un utilisateur
    public function deleteUser($id) {
        $sql = "DELETE FROM utilisateur WHERE ID = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
    
    // Ajouter un utilisateur
    public function addUser(User $user) {
        $sql = "INSERT INTO utilisateur (Nom, Email, MDP, Type, Statut, created_at) 
                VALUES (:nom, :email, :mdp, :type, :statut, NOW())";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'mdp' => password_hash($user->getMdp(), PASSWORD_DEFAULT),
                'type' => $user->getType(),
                'statut' => $user->getStatut()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }
    
    // Mettre à jour un utilisateur
    public function updateUser(User $user, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE utilisateur SET 
                    Nom = :nom,
                    Email = :email,
                    Type = :type,
                    Statut = :statut
                WHERE ID = :id'
            );
            $query->execute([
                'id' => $id,
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'type' => $user->getType(),
                'statut' => $user->getStatut()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    // Mettre à jour le mot de passe
    public function updatePassword($id, $newPassword) {
        try {
            $db = config::getConnexion();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = $db->prepare('UPDATE utilisateur SET MDP = :mdp WHERE ID = :id');
            $query->execute(['id' => $id, 'mdp' => $hashedPassword]);
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Afficher un utilisateur par ID
    public function showUser($id) {
        $sql = "SELECT u.*, p.Bio, p.Photo, p.Localisation 
                FROM utilisateur u 
                LEFT JOIN profil p ON u.ID = p.IDUtilisateur 
                WHERE u.ID = $id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        try {
            $query->execute();
            $user = $query->fetch();
            return $user;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    
    // Authentifier un utilisateur
    public function authenticate($email, $password) {
        $sql = "SELECT * FROM utilisateur WHERE Email = :email";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        
        if ($user && password_verify($password, $user['MDP'])) {
            if ($user['Statut'] === 'suspendu') {
                return ['success' => false, 'message' => 'Compte suspendu'];
            }
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }
    
    // Vérifier si email existe
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM utilisateur WHERE Email = :email";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['email' => $email]);
        return $query->fetchColumn() > 0;
    }
    
    // Changer le statut
    public function changeStatus($id, $statut) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare('UPDATE utilisateur SET Statut = :statut WHERE ID = :id');
            $query->execute(['id' => $id, 'statut' => $statut]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Statistiques
    public function getStats() {
        $db = config::getConnexion();
        $stats = [];
        
        $query = $db->query("SELECT COUNT(*) FROM utilisateur");
        $stats['total'] = $query->fetchColumn();
        
        $query = $db->query("SELECT Type, COUNT(*) as count FROM utilisateur GROUP BY Type");
        $stats['by_type'] = $query->fetchAll();
        
        $query = $db->query("SELECT Statut, COUNT(*) as count FROM utilisateur GROUP BY Statut");
        $stats['by_status'] = $query->fetchAll();
        
        return $stats;
    }
    
    // Générer un token sécurisé pour reset password
    public function generateResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $db = config::getConnexion();
        $query = $db->prepare("UPDATE utilisateur SET reset_token = :token, reset_expires = :expires_at WHERE Email = :email");
        $query->execute(['email' => $email, 'token' => password_hash($token, PASSWORD_DEFAULT), 'expires_at' => $expires_at]);
        
        return $token;
    }

    public function clearResetToken($email) {
        $db = config::getConnexion();
        $query = $db->prepare("UPDATE utilisateur SET reset_token = NULL, reset_expires = NULL WHERE Email = :email");
        $query->execute(['email' => $email]);
    }
    
    // Vérifier si l'email existe et peut recevoir un reset
    public function canResetPassword($email) {
        // Vérifier si l'utilisateur existe
        if (!$this->emailExists($email)) {
            return ['success' => false, 'message' => 'Aucun compte trouvé avec cet email'];
        }
        
        // Rate limiting : vérifier les tentatives récentes (dernière heure)
        $db = config::getConnexion();
        $query = $db->prepare("SELECT reset_expires FROM utilisateur WHERE Email = :email AND reset_expires > NOW()");
        $query->execute(['email' => $email]);
        $recent_reset = $query->fetch();
        
        if ($recent_reset) {
            return ['success' => false, 'message' => 'Un lien de reset a déjà été envoyé récemment. Vérifiez votre email.'];
        }
        
        return ['success' => true];
    }
    
    // Envoyer l'email de reset avec PHPMailer
    public function sendResetEmail($email, $token) {
        $reset_link = "http://localhost/skiller/reset-password.php?token=" . $token;
        
        $subject = "Réinitialisation de votre mot de passe - Skiller";
        $htmlBody = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Réinitialisation de mot de passe</title>
            <style>
                body { font-family: 'DM Sans', Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #6c63ff 0%, #5a53e6 100%); color: white; padding: 30px 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .content { background: #f8f9fc; padding: 30px 20px; border: 1px solid #e0e0e0; border-radius: 0 0 8px 8px; }
                .cta-button { display: inline-block; background: #6c63ff; color: white; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 20px 0; }
                .cta-button:hover { background: #5a53e6; }
                .footer { font-size: 12px; color: #999; margin-top: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin: 0;'>Réinitialisation de mot de passe</h2>
                </div>
                <div class='content'>
                    <p>Bonjour,</p>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte <strong>Skiller</strong>.</p>
                    <p>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
                    <div style='text-align: center;'>
                        <a href='$reset_link' class='cta-button'>Réinitialiser mon mot de passe</a>
                    </div>
                    <p style='color: #999; font-size: 12px;'>Ou copiez ce lien dans votre navigateur :<br><code>$reset_link</code></p>
                    <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
                    <p style='color: #999; font-size: 12px;'><strong>Important :</strong> Ce lien expirera dans 15 minutes.</p>
                    <p style='color: #999; font-size: 12px;'>Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email. Votre compte reste sécurisé.</p>
                    <div class='footer'>
                        <p>Cordialement,<br>L'équipe Skiller</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        try {
            $mail = new PHPMailer(true);
            
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = EmailConfig::SMTP_HOST;
            $mail->Port = EmailConfig::SMTP_PORT;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth = true;
            $mail->Username = EmailConfig::SMTP_USERNAME;
            $mail->Password = EmailConfig::SMTP_PASSWORD;
            
            // Paramètres de l'email
            $mail->setFrom(EmailConfig::SMTP_FROM_EMAIL, EmailConfig::SMTP_FROM_NAME);
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);
            
            // Envoyer l'email
            $sent = $mail->send();
            return $sent;
            
        } catch (Exception $e) {
            // En cas d'erreur, nettoyer le token
            $this->clearResetToken($email);
            return false;
        }
    }
    
    // Vérifier le token de reset
    public function verifyResetToken($token) {
        $db = config::getConnexion();
        $query = $db->prepare("SELECT Email, reset_token, reset_expires FROM utilisateur WHERE reset_token IS NOT NULL");
        $query->execute();
        $users = $query->fetchAll();
        
        foreach ($users as $user) {
            if (password_verify($token, $user['reset_token'])) {
                if (strtotime($user['reset_expires']) < time()) {
                    return ['success' => false, 'message' => 'Token expiré'];
                }
                return ['success' => true, 'email' => $user['Email']];
            }
        }
        
        return ['success' => false, 'message' => 'Token invalide'];
    }
    
    // Mettre à jour le mot de passe et supprimer le token
    public function resetPassword($token, $new_password) {
        $verification = $this->verifyResetToken($token);
        if (!$verification['success']) {
            return $verification;
        }
        
        $email = $verification['email'];
        
        // Hash du nouveau mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Mettre à jour le mot de passe et supprimer le token
        $db = config::getConnexion();
        $update_query = $db->prepare("UPDATE utilisateur SET MDP = :mdp, reset_token = NULL, reset_expires = NULL WHERE Email = :email");
        $update_query->execute(['mdp' => $hashed_password, 'email' => $email]);
        
        return ['success' => true, 'message' => 'Mot de passe mis à jour avec succès'];
    }
}
?>
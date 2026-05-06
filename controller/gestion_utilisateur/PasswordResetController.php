<?php
require_once __DIR__ . '/UserController.php';

class PasswordResetController {
    public static function processForgot() {
        session_start();

        if (isset($_SESSION['user_id'])) {
            header('Location: view/gestion_utilisateur/frontoffice/profil.php');
            exit;
        }

        $message = '';
        $message_type = '';
        $old_email = '';

        $userC = new UserController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old_email = trim($_POST['email'] ?? '');

            if ($old_email === '') {
                $message = "L'email est obligatoire.";
                $message_type = 'error';
            } elseif (!filter_var($old_email, FILTER_VALIDATE_EMAIL)) {
                $message = "Format d'email invalide.";
                $message_type = 'error';
            } else {
                $canReset = $userC->canResetPassword($old_email);

                if ($canReset['success']) {
                    $token = $userC->generateResetToken($old_email);

                    if ($userC->sendResetEmail($old_email, $token)) {
                        $message = "Un email de réinitialisation a été envoyé à votre adresse email.";
                        $message_type = 'success';
                    } else {
                        $message = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
                        $message_type = 'error';
                    }
                } else {
                    $message = $canReset['message'];
                    $message_type = 'error';
                }
            }
        }

        include __DIR__ . '/../../view/gestion_utilisateur/frontoffice/forgot_password.php';
    }

    public static function processReset() {
        session_start();

        if (isset($_SESSION['user_id'])) {
            header('Location: view/gestion_utilisateur/frontoffice/profil.php');
            exit;
        }

        $message = '';
        $message_type = '';
        $token = $_GET['token'] ?? '';
        $show_form = false;

        $userC = new UserController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $errors = [];

            if ($token === '') {
                $errors[] = 'Token manquant.';
            }

            if ($new_password === '') {
                $errors[] = 'Le nouveau mot de passe est obligatoire.';
            } elseif (strlen($new_password) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            } elseif (!preg_match('/[A-Z]/', $new_password)) {
                $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
            } elseif (!preg_match('/[0-9]/', $new_password)) {
                $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
            }

            if ($new_password !== $confirm_password) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (empty($errors)) {
                $result = $userC->resetPassword($token, $new_password);

                if ($result['success']) {
                    $message = $result['message'];
                    $message_type = 'success';
                    $show_form = false;
                    header('refresh:3;url=view/gestion_utilisateur/frontoffice/connexion.php');
                } else {
                    $message = $result['message'];
                    $message_type = 'error';
                    $show_form = true;
                }
            } else {
                $message = implode("<br>", $errors);
                $message_type = 'error';
                $show_form = true;
            }
        } else {
            if ($token === '') {
                $message = 'Token manquant dans l\'URL.';
                $message_type = 'error';
                $show_form = false;
            } else {
                $verification = $userC->verifyResetToken($token);
                if ($verification['success']) {
                    $show_form = true;
                } else {
                    $message = $verification['message'];
                    $message_type = 'error';
                    $show_form = false;
                }
            }
        }

        include __DIR__ . '/../../view/gestion_utilisateur/frontoffice/reset_password.php';
    }
}
?>
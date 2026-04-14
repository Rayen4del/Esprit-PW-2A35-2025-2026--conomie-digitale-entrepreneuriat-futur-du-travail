<?php
// controller/ProfilController.php
require_once __DIR__ . '/../../model/gestion_utilisateur/config.php';
require_once __DIR__ . '/../../model/gestion_utilisateur/Profil.php';

class ProfilController {
    
    // Ajouter ou mettre à jour un profil
    public function saveProfil(Profil $profil) {
        // Vérifier si le profil existe déjà
        $sql = "SELECT COUNT(*) FROM profil WHERE IDUtilisateur = :idUser";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['idUser' => $profil->getIdUtilisateur()]);
        $exists = $query->fetchColumn() > 0;
        
        if ($exists) {
            // Update
            $sql = "UPDATE profil SET Bio = :bio, Photo = :photo, Localisation = :localisation 
                    WHERE IDUtilisateur = :idUser";
        } else {
            // Insert
            $sql = "INSERT INTO profil (IDUtilisateur, Bio, Photo, Localisation) 
                    VALUES (:idUser, :bio, :photo, :localisation)";
        }
        
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idUser' => $profil->getIdUtilisateur(),
                'bio' => $profil->getBio(),
                'photo' => $profil->getPhoto(),
                'localisation' => $profil->getLocalisation()
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }
    
    // Récupérer le profil d'un utilisateur
    public function getProfilByUserId($userId) {
        $sql = "SELECT * FROM profil WHERE IDUtilisateur = :idUser";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['idUser' => $userId]);
        return $query->fetch();
    }
}
?>
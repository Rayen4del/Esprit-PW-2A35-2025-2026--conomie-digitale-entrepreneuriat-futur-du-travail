<?php
// model/gestion_utilisateur/config.php

// Vérifier si la classe n'existe pas déjà
if (!class_exists('config')) {
    class config {
        private static $pdo = null;
        
        public static function getConnexion() {
            if (!isset(self::$pdo)) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "bd_skiller";
                
                try {
                    self::$pdo = new PDO(
                        "mysql:host=$servername;dbname=$dbname",
                        $username,
                        $password
                    );
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    die('Erreur: ' . $e->getMessage());
                }
            }
            return self::$pdo;
        }
    }
}
?>